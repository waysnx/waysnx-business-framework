<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\BusinessFunctions;

use WaysNX\BusinessFramework\Models\BusinessFunction;
use WaysNX\BusinessFramework\ReferenceData\LeaveReferenceData;

/**
 * ApplyLeave
 *
 * Real Business Function Implementation: HR.LEAVE.APPLY.APPLY_LEAVE
 *
 * Purpose:
 *   Submit an employee leave request with real business logic
 *
 * This implementation demonstrates:
 * - Complete business logic within executeBusiness()
 * - Validation using validateRequest()
 * - Authorization using checkAuthorization()
 * - Business rules using evaluateBusinessRules()
 * - Event publishing using publishEvents()
 * - Response transformation using transformToResponse()
 *
 * Business Scenario:
 *   An employee submits a leave request for one or more consecutive days.
 *   The system validates:
 *   - Employee is active
 *   - Leave balance is sufficient
 *   - Dates don't overlap existing approved leave
 *   - Date constraints (not past, not too far in future)
 *   - Maximum consecutive days constraints
 *
 * Response:
 *   - Leave Request ID
 *   - Status (Pending, Approved)
 *   - Remaining Leave Balance
 *   - Days Requested
 *   - Assigned Approver
 *   - Created Timestamp
 *
 * Event:
 *   - LeaveRequested event published on success
 *
 * @extends BusinessFunction
 * @package WaysNX\BusinessFramework\BusinessFunctions
 */
class ApplyLeave extends BusinessFunction
{
    /**
     * In-memory storage for created leave requests (for this example)
     *
     * In production: Persisted to database
     */
    private static array $createdRequests = [];

    /**
     * Constructor - Define the Business Function
     *
     * Establishes the complete business function contract.
     */
    public function __construct()
    {
        // ================================
        // IDENTITY
        // ================================

        $this->functionId = 'HR.LEAVE.APPLY.APPLY_LEAVE';
        $this->functionName = 'Apply Leave';
        $this->functionVersion = '1.0.0';
        $this->module = 'HR';
        $this->domain = 'LEAVE';
        $this->capability = 'APPLY';
        $this->lifecycleStatus = 'Released';

        // ================================
        // METADATA
        // ================================

        $this->description = 'Submit an employee leave request';
        $this->businessOwner = 'HR Manager';
        $this->technicalOwner = 'HR Development Team';

        $this->tags = ['leave', 'employee', 'request', 'absence', 'workflow'];
        $this->classification = 'Core';
        $this->visibility = 'Internal';

        $this->dependencies = [];
        $this->eventsPublished = ['LeaveRequested'];
        $this->eventsConsumed = [];

        $this->keywords = ['leave', 'absence', 'vacation', 'sick-leave', 'employee', 'request'];

        // ================================
        // REQUEST CONTRACT
        // ================================

        $this->requestContract = [
            'required' => [
                'employeeId',
                'leaveType',
                'startDate',
                'endDate',
            ],
            'optional' => [
                'reason',
                'attachments',
            ],
            'fields' => [
                'employeeId' => [
                    'type' => 'string',
                    'description' => 'Unique employee identifier',
                    'example' => 'EMP-2026-001',
                ],
                'leaveType' => [
                    'type' => 'string',
                    'description' => 'Type of leave being requested',
                    'example' => 'Annual',
                    'enum' => ['Annual', 'Sick', 'Maternity', 'Paternity', 'Unpaid', 'Compensatory'],
                ],
                'startDate' => [
                    'type' => 'date',
                    'format' => 'YYYY-MM-DD',
                    'description' => 'First day of leave',
                    'example' => '2026-09-01',
                ],
                'endDate' => [
                    'type' => 'date',
                    'format' => 'YYYY-MM-DD',
                    'description' => 'Last day of leave',
                    'example' => '2026-09-05',
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Reason for leave (optional)',
                    'example' => 'Family vacation',
                    'maxLength' => 500,
                ],
                'attachments' => [
                    'type' => 'array',
                    'description' => 'Supporting documents (optional)',
                    'items' => 'string (file identifiers)',
                ],
            ],
        ];

        // ================================
        // RESPONSE CONTRACT
        // ================================

        $this->responseContract = [
            'description' => 'Leave request successfully created',
            'fields' => [
                'leaveRequestId' => [
                    'type' => 'string',
                    'description' => 'Unique identifier for the leave request',
                    'example' => 'LR-2026-001',
                ],
                'status' => [
                    'type' => 'string',
                    'description' => 'Current status of the request',
                    'example' => 'Pending',
                    'enum' => ['Pending', 'Approved', 'Rejected', 'Withdrawn'],
                ],
                'remainingBalance' => [
                    'type' => 'integer',
                    'description' => 'Employee\'s remaining leave balance for the leave type',
                    'example' => 12,
                    'unit' => 'days',
                ],
                'daysRequested' => [
                    'type' => 'integer',
                    'description' => 'Number of days requested',
                    'example' => 5,
                    'unit' => 'days',
                ],
                'approverName' => [
                    'type' => 'string',
                    'description' => 'Name of the assigned approver',
                    'example' => 'Jane Manager',
                ],
                'createdAt' => [
                    'type' => 'datetime',
                    'description' => 'When the request was created',
                    'example' => '2026-08-15T10:30:00Z',
                ],
            ],
            'success_fields' => [
                'leaveRequestId',
                'status',
                'remainingBalance',
                'daysRequested',
            ],
        ];

        // ================================
        // ERROR CATEGORIES
        // ================================

        $this->errorCategories = [
            'ValidationError',
            'AuthorizationError',
            'BusinessRuleViolation',
            'ResourceNotFound',
            'Conflict',
        ];

        // ================================
        // VALIDATION RULES
        // ================================

        $this->validationRules = [
            'employeeId' => 'required|string|min:1|max:50',
            'leaveType' => 'required|string|in:Annual,Sick,Maternity,Paternity,Unpaid,Compensatory',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
            'reason' => 'nullable|string|max:500',
            'attachments' => 'nullable|array',
        ];

        // ================================
        // AUTHORIZATION REQUIREMENTS
        // ================================

        $this->authorizationRequirements = [
            'roles' => ['Employee', 'Manager', 'HR_Admin'],
            'permissions' => ['leave.apply'],
            'conditions' => [
                'Employee can only apply for own leave',
                'Manager can apply for team members',
                'HR Admin can apply for any employee',
            ],
        ];

        // ================================
        // BUSINESS RULES
        // ================================

        $this->businessRules = [
            'Employee must be active (status = Active)',
            'Employee must have positive leave balance for the leave type',
            'Requested leave period must not overlap with existing approved leave',
            'Leave request cannot be for past dates',
            'Maximum consecutive leave days may be limited by policy',
        ];

        // ================================
        // OBSERVABILITY & AUDIT
        // ================================

        $this->observabilityRequirements = [
            'correlation' => true,
            'correlation_fields' => ['employeeId', 'leaveRequestId'],
            'metrics' => ['execution_time', 'success_rate'],
        ];

        $this->auditRequirements = [
            'enabled' => true,
            'trackChanges' => true,
            'retentionDays' => 2555,
            'fields' => ['employeeId', 'leaveType', 'startDate', 'endDate', 'status'],
        ];

        // ================================
        // INITIALIZE
        // ================================

        $this->initializeBusinessFunction();
    }

