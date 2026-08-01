<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Registry;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Exceptions\DuplicateWorkflowException;
use WaysNX\BusinessFramework\Exceptions\WorkflowNotFoundException;
use WaysNX\BusinessFramework\Registry\WorkflowDefinition;
use WaysNX\BusinessFramework\Registry\WorkflowRegistry;
use WaysNX\BusinessFramework\Registry\WorkflowStep;

/**
 * WorkflowRegistryTest
 *
 * Comprehensive test suite for WorkflowRegistry.
 *
 * Test Coverage:
 * - Registration and unregistration
 * - Duplicate detection (ID, name, step)
 * - Lookup by ID, name, alias, module, trigger
 * - Filtering (enabled, disabled, category, tag)
 * - Metadata and entity type queries
 * - Alias management
 * - Edge cases and error conditions
 * - Extension points and hooks
 *
 * @package WaysNX\BusinessFramework\Tests\Registry
 */
class WorkflowRegistryTest extends TestCase
{
    /**
     * Test registry instance
     *

     * @var WorkflowRegistry
     */
    private WorkflowRegistry $registry;

    /**
     * Set up test fixtures
     *

     * @return void
     */
    protected function setUp(): void
    {
        $this->registry = new WorkflowRegistry();
    }

    /**
     * Create a test workflow definition
     *

     * @param string $id The workflow ID
     * @param array $options Additional options
     *

     * @return WorkflowDefinition
     */
    private function createWorkflow(string $id, array $options = []): WorkflowDefinition
    {
        return new WorkflowDefinition(
            id: $id,
            name: $options['name'] ?? ucfirst($id),
            displayName: $options['displayName'] ?? ucfirst($id),
            moduleId: $options['moduleId'] ?? '',
            triggerType: $options['triggerType'] ?? 'manual',
            triggerEvent: $options['triggerEvent'] ?? '',
            steps: $options['steps'] ?? [],
            enabled: $options['enabled'] ?? true,
            tags: $options['tags'] ?? [],
            metadata: $options['metadata'] ?? []
        );
    }

    /**
     * Test registering a single workflow
     *

     * @return void
     */
    public function testRegisterSingleWorkflow(): void
    {
        $workflow = $this->createWorkflow('onboarding');
        $result = $this->registry->register($workflow);

        $this->assertSame($this->registry, $result);
        $this->assertTrue($this->registry->exists('onboarding'));
        $this->assertSame(1, $this->registry->count());
    }

    /**
     * Test registering multiple workflows
     *

     * @return void
     */
    public function testRegisterMultipleWorkflows(): void
    {
        $this->registry->register($this->createWorkflow('onboarding'));
        $this->registry->register($this->createWorkflow('approval'));

        $this->assertSame(2, $this->registry->count());
        $this->assertTrue($this->registry->exists('onboarding'));
        $this->assertTrue($this->registry->exists('approval'));
    }

    /**
     * Test registering duplicate ID throws exception
     *

     * @return void
     */
    public function testRegisterDuplicateIdThrowsException(): void
    {
        $workflow = $this->createWorkflow('onboarding');
        $this->registry->register($workflow);

        $this->expectException(DuplicateWorkflowException::class);
        $this->expectExceptionMessage("Workflow with ID 'onboarding' is already registered");

        $this->registry->register($workflow);
    }

    /**
     * Test registering duplicate name throws exception
     *

     * @return void
     */
    public function testRegisterDuplicateNameThrowsException(): void
    {
        $workflow1 = $this->createWorkflow('onboarding', ['name' => 'Onboarding']);
        $workflow2 = $this->createWorkflow('onboarding-alt', ['name' => 'Onboarding']);

        $this->registry->register($workflow1);

        $this->expectException(DuplicateWorkflowException::class);
        $this->expectExceptionMessage("Workflow with name 'Onboarding' is already registered");

        $this->registry->register($workflow2);
    }

