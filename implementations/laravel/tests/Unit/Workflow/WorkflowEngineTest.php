<?php

declare(strict_types=1);

namespace Tests\Unit\Workflow;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Exceptions\WorkflowExecutionException;
use WaysNX\BusinessFramework\Lifecycle\LifecycleManager;
use WaysNX\BusinessFramework\Registry\WorkflowDefinition;
use WaysNX\BusinessFramework\Registry\WorkflowRegistry;
use WaysNX\BusinessFramework\Registry\WorkflowStep;
use WaysNX\BusinessFramework\Workflow\WorkflowContext;
use WaysNX\BusinessFramework\Workflow\WorkflowEngine;

/**
 * WorkflowEngineTest
 *
 * Tests for WorkflowEngine functionality.
 *
 * @package Tests\Unit\Workflow
 */
class WorkflowEngineTest extends TestCase
{
    /**
     * @var WorkflowRegistry
     */
    private WorkflowRegistry $registry;

    /**
     * @var WorkflowEngine
     */
    private WorkflowEngine $engine;

    /**
     * @var LifecycleManager
     */
    private LifecycleManager $lifecycleManager;

    protected function setUp(): void
    {
        $this->registry = new WorkflowRegistry();
        $this->lifecycleManager = new LifecycleManager();
        $this->engine = new WorkflowEngine(
            $this->registry,
            $this->lifecycleManager
        );
    }

    /**
     * Test successful workflow execution
     */
    public function testSuccessfulWorkflowExecution(): void
    {
        $step = new WorkflowStep(
            id: 'step-1',
            businessFunctionId: 'create-customer',
            sequence: 1,
            description: 'Create customer',
            transitions: [],
            metadata: []
        );

        $workflow = new WorkflowDefinition(
            id: 'create-workflow',
            name: 'CreateWorkflow',
            displayName: 'Create Workflow',
            steps: [$step]
        );

        $this->registry->register($workflow);

        $engine = new class($this->registry, $this->lifecycleManager) extends WorkflowEngine {
            protected function executeBusinessFunction(string $functionId, $context): void
            {
                // Function succeeds
            }
        };

        $context = new WorkflowContext(
            workflowId: 'create-workflow',
            executionId: 'exec-1'
        );

        $result = $engine->execute('create-workflow', $context);

        $this->assertTrue($result->succeeded());
        $this->assertFalse($result->failed());
        $this->assertCount(1, $result->completedSteps);
        $this->assertEmpty($result->failedSteps);
    }

    /**
     * Test workflow execution with multiple steps
     */
    public function testWorkflowWithMultipleSteps(): void
    {
        $steps = [
            new WorkflowStep(
                id: 'step-1',
                businessFunctionId: 'func-1',
                sequence: 1,
                description: 'Step 1',
                transitions: [],
                metadata: []
            ),
            new WorkflowStep(
                id: 'step-2',
                businessFunctionId: 'func-2',
                sequence: 2,
                description: 'Step 2',
                transitions: [],
                metadata: []
            ),
            new WorkflowStep(
                id: 'step-3',
                businessFunctionId: 'func-3',
                sequence: 3,
                description: 'Step 3',
                transitions: [],
                metadata: []
            ),
        ];

        $workflow = new WorkflowDefinition(
            id: 'multi-step-workflow',
            name: 'MultiStepWorkflow',
            displayName: 'Multi-Step Workflow',
            steps: $steps
        );

        $this->registry->register($workflow);

        $executedFunctions = [];

        $engine = new class($this->registry, $this->lifecycleManager) extends WorkflowEngine {
            public $executedFunctions = [];

            protected function executeBusinessFunction(string $functionId, $context): void
            {
                $this->executedFunctions[] = $functionId;
            }
        };

        $context = new WorkflowContext(
            workflowId: 'multi-step-workflow',
            executionId: 'exec-1'
        );

        $result = $engine->execute('multi-step-workflow', $context);

        $this->assertTrue($result->succeeded());
        $this->assertCount(3, $result->completedSteps);
        $this->assertCount(3, $engine->executedFunctions);
        $this->assertEquals(['func-1', 'func-2', 'func-3'], $engine->executedFunctions);
    }

