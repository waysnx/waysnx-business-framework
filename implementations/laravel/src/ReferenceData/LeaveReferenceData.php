<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\ReferenceData;

/**
 * LeaveReferenceData
 *
 * In-memory reference data for leave management.
 *
 * Provides test/reference data for:
 * - Employees
 * - Leave types and balances
 * - Existing approved leave
 * - Approval rules
 *
 * Purpose: Demonstrate business logic without persistence layer.
 *
 * @package WaysNX\BusinessFramework\ReferenceData
 */
class LeaveReferenceData
{
    /**
     * Employee records
     *
     * In production: SELECT * FROM employees
     */
    private static array $employees = [
        'EMP-2026-001' => [
            'id' => 'EMP-2026-001',
            'name' => 'Alice Johnson',
            'email' => 'alice@company.com',
            'department' => 'Engineering',
            'manager_id' => 'EMP-2026-005',
            'status' => 'Active',
            'hired_date' => '2023-01-15',
        ],
        'EMP-2026-002' => [
            'id' => 'EMP-2026-002',
            'name' => 'Bob Smith',
            'email' => 'bob@company.com',
            'department' => 'Engineering',
            'manager_id' => 'EMP-2026-005',
            'status' => 'Active',
            'hired_date' => '2023-06-01',
        ],
        'EMP-2026-003' => [
            'id' => 'EMP-2026-003',
            'name' => 'Carol Davis',
            'email' => 'carol@company.com',
            'department' => 'Sales',
            'manager_id' => 'EMP-2026-006',
            'status' => 'Inactive',
            'hired_date' => '2022-03-10',
        ],
        'EMP-2026-004' => [
            'id' => 'EMP-2026-004',
            'name' => 'David Wilson',
            'email' => 'david@company.com',
            'department' => 'Engineering',
            'manager_id' => 'EMP-2026-005',
            'status' => 'Active',
            'hired_date' => '2024-01-01',
        ],
        'EMP-2026-005' => [
            'id' => 'EMP-2026-005',
            'name' => 'Jane Manager',
            'email' => 'jane@company.com',
            'department' => 'Engineering',
            'manager_id' => null,
            'status' => 'Active',
            'hired_date' => '2021-01-01',
        ],
        'EMP-2026-006' => [
            'id' => 'EMP-2026-006',
            'name' => 'Sales Manager',
            'email' => 'sales.mgr@company.com',
            'department' => 'Sales',
            'manager_id' => null,
            'status' => 'Active',
            'hired_date' => '2020-06-01',
        ],
    ];

    /**
     * Leave types and their policies
     *
     * In production: SELECT * FROM leave_types
     */
    private static array $leaveTypes = [
        'Annual' => [
            'name' => 'Annual',
            'description' => 'Annual vacation leave',
            'balance_per_year' => 20,
            'max_consecutive_days' => 15,
            'requires_pre_approval' => false,
            'requires_documents' => false,
        ],
        'Sick' => [
            'name' => 'Sick',
            'description' => 'Sick leave',
            'balance_per_year' => 10,
            'max_consecutive_days' => 5,
            'requires_pre_approval' => false,
            'requires_documents' => true,
        ],
        'Maternity' => [
            'name' => 'Maternity',
            'description' => 'Maternity leave',
            'balance_per_year' => 0, // Special handling
            'max_consecutive_days' => 90,
            'requires_pre_approval' => true,
            'requires_documents' => true,
        ],
        'Paternity' => [
            'name' => 'Paternity',
            'description' => 'Paternity leave',
            'balance_per_year' => 0, // Special handling
            'max_consecutive_days' => 10,
            'requires_pre_approval' => true,
            'requires_documents' => true,
        ],
        'Unpaid' => [
            'name' => 'Unpaid',
            'description' => 'Unpaid leave',
            'balance_per_year' => 0, // Unlimited
            'max_consecutive_days' => 30,
            'requires_pre_approval' => true,
            'requires_documents' => false,
        ],
        'Compensatory' => [
            'name' => 'Compensatory',
            'description' => 'Compensatory leave for overtime',
            'balance_per_year' => 0, // Accrued
            'max_consecutive_days' => 10,
            'requires_pre_approval' => false,
            'requires_documents' => false,
        ],
    ];