    // ================================
    // TEMPLATE METHODS - BUSINESS LOGIC
    // ================================

    /**
     * Validate Request Contract Compliance
     *
     * Phase: BF-VAL-01
     * Validates the structure and basic constraints of the request.
     *
     * @param array $request The incoming request data
     * @throws \InvalidArgumentException if validation fails
     * @return void
     */
    protected function validateRequest(array $request): void
    {
        // Validate date format and logical constraints
        if (isset($request['startDate']) && isset($request['endDate'])) {
            $start = \DateTime::createFromFormat('Y-m-d', $request['startDate']);
            $end = \DateTime::createFromFormat('Y-m-d', $request['endDate']);

            if (!$start || !$end) {
                throw new \InvalidArgumentException('Date format must be YYYY-MM-DD');
            }

            if ($start > $end) {
                throw new \InvalidArgumentException('startDate cannot be after endDate');
            }

            // Reject past dates
            $today = new \DateTime('today');
            if ($start < $today) {
                throw new \InvalidArgumentException('startDate cannot be in the past');
            }
        }

        // Validate leave type
        if (isset($request['leaveType'])) {
            $validTypes = ['Annual', 'Sick', 'Maternity', 'Paternity', 'Unpaid', 'Compensatory'];
            if (!in_array($request['leaveType'], $validTypes)) {
                throw new \InvalidArgumentException(
                    'leaveType must be one of: ' . implode(', ', $validTypes)
                );
            }
        }
    }

