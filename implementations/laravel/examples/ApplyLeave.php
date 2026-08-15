<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Examples;

use WaysNX\BusinessFramework\Models\BusinessFunction;

/**
 * ApplyLeave
 *
 * Example Business Function: HR.LEAVE.APPLY.APPLY_LEAVE
 *
 * Purpose:
 *   Submit an employee leave request
 *
 * This example demonstrates:
 * - How to create a concrete Business Function extending the base class
 * - How to define identity, metadata, and contracts
 * - How to specify business rules, validation, and authorization
 * - How to declare events and dependencies
 * - Complete conformance to WBF-DOC-0005
 *
 * Business Scenario:
 *   An employee submits a leave request for one or more consecutive days.
 *   The system validates:
 *   - Employee is active
 *   - Leave balance is sufficient
 *   - Dates don't overlap existing approved leave
 *
 * Response:
 *   - Leave Request ID
 *   - Status (Pending, Approved, Rejected, etc.)
 *   - Remaining Leave Balance
 *
 * Event:
 *   - LeaveRequested event is published for workflows and integrations
 *
 * @extends BusinessFunction
 * @package WaysNX\BusinessFramework\Examples
 */
class ApplyLeave extends BusinessFunction
{
    /**
     * Constructor - Define the Business Function
     *
     * The constructor defines the complete business function contract.
     * This is executed once per application startup (typically).
     */
    public function __construct()
    {
        // ================================
        // IDENTITY
        // ================================
        // Requirement: BF-ID-01 through BF-ID-07

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
        // Requirement: BF-META-01 through BF-META-10

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
        // Requirement: BF-REQ-01 through BF-REQ-05

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
                    'constraints' => 'Must be valid and active employee',
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
                    'constraints' => 'Must be future date, not past',
                ],
                'endDate' => [
                    'type' => 'date',
                    'format' => 'YYYY-MM-DD',
                    'description' => 'Last day of leave',
                    'example' => '2026-09-05',
                    'constraints' => 'Must be on or after startDate',
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
        // Requirement: BF-RESP-01 through BF-RESP-05

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
        // Requirement: BF-ERR-01 through BF-ERR-04

        $this->errorCategories = [
            'ValidationError',           // Invalid input (e.g., invalid date format)
            'AuthorizationError',        // User not permitted to apply leave
            'BusinessRuleViolation',     // Employee not active, insufficient balance, date overlap
            'ResourceNotFound',          // Employee not found
            'Conflict',                  // Duplicate request for same period
            'InternalSystemError',       // Database, system errors
        ];

        // ================================
        // VALIDATION RULES
        // ================================
        // Requirement: BF-REQ-04, BF-VAL-01 through BF-VAL-06

        $this->validationRules = [
            'employeeId' => 'required|string|min:1|max:50',
            'leaveType' => 'required|string|in:Annual,Sick,Maternity,Paternity,Unpaid,Compensatory',
            'startDate' => 'required|date_format:Y-m-d|after_or_equal:today',
            'endDate' => 'required|date_format:Y-m-d|after_or_equal:startDate',
            'reason' => 'nullable|string|max:500',
            'attachments' => 'nullable|array',
            'attachments.*' => 'string',
        ];

        // ================================
        // AUTHORIZATION REQUIREMENTS
        // ================================
        // Requirement: BF-SEC-01 through BF-SEC-05

        $this->authorizationRequirements = [
            'roles' => ['Employee', 'Manager', 'HR_Admin'],
            'permissions' => ['leave.apply', 'leave.apply.own_leave'],
            'conditions' => [
                'Employee can only apply for own leave',
                'Manager can apply for team members',
                'HR Admin can apply for any employee',
            ],
        ];

        // ================================
        // BUSINESS RULES
        // ================================
        // Requirement: BF-BR-01 through BF-BR-05

        $this->businessRules = [
            'Employee must be active (status = Active)',
            'Employee must have positive leave balance for the leave type',
            'Requested leave period must not overlap with existing approved leave',
            'Leave request cannot be for past dates',
            'Maximum consecutive leave days may be limited by policy',
            'Certain leave types may require pre-approval',
        ];

        // ================================
        // PROCESSING DEFINITION
        // ================================
        // Represents the execution pipeline without implementing the engine

        // Validation: Handled by validateRequest() and validationRules
        // Authorization: Handled by checkAuthorization()
        // Business Rules: Handled by evaluateBusinessRules()
        // Execution: Handled by executeBusiness()
        // Events: Handled by publishEvents()

        // ================================
        // OPERATIONAL DEFINITION
        // ================================
        // Requirement: BF-OBS-01 through BF-OBS-05

        $this->observabilityRequirements = [
            'correlation' => true,
            'correlation_fields' => ['employeeId', 'leaveRequestId'],
            'metrics' => [
                'execution_time',
                'success_rate',
                'approval_time',
                'rejection_rate',
            ],
            'logging' => [
                'log_request' => true,
                'log_response' => true,
                'log_business_rules' => true,
                'sensitive_fields' => ['attachments'],
            ],
        ];

        // ================================
        // AUDIT REQUIREMENTS
        // ================================
        // Requirement: BF-OBS-04

        $this->auditRequirements = [
            'enabled' => true,
            'trackChanges' => true,
            'retentionDays' => 2555, // 7 years
            'fields' => [
                'employeeId',
                'leaveType',
                'startDate',
                'endDate',
                'status',
            ],
        ];

        // ================================
        // INITIALIZE
        // ================================

        $this->initializeBusinessFunction();
    }