    /**
     * Test registering duplicate step ID throws exception
     *

     * @return void
     */
    public function testRegisterDuplicateStepIdThrowsException(): void
    {
        $step1 = new WorkflowStep('step-1', 'function-1');
        $step2 = new WorkflowStep('step-1', 'function-2');

        $workflow = $this->createWorkflow('test', ['steps' => [$step1, $step2]]);

        $this->expectException(DuplicateWorkflowException::class);
        $this->expectExceptionMessage("Step ID 'step-1' is not unique");

        $this->registry->register($workflow);
    }

    /**
     * Test finding workflow by ID
     *

     * @return void
     */
    public function testFindById(): void
    {
        $workflow = $this->createWorkflow('onboarding');
        $this->registry->register($workflow);

        $found = $this->registry->findById('onboarding');

        $this->assertSame('onboarding', $found->id);
    }

    /**
     * Test finding non-existent workflow by ID throws exception
     *

     * @return void
     */
    public function testFindByIdNotFoundThrowsException(): void
    {
        $this->expectException(WorkflowNotFoundException::class);
        $this->expectExceptionMessage("Workflow with ID 'onboarding' not found");

        $this->registry->findById('onboarding');
    }

    /**
     * Test finding workflow by name
     *

     * @return void
     */
    public function testFindByName(): void
    {
        $workflow = $this->createWorkflow('onboarding', ['name' => 'EmployeeOnboarding']);
        $this->registry->register($workflow);

        $found = $this->registry->findByName('EmployeeOnboarding');

        $this->assertSame('onboarding', $found->id);
    }

    /**
     * Test finding non-existent workflow by name throws exception
     *

     * @return void
     */
    public function testFindByNameNotFoundThrowsException(): void
    {
        $this->expectException(WorkflowNotFoundException::class);
        $this->expectExceptionMessage("Workflow with name 'EmployeeOnboarding' not found");

        $this->registry->findByName('EmployeeOnboarding');
    }

    /**
     * Test unregistering a workflow
     *

     * @return void
     */
    public function testUnregisterWorkflow(): void
    {
        $workflow = $this->createWorkflow('onboarding');
        $this->registry->register($workflow);
        $this->assertTrue($this->registry->exists('onboarding'));

        $result = $this->registry->unregister('onboarding');

        $this->assertSame($this->registry, $result);
        $this->assertFalse($this->registry->exists('onboarding'));
        $this->assertSame(0, $this->registry->count());
    }

    /**
     * Test unregistering non-existent workflow throws exception
     *

     * @return void
     */
    public function testUnregisterNonExistentThrowsException(): void
    {
        $this->expectException(WorkflowNotFoundException::class);

        $this->registry->unregister('onboarding');
    }

    /**
     * Test getting all workflows
     *

     * @return void
     */
    public function testGetAllWorkflows(): void
    {
        $this->registry->register($this->createWorkflow('onboarding'));
        $this->registry->register($this->createWorkflow('approval'));

        $all = $this->registry->all();

        $this->assertCount(2, $all);
        $this->assertArrayHasKey('onboarding', $all);
        $this->assertArrayHasKey('approval', $all);
    }

    /**
     * Test finding workflows by module
     *

     * @return void
     */
    public function testFindByModule(): void
    {
        $this->registry->register($this->createWorkflow('onboarding', ['moduleId' => 'hr']));
        $this->registry->register($this->createWorkflow('approval', ['moduleId' => 'hr']));
        $this->registry->register($this->createWorkflow('invoice', ['moduleId' => 'accounting']));

        $hrWorkflows = $this->registry->findByModule('hr');

        $this->assertCount(2, $hrWorkflows);
        $this->assertArrayHasKey('onboarding', $hrWorkflows);
        $this->assertArrayHasKey('approval', $hrWorkflows);
    }

    /**
     * Test finding workflows by trigger type
     *

     * @return void
     */
    public function testFindByTrigger(): void
    {
        $this->registry->register($this->createWorkflow('onboarding', ['triggerType' => 'event']));
        $this->registry->register($this->createWorkflow('approval', ['triggerType' => 'manual']));
        $this->registry->register($this->createWorkflow('report', ['triggerType' => 'event']));

        $eventWorkflows = $this->registry->findByTrigger('event');

        $this->assertCount(2, $eventWorkflows);
        $this->assertArrayHasKey('onboarding', $eventWorkflows);
        $this->assertArrayHasKey('report', $eventWorkflows);
    }

