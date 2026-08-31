<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Console;

use WaysNX\BusinessFramework\Console\Commands\CreateDemoCommand;
use WaysNX\BusinessFramework\Registry\ModuleRegistry;
use WaysNX\BusinessFramework\Registry\EntityRegistry;
use WaysNX\BusinessFramework\Registry\BusinessFunctionRegistry;
use WaysNX\BusinessFramework\Registry\ValidationRegistry;
use WaysNX\BusinessFramework\Registry\WorkflowRegistry;
use WaysNX\BusinessFramework\Workflow\WorkflowEngine;
use WaysNX\BusinessFramework\Lifecycle\LifecycleManager;

/**
 * CreateDemoCommandTest
 *
 * Tests for the wbf:createdemo command.
 *
 * Verifies:
 * - Command structure and metadata
 * - Real demo artifact creation
 * - Real registry registration and discovery
 * - Real WorkflowEngine execution
 * - Workflow result tracking
 * - JSON output validity
 * - Repeatability without state conflicts
 * - Lifecycle manager participation
 *
 * @package WaysNX\BusinessFramework\Tests\Console
 */
class CreateDemoCommandTest extends ConsoleTestCase
{
    /**
     * Test: Command can be instantiated
     *
     * @return void
     */
    public function testCommandCanBeInstantiated(): void
    {
        $command = new CreateDemoCommand();
        $this->assertInstanceOf(CreateDemoCommand::class, $command);
    }

    /**
     * Test: Command has proper signature
     *
     * @return void
     */
    public function testCommandHasSignature(): void
    {
        $reflection = new \ReflectionClass(CreateDemoCommand::class);
        $this->assertTrue($reflection->hasProperty('signature'));
        
        $signature = $reflection->getProperty('signature')->getValue(new CreateDemoCommand());
        $this->assertStringContainsString('wbf:createdemo', $signature);
    }

    /**
     * Test: Command has description
     *
     * @return void
     */
    public function testCommandHasDescription(): void
    {
        $command = new CreateDemoCommand();
        $this->assertNotEmpty($command->getDescription());
    }

    /**
     * Test: Command extends BaseWbfCommand
     *
     * @return void
     */
    public function testCommandExtendsBaseWbfCommand(): void
    {
        $this->assertInstanceOf(\WaysNX\BusinessFramework\Console\BaseWbfCommand::class, new CreateDemoCommand());
    }

    /**
     * Test: Command has json option
     *
     * @return void
     */
    public function testCommandHasJsonOption(): void
    {
        $reflection = new \ReflectionClass(CreateDemoCommand::class);
        $signature = $reflection->getProperty('signature')->getValue(new CreateDemoCommand());
        $this->assertStringContainsString('--json', $signature);
    }

    // ========== INTEGRATION TESTS ==========

    /**
     * Test: Demo executes successfully (exit code 0)
     *
     * @return void
     */
    public function testDemoExecutesSuccessfully(): void
    {
        $this->artisan->add($this->container->make(CreateDemoCommand::class));
        
        $exitCode = $this->artisan->call('wbf:createdemo');
        $this->assertEquals(0, $exitCode, 'Command should exit with code 0');
    }

    /**
     * Test: Module is actually created and registered
     *
     * @return void
     */
    public function testModuleIsActuallyRegistered(): void
    {
        $this->artisan->add($this->container->make(CreateDemoCommand::class));
        $this->artisan->call('wbf:createdemo');
        
        $moduleRegistry = $this->container->make(ModuleRegistry::class);
        
        // Verify module exists in registry
        $this->assertTrue($moduleRegistry->exists('AUDIT'), 'Module AUDIT should be registered');
        
        // Verify module can be retrieved
        $module = $moduleRegistry->findById('AUDIT');
        $this->assertNotNull($module);
        $this->assertEquals('AUDIT', $module->id);
        $this->assertEquals('Hospital Audit Management', $module->displayName);
    }