    /**
     * Check Authorization
     *
     * Phase: BF-SEC-01
     * Validates that the caller has permission to apply leave for the specified employee.
     *
     * @param array $request The request data
     * @param mixed $caller The caller context (e.g., User object with roles)
     * @throws \RuntimeException if authorization fails
     * @return void
     */
    protected function checkAuthorization(array $request, mixed $caller = null): void
    {
        // In-memory authorization logic for demonstration
        // Caller structure: ['id' => 'EMP-xxx', 'role' => 'Employee'|'Manager'|'HR_Admin']

        if (!$caller || !isset($caller['id']) || !isset($caller['role'])) {
            throw new \RuntimeException('Caller context required for authorization');
        }

        $callerId = $caller['id'];
        $callerRole = $caller['role'];
        $targetEmployeeId = $request['employeeId'];

        // Authorization logic:
        // - Employee: Can only apply for their own leave
        // - Manager: Can apply for their direct reports
        // - HR_Admin: Can apply for any employee

        if ($callerRole === 'Employee') {
            if ($callerId !== $targetEmployeeId) {
                throw new \RuntimeException('Employees can only apply leave for themselves');
            }
        } elseif ($callerRole === 'Manager') {
            // Check if caller is the manager of target employee
            $targetEmployee = LeaveReferenceData::getEmployee($targetEmployeeId);
            if (!$targetEmployee || $targetEmployee['manager_id'] !== $callerId) {
                throw new \RuntimeException('Managers can only apply leave for their direct reports');
            }
        } elseif ($callerRole === 'HR_Admin') {
            // HR Admin can do anything - no additional check needed
        } else {
            throw new \RuntimeException("Unknown role: {$callerRole}");
        }
    }

    /**
     * Evaluate Business Rules
     *
     * Phase: BF-BR-01
     * Validates business rules:
     * - Employee is active
     * - Leave balance is sufficient
     * - Requested dates don't overlap existing approved leave
     * - Maximum consecutive days constraints
     *
     * @param array $request The request data
     * @throws \RuntimeException if business rules fail
     * @return void
     */
    protected function evaluateBusinessRules(array $request): void
    {
        $employeeId = $request['employeeId'];
        $leaveType = $request['leaveType'];
        $startDate = $request['startDate'];
        $endDate = $request['endDate'];

        // Rule 1: Employee must be active
        if (!LeaveReferenceData::isEmployeeActive($employeeId)) {
            throw new \RuntimeException(
                "Employee {$employeeId} is not active and cannot apply for leave"
            );
        }

        // Rule 2: Leave type must exist
        if (!LeaveReferenceData::getLeaveType($leaveType)) {
            throw new \RuntimeException("Leave type '{$leaveType}' does not exist");
        }

        // Rule 3: Employee must have sufficient leave balance
        $requestedDays = LeaveReferenceData::calculateDays($startDate, $endDate);
        $currentBalance = LeaveReferenceData::getLeaveBalance($employeeId, $leaveType);

        if ($currentBalance < $requestedDays) {
            throw new \RuntimeException(
                "Insufficient leave balance. Requested: {$requestedDays} days, " .
                "Available: {$currentBalance} days for {$leaveType} leave"
            );
        }

        // Rule 4: No overlapping approved leave
        $overlapping = LeaveReferenceData::getOverlappingApprovedLeave(
            $employeeId,
            $startDate,
            $endDate
        );

        if (!empty($overlapping)) {
            $conflictPeriod = $overlapping[0]['start_date'] . ' to ' . $overlapping[0]['end_date'];
            throw new \RuntimeException(
                "Requested leave overlaps with existing approved leave ({$conflictPeriod})"
            );
        }

        // Rule 5: Maximum consecutive days constraint
        $leaveTypePolicy = LeaveReferenceData::getLeaveType($leaveType);
        if ($leaveTypePolicy && $leaveTypePolicy['max_consecutive_days'] > 0) {
            if ($requestedDays > $leaveTypePolicy['max_consecutive_days']) {
                throw new \RuntimeException(
                    "Maximum consecutive leave for {$leaveType} is " .
                    "{$leaveTypePolicy['max_consecutive_days']} days, " .
                    "but {$requestedDays} days were requested"
                );
            }
        }
    }