    /**
     * Employee leave balances
     *
     * In production: SELECT * FROM leave_balances WHERE employee_id = ?
     */
    private static array $leaveBalances = [
        'EMP-2026-001' => [
            'Annual' => 15,          // Already used 5 days
            'Sick' => 8,
            'Maternity' => 0,
            'Paternity' => 0,
            'Unpaid' => 0,           // Unlimited
            'Compensatory' => 3,
        ],
        'EMP-2026-002' => [
            'Annual' => 18,
            'Sick' => 10,
            'Maternity' => 0,
            'Paternity' => 0,
            'Unpaid' => 100,          // Effectively unlimited, set high
            'Compensatory' => 0,
        ],
        'EMP-2026-003' => [
            'Annual' => 12,
            'Sick' => 5,
            'Maternity' => 0,
            'Paternity' => 0,
            'Unpaid' => 0,
            'Compensatory' => 0,
        ],
        'EMP-2026-004' => [
            'Annual' => 20,          // New employee, full balance
            'Sick' => 10,
            'Maternity' => 0,
            'Paternity' => 0,
            'Unpaid' => 100,
            'Compensatory' => 5,     // Has some compensatory leave
        ],
        'EMP-2026-005' => [
            'Annual' => 12,
            'Sick' => 7,
            'Maternity' => 0,
            'Paternity' => 0,
            'Unpaid' => 0,
            'Compensatory' => 1,
        ],
        'EMP-2026-006' => [
            'Annual' => 14,
            'Sick' => 9,
            'Maternity' => 0,
            'Paternity' => 0,
            'Unpaid' => 0,
            'Compensatory' => 0,
        ],
    ];

    /**
     * Existing approved leave requests
     *
     * In production: SELECT * FROM leave_requests WHERE status = 'Approved' AND employee_id = ?
     */
    private static array $approvedLeave = [
        'EMP-2026-001' => [
            [
                'id' => 'LR-2026-APPROVED-001',
                'employee_id' => 'EMP-2026-001',
                'leave_type' => 'Annual',
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-05',
                'status' => 'Approved',
                'created_at' => '2026-08-01T10:00:00Z',
            ],
        ],
        'EMP-2026-002' => [
            [
                'id' => 'LR-2026-APPROVED-002',
                'employee_id' => 'EMP-2026-002',
                'leave_type' => 'Sick',
                'start_date' => '2026-09-10',
                'end_date' => '2026-09-12',
                'status' => 'Approved',
                'created_at' => '2026-08-15T14:30:00Z',
            ],
        ],
        'EMP-2026-003' => [],
        'EMP-2026-004' => [],
        'EMP-2026-005' => [],
        'EMP-2026-006' => [],
    ];

    /**
     * Approval rules by leave type
     *
     * In production: Complex business logic with approval workflows
     */
    private static array $approvalRules = [
        'Annual' => [
            'approval_required' => false,
            'approver_role' => 'Manager',
            'auto_approve_days' => 3,  // Auto-approve if 3 days or less
        ],
        'Sick' => [
            'approval_required' => true,
            'approver_role' => 'Manager',
            'auto_approve_days' => 0,
        ],
        'Maternity' => [
            'approval_required' => true,
            'approver_role' => 'HR_Admin',
            'auto_approve_days' => 0,
        ],
        'Paternity' => [
            'approval_required' => true,
            'approver_role' => 'HR_Admin',
            'auto_approve_days' => 0,
        ],
        'Unpaid' => [
            'approval_required' => true,
            'approver_role' => 'HR_Admin',
            'auto_approve_days' => 0,
        ],
        'Compensatory' => [
            'approval_required' => false,
            'approver_role' => 'Manager',
            'auto_approve_days' => 0,
        ],
    ];

    /**
     * Get employee by ID
     *
     * @param string $employeeId
     * @return array|null
     */
    public static function getEmployee(string $employeeId): ?array
    {
        return self::$employees[$employeeId] ?? null;
    }

    /**
     * Get all employees
     *
     * @return array
     */
    public static function getAllEmployees(): array
    {
        return self::$employees;
    }

