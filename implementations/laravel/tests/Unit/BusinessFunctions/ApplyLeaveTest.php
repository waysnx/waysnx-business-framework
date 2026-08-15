<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Unit\BusinessFunctions;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\BusinessFunctions\ApplyLeave;
use WaysNX\BusinessFramework\Runtime\BusinessFunctionRuntime;

/**
 * ApplyLeaveTest
 *
 * Comprehensive tests for the real HR.LEAVE.APPLY.APPLY_LEAVE business function.
 *
 * Tests validate:
 * - Request validation
 * - Authorization checks
 * - Business rules enforcement
 * - Successful leave request creation
 * - Error handling
 * - Event publishing
 * - Response contract compliance
 * - Runtime integration
 *
 * @package WaysNX\BusinessFramework\Tests\Unit\BusinessFunctions
 */
class ApplyLeaveTest extends TestCase
{
    private ApplyLeave $applyLeave;
    private BusinessFunctionRuntime $runtime;

    protected function setUp(): void
    {
        $this->applyLeave = new ApplyLeave();
        $this->runtime = new BusinessFunctionRuntime();
        ApplyLeave::clearCreatedRequests();
    }

    // ================================
    // GROUP 1: SUCCESSFUL SCENARIOS
    // ================================

    /**
     * Test 1: Valid annual leave request succeeds
     *
     * Validates the happy path: valid request from active employee with sufficient balance
     */
    public function test_valid_annual_leave_request_succeeds(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Annual',
            'startDate' => '2026-09-15',
            'endDate' => '2026-09-20', // 6 days - will auto-approve since > 3
        ];

        $caller = [
            'id' => 'EMP-2026-001',
            'role' => 'Employee',
        ];

        $response = $this->runtime->execute($this->applyLeave, $request, $caller);

        // Verify success
        $this->assertTrue($this->runtime->wasSuccessful());
        $this->assertArrayHasKey('leaveRequestId', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('remainingBalance', $response);
        $this->assertArrayHasKey('daysRequested', $response);

        // Verify response - 6 days needs approval so status is Pending unless auto-approved
        // Annual leave auto-approves if <= 3 days, so 6 days = Pending
        $this->assertSame(6, $response['daysRequested']); // 6 days: 15,16,17,18,19,20
        $this->assertSame(9, $response['remainingBalance']); // 15 - 6 = 9
    }

    /**
     * Test 2: Manager can apply leave for their direct report
     *
     * Tests authorization: Manager applying on behalf of subordinate
     */
    public function test_manager_can_apply_leave_for_direct_report(): void
    {
        // Jane Manager (EMP-2026-005) applying for Alice Johnson (EMP-2026-001)
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Sick',
            'startDate' => '2026-09-10',
            'endDate' => '2026-09-11',
        ];

        $caller = [
            'id' => 'EMP-2026-005', // Jane Manager
            'role' => 'Manager',
        ];

        $response = $this->runtime->execute($this->applyLeave, $request, $caller);

