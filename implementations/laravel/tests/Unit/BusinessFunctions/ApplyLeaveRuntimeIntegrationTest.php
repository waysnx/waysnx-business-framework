<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Unit\BusinessFunctions;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\BusinessFunctions\ApplyLeave;
use WaysNX\BusinessFramework\Runtime\BusinessFunctionRuntime;

/**
 * ApplyLeaveRuntimeIntegrationTest
 *
 * Integration tests proving the Runtime successfully orchestrates real ApplyLeave.
 *
 * Purpose: Demonstrate that the existing Runtime (created in the Runtime Experiment)
 * works seamlessly with a real, complex BusinessFunction implementation.
 *
 * @package WaysNX\BusinessFramework\Tests\Unit\BusinessFunctions
 */
class ApplyLeaveRuntimeIntegrationTest extends TestCase
{
    private ApplyLeave $applyLeave;
    private BusinessFunctionRuntime $runtime;

    protected function setUp(): void
    {
        $this->applyLeave = new ApplyLeave();
        $this->runtime = new BusinessFunctionRuntime();
        ApplyLeave::clearCreatedRequests();
    }

    /**
     * Test 1: Runtime orchestrates complete pipeline
     *
     * Validates that Runtime's execute() method successfully coordinates
     * all six pipeline phases with real ApplyLeave.
     */
    public function test_runtime_orchestrates_complete_pipeline(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Annual',
            'startDate' => '2026-10-15',
            'endDate' => '2026-10-18',
            'reason' => 'Vacation',
        ];

        $caller = [
            'id' => 'EMP-2026-001',
            'role' => 'Employee',
        ];

        // Execute through Runtime
        $response = $this->runtime->execute($this->applyLeave, $request, $caller);

        // Verify all phases completed
        $this->assertTrue($this->runtime->wasSuccessful());

        // Verify context shows execution occurred
        $context = $this->runtime->getContext();
        $this->assertSame('success', $context['status']);
        $this->assertSame('HR.LEAVE.APPLY.APPLY_LEAVE', $context['functionId']);
        $this->assertGreaterThan(0, $context['executionTime']);