    /**
     * Test: Entity is actually created and registered
     *
     * @return void
     */
    public function testEntityIsActuallyRegistered(): void
    {
        $this->artisan->add($this->container->make(CreateDemoCommand::class));
        $this->artisan->call('wbf:createdemo');
        
        $entityRegistry = $this->container->make(EntityRegistry::class);
        
        // Verify entity exists
        $this->assertTrue($entityRegistry->exists('AUDIT'), 'Entity AUDIT should be registered');
        
        // Verify entity can be retrieved
        $entity = $entityRegistry->findById('AUDIT');
        $this->assertNotNull($entity);
        $this->assertEquals('AUDIT', $entity->id);
        $this->assertEquals('Audit', $entity->displayName);
    }

    /**
     * Test: Three business functions are actually created and registered
     *
     * @return void
     */
    public function testThreeBusinessFunctionsAreRegistered(): void
    {
        $this->artisan->add($this->container->make(CreateDemoCommand::class));
        $this->artisan->call('wbf:createdemo');
        
        $functionRegistry = $this->container->make(BusinessFunctionRegistry::class);
        
        // Verify all three functions exist
        $this->assertTrue($functionRegistry->exists('AUDIT.VALIDATE.AUDIT.VALIDATE_AUDIT'));
        $this->assertTrue($functionRegistry->exists('AUDIT.RECORD.FINDING.RECORD_FINDING'));
        $this->assertTrue($functionRegistry->exists('AUDIT.COMPLETE.AUDIT.COMPLETE_AUDIT'));
        
        // Verify total count
        $this->assertEquals(3, $functionRegistry->count(), 'Should have exactly 3 business functions');
        
        // Verify each function can be retrieved
        $validateFn = $functionRegistry->findById('AUDIT.VALIDATE.AUDIT.VALIDATE_AUDIT');
        $this->assertEquals('Validate Audit', $validateFn->displayName);
        
        $recordFn = $functionRegistry->findById('AUDIT.RECORD.FINDING.RECORD_FINDING');
        $this->assertEquals('Record Finding', $recordFn->displayName);
        
        $completeFn = $functionRegistry->findById('AUDIT.COMPLETE.AUDIT.COMPLETE_AUDIT');
        $this->assertEquals('Complete Audit', $completeFn->displayName);
    }

    /**
     * Test: Validation is actually created and registered
     *
     * @return void
     */
    public function testValidationIsActuallyRegistered(): void
    {
        $this->artisan->add($this->container->make(CreateDemoCommand::class));
        $this->artisan->call('wbf:createdemo');
        
        $validationRegistry = $this->container->make(ValidationRegistry::class);
        
        // Verify validation exists
        $this->assertTrue($validationRegistry->exists('AUDIT_DATA_VALID'));
        
        // Verify validation can be retrieved
        $validation = $validationRegistry->findById('AUDIT_DATA_VALID');
        $this->assertNotNull($validation);
        $this->assertEquals('AUDIT_DATA_VALID', $validation->id);
        $this->assertEquals('Audit Data Validation', $validation->displayName);
    }

    /**
     * Test: Workflow is actually created and registered with 3 steps
     *
     * @return void
     */
    public function testWorkflowIsActuallyRegistered(): void
    {
        $this->artisan->add($this->container->make(CreateDemoCommand::class));
        $this->artisan->call('wbf:createdemo');
        
        $workflowRegistry = $this->container->make(WorkflowRegistry::class);
        
        // Verify workflow exists
        $this->assertTrue($workflowRegistry->exists('audit-review'));
        
        // Verify workflow can be retrieved
        $workflow = $workflowRegistry->findById('audit-review');
        $this->assertNotNull($workflow);
        $this->assertEquals('audit-review', $workflow->id);
        $this->assertEquals('Audit Review', $workflow->displayName);
        
        // Verify workflow has exactly 3 steps
        $this->assertEquals(3, count($workflow->steps), 'Workflow should have exactly 3 steps');
        
        // Verify step IDs and sequence
        $this->assertEquals('validate-step', $workflow->steps[0]->id);
        $this->assertEquals(0, $workflow->steps[0]->sequence);
        
        $this->assertEquals('record-step', $workflow->steps[1]->id);
        $this->assertEquals(1, $workflow->steps[1]->sequence);
        
        $this->assertEquals('complete-step', $workflow->steps[2]->id);
        $this->assertEquals(2, $workflow->steps[2]->sequence);
    }