    /**
     * Test step execution failure
     */
    public function testStepExecutionFailure(): void
    {
        $steps = [
            new WorkflowStep(
                id: 'step-1',
                businessFunctionId: 'failing-func',
                sequence: 1,
                description: 'Failing step',
                transitions: [],
                metadata: []
            ),
            new WorkflowStep(
                id: 'step-2',
                businessFunctionId: 'func-2',
                sequence: 2,
                description: 'Step 2',
                transitions: [],
                metadata: []
            ),
        ];

        $workflow = new WorkflowDefinition(
            id: 'failure-workflow',
            name: 'FailureWorkflow',
            displayName: 'Failure Workflow',
            steps: $steps
        );

        $this->registry->register($workflow);

        $engine = new class($this->registry, $this->lifecycleManager) extends WorkflowEngine {
            protected function executeBusinessFunction(string $functionId, $context): void
            {
                if ($functionId === 'failing-func') {
                    throw new \RuntimeException('Function failed');
                }
            }
        };

        $context = new WorkflowContext(
            workflowId: 'failure-workflow',
            executionId: 'exec-1'
        );

        $result = $engine->execute('failure-workflow', $context);

        $this->assertFalse($result->succeeded());
        $this->assertTrue($result->failed());
        $this->assertCount(1, $result->failedSteps);
        $this->assertTrue(in_array('step-1', $result->failedSteps));
    }

    /**
     * Test context propagation to step execution
     */
    public function testContextPropagationToSteps(): void
    {
        $step = new WorkflowStep(
            id: 'step-1',
            businessFunctionId: 'test-func',
            sequence: 1,
            description: 'Test',
            transitions: [],
            metadata: []
        );

        $workflow = new WorkflowDefinition(
            id: 'context-workflow',
            name: 'ContextWorkflow',
            displayName: 'Context Workflow',
            steps: [$step]
        );

        $this->registry->register($workflow);

        $receivedContext = null;

        $engine = new class($this->registry, $this->lifecycleManager) extends WorkflowEngine {
            public $receivedContext = null;

            protected function executeBusinessFunction(string $functionId, $context): void
            {
                $this->receivedContext = $context;
            }
        };

        $context = new WorkflowContext(
            workflowId: 'context-workflow',
            executionId: 'exec-1',
            correlationId: 'req-123',
            moduleId: 'crm',
            businessFunctionId: 'update-customer',
            inputData: ['id' => 456],
            metadata: ['userId' => 789]
        );

        $engine->execute('context-workflow', $context);

        $this->assertNotNull($engine->receivedContext);
        $this->assertEquals('context-workflow', $engine->receivedContext->workflowId);
        $this->assertEquals('exec-1', $engine->receivedContext->executionId);
        $this->assertEquals('req-123', $engine->receivedContext->correlationId);
        $this->assertEquals('crm', $engine->receivedContext->moduleId);
        $this->assertEquals(['id' => 456], $engine->receivedContext->inputData);
        $this->assertEquals(789, $engine->receivedContext->getMetadataValue('userId'));
    }