    /**
     * Test finding workflows by trigger event
     *

     * @return void
     */
    public function testFindByTriggerEvent(): void
    {
        $this->registry->register($this->createWorkflow('onboarding', ['triggerEvent' => 'employee.created']));
        $this->registry->register($this->createWorkflow('update', ['triggerEvent' => 'employee.created']));
        $this->registry->register($this->createWorkflow('delete', ['triggerEvent' => 'employee.deleted']));

        $createdWorkflows = $this->registry->findByTriggerEvent('employee.created');

        $this->assertCount(2, $createdWorkflows);
        $this->assertArrayHasKey('onboarding', $createdWorkflows);
        $this->assertArrayHasKey('update', $createdWorkflows);
    }

    /**
     * Test finding workflows by category
     *

     * @return void
     */
    public function testFindByCategory(): void
    {
        $this->registry->register($this->createWorkflow('onboarding', ['category' => 'hr-processes']));
        $this->registry->register($this->createWorkflow('approval', ['category' => 'finance-processes']));

        $hrWorkflows = $this->registry->findByCategory('hr-processes');

        $this->assertCount(1, $hrWorkflows);
        $this->assertArrayHasKey('onboarding', $hrWorkflows);
    }

    /**
     * Test finding workflows by tag
     *

     * @return void
     */
    public function testFindByTag(): void
    {
        $this->registry->register($this->createWorkflow('onboarding', ['tags' => ['core', 'hr']]));
        $this->registry->register($this->createWorkflow('approval', ['tags' => ['core', 'finance']]));
        $this->registry->register($this->createWorkflow('report', ['tags' => ['finance']]));

        $coreWorkflows = $this->registry->findByTag('core');

        $this->assertCount(2, $coreWorkflows);
        $this->assertArrayHasKey('onboarding', $coreWorkflows);
        $this->assertArrayHasKey('approval', $coreWorkflows);
    }

    /**
     * Test getting enabled workflows
     *

     * @return void
     */
    public function testGetEnabledWorkflows(): void
    {
        $this->registry->register($this->createWorkflow('onboarding', ['enabled' => true]));
        $this->registry->register($this->createWorkflow('disabled', ['enabled' => false]));

        $enabledWorkflows = $this->registry->enabled();

        $this->assertCount(1, $enabledWorkflows);
        $this->assertArrayHasKey('onboarding', $enabledWorkflows);
    }

    /**
     * Test getting disabled workflows
     *

     * @return void
     */
    public function testGetDisabledWorkflows(): void
    {
        $this->registry->register($this->createWorkflow('onboarding', ['enabled' => true]));
        $this->registry->register($this->createWorkflow('disabled', ['enabled' => false]));

        $disabledWorkflows = $this->registry->disabled();

        $this->assertCount(1, $disabledWorkflows);
        $this->assertArrayHasKey('disabled', $disabledWorkflows);
    }

    /**
     * Test adding an alias
     *

     * @return void
     */
    public function testAddAlias(): void
    {
        $this->registry->register($this->createWorkflow('onboarding'));

        $result = $this->registry->alias('onboarding', 'emp-onboarding');

        $this->assertSame($this->registry, $result);
        $this->assertTrue($this->registry->aliasExists('emp-onboarding'));
    }

    /**
     * Test finding workflow by alias
     *

     * @return void
     */
    public function testFindByAlias(): void
    {
        $this->registry->register($this->createWorkflow('onboarding'));
        $this->registry->alias('onboarding', 'emp-onboarding');

        $found = $this->registry->findByAlias('emp-onboarding');

        $this->assertSame('onboarding', $found->id);
    }