        // Verify response is valid
        $this->assertArrayHasKey('leaveRequestId', $response);
        $this->assertSame(4, $response['daysRequested']);
    }

    /**
     * Test 2: Runtime handles validation failure
     *
     * Validates that Runtime correctly propagates validation errors.
     */
    public function test_runtime_handles_validation_failure(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Annual',
            'startDate' => '2026-09-20',
            'endDate' => '2026-09-15', // Invalid: end before start
        ];

        $caller = [
            'id' => 'EMP-2026-001',
            'role' => 'Employee',
        ];

        try {
            $this->runtime->execute($this->applyLeave, $request, $caller);
            $this->fail('Expected InvalidArgumentException');
        } catch (\InvalidArgumentException $e) {
            // Runtime should propagate the exception
            $this->assertStringContainsString('startDate cannot be after endDate', $e->getMessage());
        }

        // Verify context reflects failure
        $context = $this->runtime->getContext();
        $this->assertSame('failed', $context['status']);
        $this->assertFalse($this->runtime->wasSuccessful());
    }

    /**
     * Test 3: Runtime handles authorization failure
     *
     * Validates that Runtime correctly propagates authorization errors.
     */
    public function test_runtime_handles_authorization_failure(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Annual',
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-03',
        ];

        $caller = [
            'id' => 'EMP-2026-002', // Different employee
            'role' => 'Employee',
        ];

        try {
            $this->runtime->execute($this->applyLeave, $request, $caller);
            $this->fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            // Runtime should propagate the exception
            $this->assertStringContainsString('Employees can only apply leave for themselves', $e->getMessage());
        }

        // Verify context reflects failure
        $this->assertFalse($this->runtime->wasSuccessful());
    }

    /**
     * Test 4: Runtime handles business rule failure
     *
     * Validates that Runtime correctly propagates business rule violations.
     */
    public function test_runtime_handles_business_rule_failure(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-003', // Inactive employee
            'leaveType' => 'Annual',
            'startDate' => '2026-10-05',
            'endDate' => '2026-10-08',
        ];

        $caller = [
            'id' => 'EMP-2026-003',
            'role' => 'Employee',
        ];

        try {
            $this->runtime->execute($this->applyLeave, $request, $caller);
            $this->fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            // Runtime should propagate the exception
            $this->assertStringContainsString('is not active', $e->getMessage());
        }

        // Verify context reflects failure
        $this->assertFalse($this->runtime->wasSuccessful());
    }

    /**
     * Test 5: Runtime collects events after successful execution
     *
     * Validates that Runtime properly observes events published by ApplyLeave.
     */
    public function test_runtime_collects_events_after_success(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-002',
            'leaveType' => 'Annual',
            'startDate' => '2026-10-20',
            'endDate' => '2026-10-22',
        ];

        $caller = [
            'id' => 'EMP-2026-002',
            'role' => 'Employee',
        ];

        $this->runtime->execute($this->applyLeave, $request, $caller);

        // Runtime should collect the LeaveRequested event
        $events = $this->runtime->getEvents();
        $this->assertNotEmpty($events);
        $this->assertSame('LeaveRequested', $events[0]['type']);
    }

    /**
     * Test 6: Runtime does not collect events on failure
     *
     * Validates that Runtime only collects events after successful execution.
     */
    public function test_runtime_does_not_collect_events_on_failure(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Annual',
            'startDate' => '2026-01-01', // Past date
            'endDate' => '2026-01-05',
        ];

        $caller = [
            'id' => 'EMP-2026-001',
            'role' => 'Employee',
        ];

        try {
            $this->runtime->execute($this->applyLeave, $request, $caller);
        } catch (\InvalidArgumentException) {
            // Expected
        }

        // Events should be empty since execution failed
        $events = $this->runtime->getEvents();
        $this->assertEmpty($events);
    }

    /**
     * Test 7: Runtime captures execution metrics
     *
     * Validates that Runtime properly tracks execution metrics.
     */
    public function test_runtime_captures_execution_metrics(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-004',
            'leaveType' => 'Sick',
            'startDate' => '2026-11-01',
            'endDate' => '2026-11-02',
        ];

        $caller = [
            'id' => 'EMP-2026-004',
            'role' => 'Employee',
        ];

        $this->runtime->execute($this->applyLeave, $request, $caller);

        $context = $this->runtime->getContext();

        // Verify all metrics are captured
        $this->assertArrayHasKey('functionId', $context);
        $this->assertArrayHasKey('startTime', $context);
        $this->assertArrayHasKey('endTime', $context);
        $this->assertArrayHasKey('executionTime', $context);
        $this->assertArrayHasKey('status', $context);

        // Verify execution time is meaningful
        $this->assertGreaterThan(0, $context['executionTime']);
        $this->assertLessThan(1, $context['executionTime']); // Should be fast
    }

    /**
     * Test 8: Multiple sequential executions work independently
     *
     * Validates that Runtime can handle multiple sequential calls
     * without state pollution.
     */
    public function test_multiple_sequential_executions(): void
    {
        // First execution
        $request1 = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Annual',
            'startDate' => '2026-11-10',
            'endDate' => '2026-11-12',
        ];

        $caller1 = ['id' => 'EMP-2026-001', 'role' => 'Employee'];
        $response1 = $this->runtime->execute($this->applyLeave, $request1, $caller1);

        $this->assertTrue($this->runtime->wasSuccessful());
        $events1 = $this->runtime->getEvents();
        $this->assertNotEmpty($events1);

        // Second execution (new Runtime instance)
        $runtime2 = new BusinessFunctionRuntime();
        $request2 = [
            'employeeId' => 'EMP-2026-002',
            'leaveType' => 'Sick',
            'startDate' => '2026-11-15',
            'endDate' => '2026-11-16',
        ];

        $caller2 = ['id' => 'EMP-2026-002', 'role' => 'Employee'];
        $response2 = $runtime2->execute($this->applyLeave, $request2, $caller2);

        $this->assertTrue($runtime2->wasSuccessful());

        // Verify responses are different
        $this->assertNotSame($response1['leaveRequestId'], $response2['leaveRequestId']);

        // Verify events are tracked separately
        $events2 = $runtime2->getEvents();
        $this->assertNotEmpty($events2);
    }

    /**
     * Test 9: Runtime works with different caller roles
     *
     * Validates that Runtime properly passes caller context through
     * the authorization phase.
     */
    public function test_runtime_works_with_different_caller_roles(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-001',
            'leaveType' => 'Sick',
            'startDate' => '2026-11-20',
            'endDate' => '2026-11-21',
        ];

        // Employee caller
        $employee = ['id' => 'EMP-2026-001', 'role' => 'Employee'];
        $response1 = $this->runtime->execute($this->applyLeave, $request, $employee);
        $this->assertTrue($this->runtime->wasSuccessful());

        // Manager caller
        $runtime2 = new BusinessFunctionRuntime();
        $manager = ['id' => 'EMP-2026-005', 'role' => 'Manager'];
        $response2 = $runtime2->execute($this->applyLeave, $request, $manager);
        $this->assertTrue($runtime2->wasSuccessful());

        // HR Admin caller
        $runtime3 = new BusinessFunctionRuntime();
        $admin = ['id' => 'EMP-HR-001', 'role' => 'HR_Admin'];
        $response3 = $runtime3->execute($this->applyLeave, $request, $admin);
        $this->assertTrue($runtime3->wasSuccessful());
    }

    /**
     * Test 10: Runtime preserves response contract
     *
     * Validates that the response passed through Runtime matches
     * the ApplyLeave response contract.
     */
    public function test_runtime_preserves_response_contract(): void
    {
        $request = [
            'employeeId' => 'EMP-2026-004',
            'leaveType' => 'Annual',
            'startDate' => '2026-12-01',
            'endDate' => '2026-12-03',
        ];

        $caller = ['id' => 'EMP-2026-004', 'role' => 'Employee'];
        $response = $this->runtime->execute($this->applyLeave, $request, $caller);

        // Verify response contract fields
        $this->assertIsString($response['leaveRequestId']);
        $this->assertIsString($response['status']);
        $this->assertIsInt($response['remainingBalance']);
        $this->assertIsInt($response['daysRequested']);
        $this->assertIsString($response['createdAt']);

        // Verify response values make sense
        $this->assertTrue(strlen($response['leaveRequestId']) > 0);
        $this->assertContains($response['status'], ['Pending', 'Approved']);
        $this->assertGreaterThanOrEqual(0, $response['remainingBalance']);
        $this->assertGreaterThan(0, $response['daysRequested']);
    }
}