        $this->assertTrue($this->runtime->wasSuccessful());
        $this->assertSame('Pending', $response['status']);
    }

    /**
     * Test 3: HR Admin can apply leave for any employee
     *
     * Tests authorization: HR Admin has full permissions
     */
    public function test_hr_admin_can_apply_leave_for_any_employee(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-002',
            'leaveType' => 'Unpaid',
            'startDate' => '2026-09-22',
            'endDate' => '2026-09-25',
        ];

        $caller = [
            'id' => 'EMP-HR-ADMIN-001',
            'role' => 'HR_Admin',
        ];

        $response = $this->runtime->execute($this->applyLeave, $request, $caller);

        $this->assertTrue($this->runtime->wasSuccessful());
        $this->assertArrayHasKey('leaveRequestId', $response);
    }

    /**
     * Test 4: Short annual leave auto-approves
     *
     * Tests business logic: Annual leave ≤3 days auto-approves
     */
    public function test_short_annual_leave_auto_approves(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-004',
            'leaveType' => 'Annual',
            'startDate' => '2026-09-28',
            'endDate' => '2026-09-30', // 3 days
        ];

        $caller = [
            'id' => 'EMP-2026-004',
            'role' => 'Employee',
        ];

        $response = $this->runtime->execute($this->applyLeave, $request, $caller);

        $this->assertTrue($this->runtime->wasSuccessful());
        $this->assertSame('Approved', $response['status']);
        $this->assertSame(3, $response['daysRequested']);
    }

    /**
     * Test 5: Remaining balance is calculated correctly
     *
     * Verifies balance calculation: current - requested = remaining
     */
    public function test_remaining_balance_calculated_correctly(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-002', // Has 18 Annual days
            'leaveType' => 'Annual',
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-05', // 5 days
        ];

        $caller = [
            'id' => 'EMP-2026-002',
            'role' => 'Employee',
        ];

        $response = $this->runtime->execute($this->applyLeave, $request, $caller);

        $this->assertTrue($this->runtime->wasSuccessful());
        $this->assertSame(5, $response['daysRequested']);
        $this->assertSame(13, $response['remainingBalance']); // 18 - 5 = 13
    }

    /**
     * Test 6: Runtime can execute real ApplyLeave
     *
     * Verifies that the Runtime successfully orchestrates ApplyLeave
     */
    public function test_runtime_executes_real_apply_leave(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-004',
            'leaveType' => 'Compensatory',
            'startDate' => '2026-10-10',
            'endDate' => '2026-10-11',
        ];

        $caller = [
            'id' => 'EMP-2026-004',
            'role' => 'Employee',
        ];

        $response = $this->runtime->execute($this->applyLeave, $request, $caller);

        // Verify all phases succeeded
        $context = $this->runtime->getContext();
        $this->assertSame('success', $context['status']);
        $this->assertArrayHasKey('executionTime', $context);
        $this->assertGreaterThan(0, $context['executionTime']);

        // Verify response
        $this->assertSame('Approved', $response['status']);
    }

    // ================================
    // GROUP 2: VALIDATION FAILURES
    // ================================

    /**
     * Test 7: Invalid date format fails validation
     *
     * Validates date format checking in validateRequest()
     */
    public function test_invalid_date_format_fails_validation(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Annual',
            'startDate' => '15-09-2026', // Wrong format
            'endDate' => '2026-09-20',
        ];

        $caller = [
            'id' => 'EMP-2026-001',
            'role' => 'Employee',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Date format must be YYYY-MM-DD');

        $this->runtime->execute($this->applyLeave, $request, $caller);
    }

    /**
     * Test 8: Start date after end date fails validation
     *
     * Validates logical date constraints
     */
    public function test_start_date_after_end_date_fails_validation(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Annual',
            'startDate' => '2026-09-20',
            'endDate' => '2026-09-15', // Before start
        ];

        $caller = [
            'id' => 'EMP-2026-001',
            'role' => 'Employee',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('startDate cannot be after endDate');

        $this->runtime->execute($this->applyLeave, $request, $caller);
    }

    /**
     * Test 9: Past date fails validation
     *
     * Validates that leave cannot be requested for past dates
     */
    public function test_past_date_fails_validation(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Annual',
            'startDate' => '2026-01-01', // Past
            'endDate' => '2026-01-05',
        ];

        $caller = [
            'id' => 'EMP-2026-001',
            'role' => 'Employee',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('startDate cannot be in the past');

        $this->runtime->execute($this->applyLeave, $request, $caller);
    }

    /**
     * Test 10: Invalid leave type fails validation
     *
     * Validates leave type enum constraint
     */
    public function test_invalid_leave_type_fails_validation(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'InvalidType',
            'startDate' => '2026-09-15',
            'endDate' => '2026-09-20',
        ];

        $caller = [
            'id' => 'EMP-2026-001',
            'role' => 'Employee',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('leaveType must be one of');

        $this->runtime->execute($this->applyLeave, $request, $caller);
    }

    // ================================
    // GROUP 3: AUTHORIZATION FAILURES
    // ================================

    /**
     * Test 11: Employee cannot apply leave for other employees
     *
     * Validates authorization: Employee role scope
     */
    public function test_employee_cannot_apply_leave_for_others(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-002', // Different employee
            'leaveType' => 'Annual',
            'startDate' => '2026-09-15',
            'endDate' => '2026-09-20',
        ];

        $caller = [
            'id' => 'EMP-2026-001', // Different caller
            'role' => 'Employee',
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Employees can only apply leave for themselves');

        $this->runtime->execute($this->applyLeave, $request, $caller);
    }

    /**
     * Test 12: Manager cannot apply leave for non-reports
     *
     * Validates authorization: Manager scope limited to direct reports
     */
    public function test_manager_cannot_apply_leave_for_non_reports(): void
    {
        // Sales Manager (EMP-2026-006) trying to apply for Engineering employee (EMP-2026-001)
        $request = [
            'employeeId' => 'EMP-2026-001', // Not their report
            'leaveType' => 'Annual',
            'startDate' => '2026-09-15',
            'endDate' => '2026-09-20',
        ];

        $caller = [
            'id' => 'EMP-2026-006', // Sales Manager
            'role' => 'Manager',
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Managers can only apply leave for their direct reports');

        $this->runtime->execute($this->applyLeave, $request, $caller);
    }

    /**
     * Test 13: Missing caller context fails authorization
     *
     * Validates that authorization always requires caller context
     */
    public function test_missing_caller_context_fails_authorization(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Annual',
            'startDate' => '2026-09-15',
            'endDate' => '2026-09-20',
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Caller context required');

        $this->runtime->execute($this->applyLeave, $request, null);
    }

    // ================================
    // GROUP 4: BUSINESS RULE FAILURES
    // ================================

    /**
     * Test 14: Inactive employee cannot apply leave
     *
     * Validates business rule: Employee must be active
     */
    public function test_inactive_employee_cannot_apply_leave(): void
    {
        // Carol Davis (EMP-2026-003) has status = 'Inactive'
        $request = [
            'employeeId' => 'EMP-2026-003',
            'leaveType' => 'Annual',
            'startDate' => '2026-09-15',
            'endDate' => '2026-09-20',
        ];

        $caller = [
            'id' => 'EMP-2026-003',
            'role' => 'Employee',
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('is not active');

        $this->runtime->execute($this->applyLeave, $request, $caller);
    }

    /**
     * Test 15: Insufficient leave balance fails
     *
     * Validates business rule: Must have sufficient balance
     */
    public function test_insufficient_leave_balance_fails(): void
    {
        // Alice (EMP-2026-001) has only 15 Annual days
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Annual',
            'startDate' => '2026-09-15',
            'endDate' => '2026-10-05', // 21 days (more than available)
        ];

        $caller = [
            'id' => 'EMP-2026-001',
            'role' => 'Employee',
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Insufficient leave balance');

        $this->runtime->execute($this->applyLeave, $request, $caller);
    }

    /**
     * Test 16: Overlapping approved leave fails
     *
     * Validates business rule: No overlapping approved leave
     */
    public function test_overlapping_approved_leave_fails(): void
    {
        // Alice (EMP-2026-001) already has approved leave Sep 1-5
        // Trying to request Sep 3-8 (overlaps Sep 3-5)
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Annual',
            'startDate' => '2026-09-03',
            'endDate' => '2026-09-08',
        ];

        $caller = [
            'id' => 'EMP-2026-001',
            'role' => 'Employee',
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('overlaps with existing approved leave');

        $this->runtime->execute($this->applyLeave, $request, $caller);
    }

    /**
     * Test 17: Exceeds maximum consecutive days fails
     *
     * Validates business rule: Maximum consecutive days constraint
     */
    public function test_exceeds_maximum_consecutive_days_fails(): void
    {
        // Sick leave has max 5 consecutive days
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Sick',
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-07', // 7 days (max is 5)
        ];

        $caller = [
            'id' => 'EMP-2026-001',
            'role' => 'Employee',
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Maximum consecutive leave');

        $this->runtime->execute($this->applyLeave, $request, $caller);
    }

    /**
     * Test 18: Single day leave succeeds
     *
     * Edge case: Single day (start = end)
     */
    public function test_single_day_leave_succeeds(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-002',
            'leaveType' => 'Sick',
            'startDate' => '2026-09-20',
            'endDate' => '2026-09-20',
        ];

        $caller = [
            'id' => 'EMP-2026-002',
            'role' => 'Employee',
        ];

        $response = $this->runtime->execute($this->applyLeave, $request, $caller);

        $this->assertTrue($this->runtime->wasSuccessful());
        $this->assertSame(1, $response['daysRequested']);
    }

    // ================================
    // GROUP 5: EVENT AND RESPONSE BEHAVIOR
    // ================================

    /**
     * Test 19: LeaveRequested event published on success
     *
     * Validates that events are published after successful execution
     */
    public function test_leave_requested_event_published_on_success(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-004',
            'leaveType' => 'Annual',
            'startDate' => '2026-11-01',
            'endDate' => '2026-11-03',
        ];

        $caller = [
            'id' => 'EMP-2026-004',
            'role' => 'Employee',
        ];

        $this->runtime->execute($this->applyLeave, $request, $caller);

        $events = $this->runtime->getEvents();
        $this->assertNotEmpty($events);
        $this->assertSame('LeaveRequested', $events[0]['type']);
    }

    /**
     * Test 20: Response follows contract
     *
     * Validates response contract compliance
     */
    public function test_response_follows_contract(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-002',
            'leaveType' => 'Annual',
            'startDate' => '2026-11-15',
            'endDate' => '2026-11-18',
        ];

        $caller = [
            'id' => 'EMP-2026-002',
            'role' => 'Employee',
        ];

        $response = $this->runtime->execute($this->applyLeave, $request, $caller);

        // Contract fields (success_fields from BFD)
        $this->assertArrayHasKey('leaveRequestId', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('remainingBalance', $response);
        $this->assertArrayHasKey('daysRequested', $response);
        $this->assertArrayHasKey('approverName', $response);
        $this->assertArrayHasKey('createdAt', $response);

        // Contract compliance: values are correct type
        $this->assertIsString($response['leaveRequestId']);
        $this->assertIsString($response['status']);
        $this->assertIsInt($response['remainingBalance']);
        $this->assertIsInt($response['daysRequested']);
        $this->assertIsString($response['createdAt']);
    }

    /**
     * Test 21: BFD remains accurate after real execution
     *
     * Validates that the BFD is preserved and matches implementation
     */
    public function test_bfd_remains_accurate(): void
    {
        $bfd = $this->applyLeave->getCompleteContractDefinition();

        // Identity
        $this->assertSame('HR.LEAVE.APPLY.APPLY_LEAVE', $bfd['identity']['functionId']);
        $this->assertSame('Apply Leave', $bfd['identity']['functionName']);

        // Response Contract
        $successFields = $bfd['contract']['response']['success_fields'] ?? [];
        $this->assertContains('leaveRequestId', $successFields);
        $this->assertContains('status', $successFields);
        $this->assertContains('remainingBalance', $successFields);
        $this->assertContains('daysRequested', $successFields);

        // Events
        $this->assertContains('LeaveRequested', $bfd['metadata']['eventsPublished']);
    }

    // ================================
    // GROUP 6: STORED LEAVE REQUESTS
    // ================================

    /**
     * Test 22: Created leave request is stored
     *
     * Verifies that leave requests are persisted (in-memory for this test)
     */
    public function test_created_leave_request_is_stored(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-004',
            'leaveType' => 'Annual',
            'startDate' => '2026-11-25',
            'endDate' => '2026-11-27',
        ];

        $caller = [
            'id' => 'EMP-2026-004',
            'role' => 'Employee',
        ];

        $response = $this->runtime->execute($this->applyLeave, $request, $caller);
        $leaveRequestId = $response['leaveRequestId'];

        // Verify it's stored
        $stored = ApplyLeave::getCreatedRequest($leaveRequestId);
        $this->assertNotNull($stored);
        $this->assertSame('EMP-2026-004', $stored['employeeId']);
        $this->assertSame('Annual', $stored['leaveType']);
        $this->assertSame('2026-11-25', $stored['startDate']);
        $this->assertSame('2026-11-27', $stored['endDate']);
    }

    /**
     * Test 23: Multiple requests can be created
     *
     * Tests that multiple requests can be submitted independently
     */
    public function test_multiple_leave_requests_can_be_created(): void
    {
        $caller1 = ['id' => 'EMP-2026-001', 'role' => 'Employee'];
        $caller2 = ['id' => 'EMP-2026-002', 'role' => 'Employee'];

        // Request 1
        $request1 = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Annual',
            'startDate' => '2026-12-01',
            'endDate' => '2026-12-03',
        ];
        $response1 = $this->runtime->execute($this->applyLeave, $request1, $caller1);

        // Request 2
        $request2 = [
            'employeeId' => 'EMP-2026-002',
            'leaveType' => 'Sick',
            'startDate' => '2026-12-05',
            'endDate' => '2026-12-06',
        ];
        $response2 = $this->runtime->execute($this->applyLeave, $request2, $caller2);

        // Verify both stored
        $allRequests = ApplyLeave::getCreatedRequests();
        $this->assertGreaterThanOrEqual(2, count($allRequests));
        $this->assertArrayHasKey($response1['leaveRequestId'], $allRequests);
        $this->assertArrayHasKey($response2['leaveRequestId'], $allRequests);
    }
}