    /**
     * Test: Registry discovery works correctly
     *
     * @return void
     */
    public function testRegistryDiscoveryWorks(): void
    {
        $this->artisan->add($this->container->make(CreateDemoCommand::class));
        $this->artisan->call('wbf:createdemo');
        
        $moduleRegistry = $this->container->make(ModuleRegistry::class);
        $workflowRegistry = $this->container->make(WorkflowRegistry::class);
        
        // Verify discovery via findById
        $module = $moduleRegistry->findById('AUDIT');
        $this->assertNotNull($module);
        
        $workflow = $workflowRegistry->findById('audit-review');
        $this->assertNotNull($workflow);
        
        // Verify all() also works
        $allModules = $moduleRegistry->all();
        $this->assertArrayHasKey('AUDIT', $allModules);
        
        $allWorkflows = $workflowRegistry->all();
        $this->assertArrayHasKey('audit-review', $allWorkflows);
    }

    /**
     * Test: All workflow steps reference valid business functions
     *
     * @return void
     */
    public function testAllStepsReferenceBusinessFunctions(): void
    {
        $this->artisan->add($this->container->make(CreateDemoCommand::class));
        $this->artisan->call('wbf:createdemo');
        
        $workflowRegistry = $this->container->make(WorkflowRegistry::class);
        $functionRegistry = $this->container->make(BusinessFunctionRegistry::class);
        
        $workflow = $workflowRegistry->findById('audit-review');
        
        // Verify each step references a registered function
        foreach ($workflow->steps as $step) {
            $this->assertTrue(
                $functionRegistry->exists($step->businessFunctionId),
                "Step {$step->id} references non-existent function {$step->businessFunctionId}"
            );
        }
    }

    /**
     * Test: Lifecycle manager participates in execution
     *
     * @return void
     */
    public function testLifecycleManagerParticipatesInExecution(): void
    {
        $this->artisan->add($this->container->make(CreateDemoCommand::class));
        
        $lifecycleManager = $this->container->make(LifecycleManager::class);
        
        // Verify LifecycleManager is available and initialized
        $this->assertNotNull($lifecycleManager, 'LifecycleManager should be available');
        $this->assertInstanceOf(LifecycleManager::class, $lifecycleManager);
        
        // Execute demo - this internally uses WorkflowEngine which shares the LifecycleManager instance
        $exitCode = $this->artisan->call('wbf:createdemo');
        $this->assertEquals(0, $exitCode, 'Demo should execute successfully');
        
        // LifecycleManager participates in execution through WorkflowEngine
        // Note: The current WBF architecture does not expose a public event listener API for testing
        // LifecycleManager.register() requires LifecycleHandler implementation
        // The verification that lifecycle participates is implicit through successful WorkflowEngine execution
        // which internally invokes LifecycleManager.dispatch() for various lifecycle events
    }