    /**
     * Test lifecycle integration
     */
    public function testLifecycleIntegration(): void
    {
        $step = new WorkflowStep(
            id: 'step-1',
            businessFunctionId: 'test-func',
            sequence: 1,
            description: 'Test',
            transitions: [],
            metadata: []
        );

        $workflow = new WorkflowDefinition(
            id: 'lifecycle-workflow',
            name: 'LifecycleWorkflow',
            displayName: 'Lifecycle Workflow',
            steps: [$step]
        );

        $this->registry->register($workflow);

        $events = [];

        $this->lifecycleManager->register(
            new \WaysNX\BusinessFramework\Lifecycle\LifecycleHandler(
                id: 'before-handler',
                callable: function() use (&$events) {
                    $events[] = 'beforeWorkflow';
                },
                supportedEvents: ['beforeWorkflow']
            )
        );

        $this->lifecycleManager->register(
            new \WaysNX\BusinessFramework\Lifecycle\LifecycleHandler(
                id: 'after-handler',
                callable: function() use (&$events) {
                    $events[] = 'afterWorkflow';
                },
                supportedEvents: ['afterWorkflow']
            )
        );

        $this->lifecycleManager->register(
            new \WaysNX\BusinessFramework\Lifecycle\LifecycleHandler(
                id: 'completed-handler',
                callable: function() use (&$events) {
                    $events[] = 'workflowCompleted';
                },
                supportedEvents: ['workflowCompleted']
            )
        );

        $engine = new class($this->registry, $this->lifecycleManager) extends WorkflowEngine {
            protected function executeBusinessFunction(string $functionId, $context): void
            {
                // Success
            }
        };

        $context = new WorkflowContext(
            workflowId: 'lifecycle-workflow',
            executionId: 'exec-1'
        );

        $engine->execute('lifecycle-workflow', $context);

        $this->assertContains('beforeWorkflow', $events);
        $this->assertContains('afterWorkflow', $events);
        $this->assertContains('workflowCompleted', $events);
    }

    /**
     * Test workflow result generation
     */
    public function testWorkflowResultGeneration(): void
    {
        $steps = [
            new WorkflowStep(
                id: 'step-1',
                businessFunctionId: 'func-1',
                sequence: 1,
                description: 'Step 1',
                transitions: [],
                metadata: []
            ),
            new WorkflowStep(
                id: 'step-2',
                businessFunctionId: 'func-2',
                sequence: 2,
                description: 'Step 2',
                transitions: [],
                metadata: []
            ),
        ];

        $workflow = new WorkflowDefinition(
            id: 'result-workflow',
            name: 'ResultWorkflow',
            displayName: 'Result Workflow',
            steps: $steps
        );

        $this->registry->register($workflow);

        $engine = new class($this->registry, $this->lifecycleManager) extends WorkflowEngine {
            protected function executeBusinessFunction(string $functionId, $context): void
            {
                // Success
            }
        };

        $context = new WorkflowContext(
            workflowId: 'result-workflow',
            executionId: 'exec-1'
        );

        $result = $engine->execute('result-workflow', $context);

        $this->assertNotNull($result->executionId);
        $this->assertEquals('result-workflow', $result->workflowId);
        $this->assertEquals('completed', $result->status);
        $this->assertEquals(2, $result->totalSteps());
        $this->assertEquals(100.0, $result->completionPercentage());
        $this->assertGreaterThanOrEqual(0, $result->duration);
    }

    /**
     * Test extension points are called
     */
    public function testExtensionPointsAreCalled(): void
    {
        $step = new WorkflowStep(
            id: 'step-1',
            businessFunctionId: 'test-func',
            sequence: 1,
            description: 'Test',
            transitions: [],
            metadata: []
        );

        $workflow = new WorkflowDefinition(
            id: 'extension-workflow',
            name: 'ExtensionWorkflow',
            displayName: 'Extension Workflow',
            steps: [$step]
        );

        $this->registry->register($workflow);

        $engine = new class($this->registry, $this->lifecycleManager) extends WorkflowEngine {
            public $calls = [];

            protected function beforeExecute($workflow, $context): void
            {
                $this->calls[] = 'beforeExecute';
            }

            protected function beforeStep($workflow, $step, $context): void
            {
                $this->calls[] = 'beforeStep';
            }

            protected function afterStep($workflow, $step, $context): void
            {
                $this->calls[] = 'afterStep';
            }

            protected function beforeComplete($workflow, $context, $status, $completed, $failed): void
            {
                $this->calls[] = 'beforeComplete';
            }

            protected function afterComplete($workflow, $context, $result): void
            {
                $this->calls[] = 'afterComplete';
            }

            protected function executeBusinessFunction(string $functionId, $context): void
            {
                // Success
            }
        };

        $context = new WorkflowContext(
            workflowId: 'extension-workflow',
            executionId: 'exec-1'
        );

        $engine->execute('extension-workflow', $context);

        $this->assertContains('beforeExecute', $engine->calls);
        $this->assertContains('beforeStep', $engine->calls);
        $this->assertContains('afterStep', $engine->calls);
        $this->assertContains('beforeComplete', $engine->calls);
        $this->assertContains('afterComplete', $engine->calls);
    }