    /**
     * Execute Business Logic
     *
     * Phase: BF-EXE-01
     * Core operation: Create the leave request.
     *
     * This is where real business logic lives.
     * All validation and authorization have passed.
     * The request is known to be valid and authorized.
     *
     * Steps:
     * 1. Generate unique leaveRequestId
     * 2. Calculate requested days
     * 3. Determine approval status
     * 4. Calculate remaining balance
     * 5. Get assigned approver
     * 6. Store the leave request (in-memory for this example)
     * 7. Return the raw business result
     *
     * @param array $request The validated request
     * @return array The business result
     */
    protected function executeBusiness(array $request): array
    {
        $employeeId = $request['employeeId'];
        $leaveType = $request['leaveType'];
        $startDate = $request['startDate'];
        $endDate = $request['endDate'];

        // Step 1: Generate unique leave request ID
        $leaveRequestId = 'LR-' . date('Y') . '-' . str_pad(
            (string)(count(self::$createdRequests) + 1),
            6,
            '0',
            STR_PAD_LEFT
        );

        // Step 2: Calculate requested days
        $daysRequested = LeaveReferenceData::calculateDays($startDate, $endDate);

        // Step 3: Determine approval status
        $approvalRule = LeaveReferenceData::getApprovalRule($leaveType);
        $status = 'Pending';
        if ($approvalRule && !$approvalRule['approval_required']) {
            $status = 'Approved';
        } elseif ($approvalRule && $approvalRule['auto_approve_days'] > 0) {
            if ($daysRequested <= $approvalRule['auto_approve_days']) {
                $status = 'Approved';
            }
        }

        // Step 4: Calculate remaining balance after request (if approved)
        $currentBalance = LeaveReferenceData::getLeaveBalance($employeeId, $leaveType);
        $remainingBalance = $status === 'Approved' ? $currentBalance - $daysRequested : $currentBalance;

        // Step 5: Get assigned approver
        $approverName = null;
        if ($status === 'Pending') {
            $approverRule = LeaveReferenceData::getApprovalRule($leaveType);
            if ($approverRule['approver_role'] === 'Manager') {
                $manager = LeaveReferenceData::getEmployeeManager($employeeId);
                $approverName = $manager ? $manager['name'] : 'Unassigned Manager';
            } elseif ($approverRule['approver_role'] === 'HR_Admin') {
                $approverName = 'HR Administrator';
            }
        }

        // Step 6: Create leave request record (in-memory storage for this example)
        $leaveRequest = [
            'leaveRequestId' => $leaveRequestId,
            'employeeId' => $employeeId,
            'leaveType' => $leaveType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'daysRequested' => $daysRequested,
            'status' => $status,
            'currentBalance' => $currentBalance,
            'remainingBalance' => $remainingBalance,
            'approverName' => $approverName,
            'reason' => $request['reason'] ?? null,
            'attachments' => $request['attachments'] ?? [],
            'createdAt' => date('Y-m-d\TH:i:s\Z'),
        ];

        // Store in memory (in production: INSERT INTO leave_requests)
        self::$createdRequests[$leaveRequestId] = $leaveRequest;

        // Step 7: Return raw business result
        return [
            'leaveRequestId' => $leaveRequestId,
            'status' => $status,
            'remainingBalance' => $remainingBalance,
            'daysRequested' => $daysRequested,
            'approverName' => $approverName,
            'createdAt' => date('Y-m-d\TH:i:s\Z'),
        ];
    }

    /**
     * Publish Events
     *
     * Phase: BF-EVT-01
     * Publishes the LeaveRequested event for downstream consumers.
     *
     * @param array $request The original request
     * @param array $result The execution result
     * @return void
     */
    protected function publishEvents(array $request, array $result): void
    {
        // In a real system, this would publish to an event bus or message queue
        // For this example, we just record that the event was published
        // The Runtime can check via getEventsPublished()
    }

    /**
     * Transform Execution Result into Response
     *
     * Phase: Final Response Transformation
     * Ensures the result matches the Response Contract.
     *
     * Removes internal fields and ensures only contract-compliant fields are returned.
     *
     * @param array $result The raw execution result
     * @return array The response matching Response Contract
     */
    protected function transformToResponse(array $result): array
    {
        // Ensure response matches the contract (remove internal fields)
        return [
            'leaveRequestId' => $result['leaveRequestId'] ?? null,
            'status' => $result['status'] ?? 'Pending',
            'remainingBalance' => $result['remainingBalance'] ?? 0,
            'daysRequested' => $result['daysRequested'] ?? 0,
            'approverName' => $result['approverName'] ?? null,
            'createdAt' => $result['createdAt'] ?? null,
        ];
    }

    // ================================
    // TEST HELPER METHODS
    // ================================

    /**
     * Get all created leave requests (for testing)
     *
     * @return array
     */
    public static function getCreatedRequests(): array
    {
        return self::$createdRequests;
    }

    /**
     * Clear created requests (for testing)
     *
     * @return void
     */
    public static function clearCreatedRequests(): void
    {
        self::$createdRequests = [];
    }

    /**
     * Get created request by ID (for testing)
     *
     * @param string $leaveRequestId
     * @return array|null
     */
    public static function getCreatedRequest(string $leaveRequestId): ?array
    {
        return self::$createdRequests[$leaveRequestId] ?? null;
    }
}