    /**
     * Test adding duplicate alias throws exception
     *

     * @return void
     */
    public function testAddDuplicateAliasThrowsException(): void
    {
        $this->registry->register($this->createWorkflow('onboarding'));
        $this->registry->alias('onboarding', 'emp-onboarding');

        $this->expectException(DuplicateWorkflowException::class);
        $this->expectExceptionMessage("Alias 'emp-onboarding' is already registered");

        $this->registry->alias('onboarding', 'emp-onboarding');
    }

    /**
     * Test removing an alias
     *

     * @return void
     */
    public function testRemoveAlias(): void
    {
        $this->registry->register($this->createWorkflow('onboarding'));
        $this->registry->alias('onboarding', 'emp-onboarding');

        $result = $this->registry->removeAlias('emp-onboarding');

        $this->assertSame($this->registry, $result);
        $this->assertFalse($this->registry->aliasExists('emp-onboarding'));
    }

    /**
     * Test getting aliases for workflow
     *

     * @return void
     */
    public function testGetAliasesForWorkflow(): void
    {
        $this->registry->register($this->createWorkflow('onboarding'));
        $this->registry->alias('onboarding', 'emp-onboarding');
        $this->registry->alias('onboarding', 'new-hire');

        $aliases = $this->registry->getAliasesFor('onboarding');

        $this->assertCount(2, $aliases);
        $this->assertContains('emp-onboarding', $aliases);
        $this->assertContains('new-hire', $aliases);
    }

    /**
     * Test getting experimental workflows
     *

     * @return void
     */
    public function testGetExperimentalWorkflows(): void
    {
        $this->registry->register($this->createWorkflow('onboarding', ['metadata' => ['experimental' => true]]));
        $this->registry->register($this->createWorkflow('approval'));

        $experimental = $this->registry->experimental();

        $this->assertCount(1, $experimental);
        $this->assertArrayHasKey('onboarding', $experimental);
    }

    /**
     * Test getting deprecated workflows
     *

     * @return void
     */
    public function testGetDeprecatedWorkflows(): void
    {
        $this->registry->register($this->createWorkflow('oldflow', ['metadata' => ['deprecated' => true]]));
        $this->registry->register($this->createWorkflow('newflow'));

        $deprecated = $this->registry->deprecated();

        $this->assertCount(1, $deprecated);
        $this->assertArrayHasKey('oldflow', $deprecated);
    }

    /**
     * Test finding workflows supporting entity type
     *

     * @return void
     */
    public function testSupportingEntity(): void
    {
        $this->registry->register($this->createWorkflow('onboarding', ['supportedEntityTypes' => ['Employee']]));
        $this->registry->register($this->createWorkflow('invoice', ['supportedEntityTypes' => ['Invoice']]));

        $employeeWorkflows = $this->registry->supportingEntity('Employee');

        $this->assertCount(1, $employeeWorkflows);
        $this->assertArrayHasKey('onboarding', $employeeWorkflows);
    }

    /**
     * Test sorting by priority
     *

     * @return void
     */
    public function testSortedByPriority(): void
    {
        $this->registry->register($this->createWorkflow('low', ['priority' => 1]));
        $this->registry->register($this->createWorkflow('high', ['priority' => 100]));
        $this->registry->register($this->createWorkflow('medium', ['priority' => 50]));

        $sorted = $this->registry->sortedByPriority();

        $ids = array_keys($sorted);
        $this->assertSame('high', $ids[0]);
        $this->assertSame('medium', $ids[1]);
        $this->assertSame('low', $ids[2]);
    }

    /**
     * Test clearing all workflows
     *

     * @return void
     */
    public function testClearAllWorkflows(): void
    {
        $this->registry->register($this->createWorkflow('onboarding'));
        $this->registry->alias('onboarding', 'emp-onboarding');

        $this->assertSame(1, $this->registry->count());

        $result = $this->registry->clear();

        $this->assertSame($this->registry, $result);
        $this->assertSame(0, $this->registry->count());
    }

    /**
     * Test empty registry
     *

     * @return void
     */
    public function testEmptyRegistry(): void
    {
        $this->assertSame(0, $this->registry->count());
        $this->assertCount(0, $this->registry->all());
        $this->assertCount(0, $this->registry->enabled());
    }