    // ================================
    // BUSINESS LOGIC - TEMPLATE METHODS
    // ================================

    /**
     * Validate Request Contract Compliance
     *
     * Called during validation phase (BF-VAL-01).
     * Override to implement business-specific validation beyond data type validation.
     *
     * @param array $request The incoming request data
     * @throws \InvalidArgumentException if validation fails
     * @return void
     */
    protected function validateRequest(array $request): void
    {
        // Date range validation
        if (isset($request['startDate']) && isset($request['endDate'])) {
            $start = \DateTime::createFromFormat('Y-m-d', $request['startDate']);
            $end = \DateTime::createFromFormat('Y-m-d', $request['endDate']);

            if ($start && $end && $start > $end) {
                throw new \InvalidArgumentException(
                    'startDate must not be after endDate'
                );
            }
        }
    }

    /**
     * Check Authorization
     *
     * Called during authorization phase (BF-SEC-01).
     * Override to implement authorization logic.
     *
     * @param array $request The request data
     * @param mixed $caller The caller/user context
     * @throws \RuntimeException if authorization fails
     * @return void
     */
    protected function checkAuthorization(array $request, mixed $caller = null): void
    {
        // Implementation-specific authorization
        // In Laravel: Check user roles, permissions, tenant access, etc.
        // In Node.js: Check JWT claims, RBAC policies, etc.
    }

    /**
     * Evaluate Business Rules
     *
     * Called during business rule evaluation phase (BF-BR-01).
     * Override to implement business rule checks.
     *
     * @param array $request The request data
     * @throws \RuntimeException if business rules fail
     * @return void
     */
    protected function evaluateBusinessRules(array $request): void
    {
        // Pseudo-code (actual implementation calls business rule engine)
        // 1. Fetch employee: SELECT FROM employees WHERE id = request.employeeId
        // 2. Check employee is active
        // 3. Check leave balance: SELECT balance FROM leave_balances WHERE employee_id = ? AND leave_type = ?
        // 4. Check date overlap: SELECT FROM leave_requests WHERE employee_id = ? AND status = 'Approved' AND (startDate BETWEEN request.startDate AND request.endDate)
    }

    /**
     * Execute Business Logic
     *
     * Core business operation: Create the leave request.
     *
     * Requirement: BF-EXE-02, BF-EXE-03
     *
     * @param array $request The validated request
     * @return array The business result
     */
    protected function executeBusiness(array $request): array
    {
        // Pseudo-code (actual implementation):
        // 1. Generate unique leaveRequestId
        // 2. INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason, status)
        // 3. Calculate daysRequested
        // 4. UPDATE leave_balances SET balance = balance - daysRequested WHERE employee_id = ? AND leave_type = ?
        // 5. Get updated balance
        // 6. Get assigned approver
        // 7. Return result

        return [
            'leaveRequestId' => 'LR-2026-' . uniqid(),
            'status' => 'Pending',
            'remainingBalance' => 15, // Placeholder
            'daysRequested' => 5,     // Placeholder
            'approverName' => 'Jane Manager',
            'createdAt' => date('Y-m-d\TH:i:s\Z'),
        ];
    }

    /**
     * Publish Events
     *
     * Called after successful execution (BF-EVT-01).
     * Override to publish domain events.
     *
     * Requirement: BF-EVT-01 through BF-EVT-05
     *
     * @param array $request The original request
     * @param array $result The execution result
     * @return void
     */
    protected function publishEvents(array $request, array $result): void
    {
        // Publish LeaveRequested event
        // In Laravel: event(new LeaveRequested($request, $result));
        // In Node.js: eventBus.publish('LeaveRequested', {request, result});
    }

    /**
     * Transform Execution Result into Response
     *
     * Ensures the result matches the Response Contract.
     *
     * Requirement: BF-RESP-02, BF-RESP-05
     *
     * @param array $result The raw execution result
     * @return array The response matching Response Contract
     */
    protected function transformToResponse(array $result): array
    {
        // Ensure response matches the contract
        return [
            'leaveRequestId' => $result['leaveRequestId'] ?? null,
            'status' => $result['status'] ?? 'Pending',
            'remainingBalance' => $result['remainingBalance'] ?? 0,
            'daysRequested' => $result['daysRequested'] ?? 0,
            'approverName' => $result['approverName'] ?? null,
            'createdAt' => $result['createdAt'] ?? null,
        ];
    }
}

// ================================
// USAGE EXAMPLE
// ================================

/*
In application code:

    $applyLeave = new ApplyLeave();

    // Define request
    $request = [
        'employeeId' => 'EMP-2026-001',
        'leaveType' => 'Annual',
        'startDate' => '2026-09-01',
        'endDate' => '2026-09-05',
        'reason' => 'Family vacation',
    ];

    // Get the contract definition (for documentation, code generation, validation)
    $definition = $applyLeave->getCompleteContractDefinition();

    // Generate documentation
    $doc = generateDocumentation($definition);

    // Generate OpenAPI spec
    $openapi = generateOpenAPI($definition);

    // In runtime (handled by execution engine):
    // 1. $applyLeave->validateRequest($request)
    // 2. $applyLeave->checkAuthorization($request, $user)
    // 3. $applyLeave->evaluateBusinessRules($request)
    // 4. $result = $applyLeave->executeBusiness($request)
    // 5. $applyLeave->publishEvents($request, $result)
    // 6. $response = $applyLeave->transformToResponse($result)
*/