    /**
     * Check if employee exists and is active
     *
     * @param string $employeeId
     * @return bool
     */
    public static function isEmployeeActive(string $employeeId): bool
    {
        $employee = self::getEmployee($employeeId);
        return $employee !== null && $employee['status'] === 'Active';
    }

    /**
     * Get leave type definition
     *
     * @param string $leaveType
     * @return array|null
     */
    public static function getLeaveType(string $leaveType): ?array
    {
        return self::$leaveTypes[$leaveType] ?? null;
    }

    /**
     * Get all leave types
     *
     * @return array
     */
    public static function getAllLeaveTypes(): array
    {
        return self::$leaveTypes;
    }

    /**
     * Get employee leave balance
     *
     * @param string $employeeId
     * @param string $leaveType
     * @return int
     */
    public static function getLeaveBalance(string $employeeId, string $leaveType): int
    {
        return self::$leaveBalances[$employeeId][$leaveType] ?? 0;
    }

    /**
     * Get all balances for employee
     *
     * @param string $employeeId
     * @return array
     */
    public static function getEmployeeBalances(string $employeeId): array
    {
        return self::$leaveBalances[$employeeId] ?? [];
    }

    /**
     * Check for overlapping approved leave
     *
     * @param string $employeeId
     * @param string $startDate (YYYY-MM-DD)
     * @param string $endDate (YYYY-MM-DD)
     * @return array[] Overlapping leave requests
     */
    public static function getOverlappingApprovedLeave(
        string $employeeId,
        string $startDate,
        string $endDate
    ): array {
        $employeeLeave = self::$approvedLeave[$employeeId] ?? [];
        $overlapping = [];

        foreach ($employeeLeave as $leave) {
            if (self::datesOverlap($startDate, $endDate, $leave['start_date'], $leave['end_date'])) {
                $overlapping[] = $leave;
            }
        }

        return $overlapping;
    }

    /**
     * Check if two date ranges overlap
     *
     * @param string $start1 (YYYY-MM-DD)
     * @param string $end1 (YYYY-MM-DD)
     * @param string $start2 (YYYY-MM-DD)
     * @param string $end2 (YYYY-MM-DD)
     * @return bool
     */
    public static function datesOverlap(
        string $start1,
        string $end1,
        string $start2,
        string $end2
    ): bool {
        $start1 = \DateTime::createFromFormat('Y-m-d', $start1);
        $end1 = \DateTime::createFromFormat('Y-m-d', $end1);
        $start2 = \DateTime::createFromFormat('Y-m-d', $start2);
        $end2 = \DateTime::createFromFormat('Y-m-d', $end2);

        if (!$start1 || !$end1 || !$start2 || !$end2) {
            return false;
        }

        return !($end1 < $start2 || $end2 < $start1);
    }

    /**
     * Calculate days between dates (inclusive)
     *
     * @param string $startDate (YYYY-MM-DD)
     * @param string $endDate (YYYY-MM-DD)
     * @return int
     */
    public static function calculateDays(string $startDate, string $endDate): int
    {
        $start = \DateTime::createFromFormat('Y-m-d', $startDate);
        $end = \DateTime::createFromFormat('Y-m-d', $endDate);

        if (!$start || !$end) {
            return 0;
        }

        return (int)$end->diff($start)->days + 1;
    }

    /**
     * Get approval rule for leave type
     *
     * @param string $leaveType
     * @return array|null
     */
    public static function getApprovalRule(string $leaveType): ?array
    {
        return self::$approvalRules[$leaveType] ?? null;
    }

    /**
     * Get manager for employee
     *
     * @param string $employeeId
     * @return array|null
     */
    public static function getEmployeeManager(string $employeeId): ?array
    {
        $employee = self::getEmployee($employeeId);
        if (!$employee || !$employee['manager_id']) {
            return null;
        }

        return self::getEmployee($employee['manager_id']);
    }

    /**
     * Get all managers
     *
     * @return array
     */
    public static function getAllManagers(): array
    {
        $managers = [];
        foreach (self::$employees as $employee) {
            if ($employee['manager_id'] === null) {
                $managers[] = $employee;
            }
        }

        return $managers;
    }
}