    /**
     * Test execution failure on workflow not found
     */
    public function testExecutionFailureOnNotFound(): void
    {
        $this->expectException(WorkflowExecutionException::class);
        $this->expectExceptionMessageMatches("/workflow.*not found/i");

        $context = new WorkflowContext(
            workflowId: 'non-existent',
            executionId: 'exec-1'
        );

        $this->engine->execute('non-existent-workflow', $context);
    }

    /**
     * Test workflow without lifecycle manager
     */
    public function testWorkflowWithoutLifecycleManager(): void
    {
        $step = new WorkflowStep(
            id: 'step-1',
            businessFunctionId: 'test-func',
            sequence: 1,
            description: 'Test',
            transitions: [],
            metadata: []
        );

        $workflow = new WorkflowDefinition(
            id: 'no-lifecycle-workflow',
            name: 'NoLifecycleWorkflow',
            displayName: 'No Lifecycle Workflow',
            steps: [$step]
        );

        $this->registry->register($workflow);

        $engine = new class($this->registry) extends WorkflowEngine {
            protected function executeBusinessFunction(string $functionId, $context): void
            {
                // Success
            }
        };

        $context = new WorkflowContext(
            workflowId: 'no-lifecycle-workflow',
            executionId: 'exec-1'
        );

        $result = $engine->execute('no-lifecycle-workflow', $context);

        $this->assertTrue($result->succeeded());
    }

    /**
     * Test lifecycle manager can be set
     */
    public function testLifecycleManagerCanBeSet(): void
    {
        $engine = new WorkflowEngine($this->registry);
        $this->assertNull($engine->getLifecycleManager());

        $result = $engine->setLifecycleManager($this->lifecycleManager);
        $this->assertSame($engine, $result);
        $this->assertSame($this->lifecycleManager, $engine->getLifecycleManager());
    }

    /**
     * Test empty workflow
     */
    public function testEmptyWorkflow(): void
    {
        $workflow = new WorkflowDefinition(
            id: 'empty-workflow',
            name: 'EmptyWorkflow',
            displayName: 'Empty Workflow',
            steps: []
        );

        $this->registry->register($workflow);

        $engine = new WorkflowEngine($this->registry, $this->lifecycleManager);

        $context = new WorkflowContext(
            workflowId: 'empty-workflow',
            executionId: 'exec-1'
        );

        $result = $engine->execute('empty-workflow', $context);

        $this->assertTrue($result->succeeded());
        $this->assertEmpty($result->completedSteps);
    }

    /**
     * Test result to array
     */
    public function testResultToArray(): void
    {
        $step = new WorkflowStep(
            id: 'step-1',
            businessFunctionId: 'test-func',
            sequence: 1,
            description: 'Test',
            transitions: [],
            metadata: []
        );

        $workflow = new WorkflowDefinition(
            id: 'array-workflow',
            name: 'ArrayWorkflow',
            displayName: 'Array Workflow',
            steps: [$step]
        );

        $this->registry->register($workflow);

        $engine = new class($this->registry, $this->lifecycleManager) extends WorkflowEngine {
            protected function executeBusinessFunction(string $functionId, $context): void
            {
                // Success
            }
        };

        $context = new WorkflowContext(
            workflowId: 'array-workflow',
            executionId: 'exec-1'
        );

        $result = $engine->execute('array-workflow', $context);
        $array = $result->toArray();

        $this->assertArrayHasKey('executionId', $array);
        $this->assertArrayHasKey('workflowId', $array);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('completedSteps', $array);
        $this->assertArrayHasKey('failedSteps', $array);
        $this->assertArrayHasKey('duration', $array);
        $this->assertArrayHasKey('summary', $array);
    }
}