    /**
     * Test unregister removes aliases
     *

     * @return void
     */
    public function testUnregisterRemovesAliases(): void
    {
        $this->registry->register($this->createWorkflow('onboarding'));
        $this->registry->alias('onboarding', 'emp-onboarding');
        $this->registry->alias('onboarding', 'new-hire');

        $this->assertTrue($this->registry->aliasExists('emp-onboarding'));

        $this->registry->unregister('onboarding');

        $this->assertFalse($this->registry->aliasExists('emp-onboarding'));
        $this->assertFalse($this->registry->aliasExists('new-hire'));
    }

    /**
     * Test method chaining
     *

     * @return void
     */
    public function testMethodChaining(): void
    {
        $result = $this->registry
            ->register($this->createWorkflow('onboarding'))
            ->register($this->createWorkflow('approval'))
            ->alias('onboarding', 'emp-onboarding');

        $this->assertSame($this->registry, $result);
        $this->assertSame(2, $this->registry->count());
    }

    /**
     * Test extension hooks are called
     *

     * @return void
     */
    public function testExtensionHooksAreCalled(): void
    {
        $registry = new TestWorkflowRegistry();
        $workflow = $this->createWorkflow('onboarding');

        $registry->register($workflow);

        $this->assertTrue($registry->beforeRegisterCalled);
        $this->assertTrue($registry->afterRegisterCalled);

        $registry->unregister('onboarding');

        $this->assertTrue($registry->beforeUnregisterCalled);
        $this->assertTrue($registry->afterUnregisterCalled);
    }

    /**
     * Test workflow with steps
     *

     * @return void
     */
    public function testWorkflowWithSteps(): void
    {
        $step1 = new WorkflowStep('step-1', 'create-employee');
        $step2 = new WorkflowStep('step-2', 'send-welcome');

        $workflow = $this->createWorkflow('onboarding', ['steps' => [$step1, $step2]]);
        $this->registry->register($workflow);

        $found = $this->registry->findById('onboarding');

        $this->assertCount(2, $found->steps);
        $this->assertSame('step-1', $found->steps[0]->id);
    }
}

/**
 * TestWorkflowRegistry
 *
 * Test implementation that tracks hook calls.
 *

 * @package WaysNX\BusinessFramework\Tests\Registry
 */
class TestWorkflowRegistry extends WorkflowRegistry
{
    /**
     * Track if beforeRegister was called
     *

     * @var bool
     */
    public bool $beforeRegisterCalled = false;

    /**
     * Track if afterRegister was called
     *

     * @var bool
     */
    public bool $afterRegisterCalled = false;

    /**
     * Track if beforeUnregister was called
     *

     * @var bool
     */
    public bool $beforeUnregisterCalled = false;

    /**
     * Track if afterUnregister was called
     *

     * @var bool
     */
    public bool $afterUnregisterCalled = false;

    /**
     * Override beforeRegister to track calls
     *

     * @param WorkflowDefinition $definition The workflow definition
     *

     * @return void
     */
    protected function beforeRegister(WorkflowDefinition $definition): void
    {
        $this->beforeRegisterCalled = true;
    }

    /**
     * Override afterRegister to track calls
     *

     * @param WorkflowDefinition $definition The workflow definition
     *

     * @return void
     */
    protected function afterRegister(WorkflowDefinition $definition): void
    {
        $this->afterRegisterCalled = true;
    }

    /**
     * Override beforeUnregister to track calls
     *

     * @param WorkflowDefinition $definition The workflow definition
     *

     * @return void
     */
    protected function beforeUnregister(WorkflowDefinition $definition): void
    {
        $this->beforeUnregisterCalled = true;
    }

    /**
     * Override afterUnregister to track calls
     *

     * @param WorkflowDefinition $definition The workflow definition
     *

     * @return void
     */
    protected function afterUnregister(WorkflowDefinition $definition): void
    {
        $this->afterUnregisterCalled = true;
    }
}