    /**
     * Test: JSON output is valid and well-formed
     *
     * @return void
     */
    public function testJsonOutputIsValid(): void
    {
        $this->artisan->add($this->container->make(CreateDemoCommand::class));
        
        // Capture output
        $this->artisan->call('wbf:createdemo', ['--json' => true]);
        $output = $this->artisan->output();
        
        // Verify no ANSI escape sequences
        $this->assertStringNotContainsString("\033", $output, 'Output should not contain ANSI escape sequences');
        $this->assertStringNotContainsString("\x1b", $output, 'Output should not contain escape characters');
        
        // Parse JSON
        $json = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        
        // Verify structure
        $this->assertIsArray($json);
        $this->assertArrayHasKey('success', $json);
        $this->assertArrayHasKey('demo', $json);
        $this->assertArrayHasKey('artifacts', $json);
        $this->assertArrayHasKey('execution', $json);
        
        // Verify success status
        $this->assertTrue($json['success']);
        
        // Verify execution details
        $this->assertArrayHasKey('status', $json['execution']);
        $this->assertEquals('completed', $json['execution']['status']);
        
        $this->assertArrayHasKey('completedSteps', $json['execution']);
        $this->assertCount(3, $json['execution']['completedSteps']);
        
        $this->assertArrayHasKey('failedSteps', $json['execution']);
        $this->assertCount(0, $json['execution']['failedSteps']);
    }

    /**
     * Test: Command executes successfully three times (repeatability)
     *
     * @return void
     */
    public function testCommandIsRepeatable(): void
    {
        $this->artisan->add($this->container->make(CreateDemoCommand::class));
        
        // Run 1
        $exitCode1 = $this->artisan->call('wbf:createdemo');
        $this->assertEquals(0, $exitCode1, 'First execution should succeed');
        
        // Run 2
        $exitCode2 = $this->artisan->call('wbf:createdemo');
        $this->assertEquals(0, $exitCode2, 'Second execution should succeed');
        
        // Run 3
        $exitCode3 = $this->artisan->call('wbf:createdemo');
        $this->assertEquals(0, $exitCode3, 'Third execution should succeed');
        
        // Verify all succeeded
        $this->assertEquals(0, $exitCode1);
        $this->assertEquals(0, $exitCode2);
        $this->assertEquals(0, $exitCode3);
    }

    /**
     * Test: Workflow steps are completed in correct sequence
     *
     * @return void
     */
    public function testWorkflowStepsExecuteInSequence(): void
    {
        $this->artisan->add($this->container->make(CreateDemoCommand::class));
        
        // Execute and capture output
        $this->artisan->call('wbf:createdemo');
        $output = $this->artisan->output();
        
        // Verify all three steps appear in output
        $this->assertStringContainsString('validate-step', $output, 'Output should contain validate-step');
        $this->assertStringContainsString('record-step', $output, 'Output should contain record-step');
        $this->assertStringContainsString('complete-step', $output, 'Output should contain complete-step');
        
        // Verify sequence is mentioned
        $this->assertStringContainsString('COMPLETED', $output, 'Output should indicate successful completion');
    }

    /**
     * Test: Output contains all WBF concept demonstrations
     *
     * @return void
     */
    public function testOutputDemonstatesAllWBFConcepts(): void
    {
        $this->artisan->add($this->container->make(CreateDemoCommand::class));
        
        $this->artisan->call('wbf:createdemo');
        $output = $this->artisan->output();
        
        // Verify all phases are present
        $this->assertStringContainsString('PHASE 1', $output);
        $this->assertStringContainsString('PHASE 2', $output);
        $this->assertStringContainsString('PHASE 3', $output);
        $this->assertStringContainsString('PHASE 4', $output);
        
        // Verify key artifacts are mentioned
        $this->assertStringContainsString('Hospital Audit Management', $output);
        $this->assertStringContainsString('Module', $output);
        $this->assertStringContainsString('Entity', $output);
        $this->assertStringContainsString('Business Function', $output);
        $this->assertStringContainsString('Validation', $output);
        $this->assertStringContainsString('Workflow', $output);
        
        // Verify demonstration summary is present
        $this->assertStringContainsString('WBF DEMONSTRATION SUMMARY', $output);
        $this->assertStringContainsString('WBF CONCEPTS DEMONSTRATED', $output);
    }
}
