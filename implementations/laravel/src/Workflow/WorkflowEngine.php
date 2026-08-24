<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Workflow;

use Ramsey\Uuid\Uuid;
use WaysNX\BusinessFramework\Exceptions\WorkflowExecutionException;
use WaysNX\BusinessFramework\Exceptions\WorkflowStepException;
use WaysNX\BusinessFramework\Lifecycle\LifecycleContext;
use WaysNX\BusinessFramework\Lifecycle\LifecycleEvent;
use WaysNX\BusinessFramework\Lifecycle\LifecycleManager;
use WaysNX\BusinessFramework\Registry\WorkflowDefinition;
use WaysNX\BusinessFramework\Registry\WorkflowRegistry;
use WaysNX\BusinessFramework\Registry\WorkflowStep;

/**
 * WorkflowEngine
 *
 * Executes workflow definitions and coordinates business functions.
 *
 * Purpose:
 * Execute workflow definitions by coordinating business function execution.
 *
 * Responsibilities:
 * - Load workflow definitions from registry
 * - Create and manage execution context
 * - Execute workflow steps in sequence
 * - Coordinate business function execution
 * - Track execution progress
 * - Manage step transitions
 * - Generate execution results
 * - Publish lifecycle events
 * - Generate execution metadata
 *
 * Usage:
 * ```php
 * $engine = new WorkflowEngine(
 *     registry: $workflowRegistry,
 *     lifecycleManager: $lifecycleManager
 * );
 *
 * $context = new WorkflowContext(
 *     workflowId: 'employee-onboarding',
 *     executionId: 'exec-1',
 *     entity: $employee,
 *     inputData: $data
 * );
 *
 * $result = $engine->execute('employee-onboarding', $context);
 * ```
 *
 * Extension Points:
 * - Override beforeExecute() for pre-execution hooks
 * - Override afterExecute() for post-execution hooks
 * - Override beforeStep() for pre-step hooks
 * - Override afterStep() for post-step hooks
 * - Override executeBusinessFunction() to invoke functions
 * - Override determineNextStep() for custom transitions
 *
 * @package WaysNX\BusinessFramework\Workflow
 */
class WorkflowEngine
{
    /**
     * The workflow registry
     *

     * @var WorkflowRegistry
     */
    private WorkflowRegistry $registry;

    /**
     * The lifecycle manager
     *

     * @var LifecycleManager|null
     */
    private ?LifecycleManager $lifecycleManager;

    /**
     * Current execution state
     *

     * @var array
     */
    private array $executionState = [];

    /**
     * Initialize a new WorkflowEngine
     *

     * @param WorkflowRegistry $registry The workflow registry
     * @param LifecycleManager|null $lifecycleManager The lifecycle manager
     */
    public function __construct(
        WorkflowRegistry $registry,
        ?LifecycleManager $lifecycleManager = null
    ) {
        $this->registry = $registry;
        $this->lifecycleManager = $lifecycleManager;
    }

    /**
     * Execute a workflow by ID
     *

     * @param string $workflowId The workflow ID
     * @param WorkflowContext $context The workflow context
     *

     * @return WorkflowResult The workflow result
     *

     * @throws WorkflowExecutionException If execution fails
     */
    public function execute(string $workflowId, WorkflowContext $context): WorkflowResult
    {
        try {
            $workflow = $this->registry->findById($workflowId);
            return $this->executeWorkflow($workflow, $context);
        } catch (\Exception $e) {
            throw new WorkflowExecutionException(
                "Workflow '{$workflowId}' execution failed: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Execute a workflow definition
     *

     * @param WorkflowDefinition $workflow The workflow definition
     * @param WorkflowContext $context The workflow context
     *

     * @return WorkflowResult The workflow result
     *

     * @throws WorkflowExecutionException If execution fails
     */
    public function executeWorkflow(
        WorkflowDefinition $workflow,
        WorkflowContext $context
    ): WorkflowResult {
        $executionId = $context->executionId ?: (string) Uuid::uuid4();
        $startTime = hrtime(true);

        try {
            $this->publishEvent('beforeWorkflow', $workflow->id, $context);
            $this->beforeExecute($workflow, $context);

            // Initialize execution state
            $completedSteps = [];
            $failedSteps = [];
            $errors = [];
            $warnings = [];

            // Execute steps sequentially
            $currentStepId = $this->getFirstStep($workflow);

            while (!empty($currentStepId)) {
                try {
                    $step = $this->getStepById($workflow, $currentStepId);
                    if ($step === null) {
                        $errors[] = "Step '{$currentStepId}' not found";
                        break;
                    }

                    $this->beforeStep($workflow, $step, $context);

                    // Execute the step
                    $this->executeStep($step, $context);

                    $completedSteps[] = $currentStepId;

                    $this->afterStep($workflow, $step, $context);

                    // Determine next step
                    $currentStepId = $this->determineNextStep($workflow, $step, $completedSteps);
                } catch (\Throwable $e) {
                    $failedSteps[] = $currentStepId;
                    $errors[] = "Step '{$currentStepId}' failed: {$e->getMessage()}";
                    break;
                }
            }

            $endTime = hrtime(true);
            $duration = (int) (($endTime - $startTime) / 1_000_000);

            $status = empty($failedSteps) ? 'completed' : 'failed';

            $this->beforeComplete($workflow, $context, $status, $completedSteps, $failedSteps);

            $result = new WorkflowResult(
                executionId: $executionId,
                workflowId: $workflow->id,
                status: $status,
                completedSteps: $completedSteps,
                failedSteps: $failedSteps,
                warnings: $warnings,
                errors: $errors,
                duration: $duration,
                metadata: [
                    'workflowName' => $workflow->name,
                    'stepCount' => count($workflow->steps),
                    'stepsCompleted' => count($completedSteps),
                    'stepsFailed' => count($failedSteps),
                ]
            );

            $this->afterComplete($workflow, $context, $result);

            if ($result->succeeded()) {
                $this->publishEvent('workflowCompleted', $workflow->id, $context);
            } else {
                $this->publishEvent('workflowFailed', $workflow->id, $context);
            }

            $this->publishEvent('afterWorkflow', $workflow->id, $context);

            return $result;
        } catch (\Exception $e) {
            throw new WorkflowExecutionException(
                "Workflow execution failed: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Execute a single workflow step
     *

     * @param WorkflowStep $step The step to execute
     * @param WorkflowContext $context The workflow context
     *

     * @return void
     *

     * @throws WorkflowStepException If execution fails
     */
    protected function executeStep(WorkflowStep $step, WorkflowContext $context): void
    {
        try {
            $this->publishEvent('beforeStep', $step->id, $context);

            // Execute the business function associated with this step
            $this->executeBusinessFunction($step->businessFunctionId, $context);

            $this->publishEvent('stepCompleted', $step->id, $context);
        } catch (\Throwable $e) {
            $this->publishEvent('stepFailed', $step->id, $context);
            throw new WorkflowStepException(
                "Step '{$step->id}' execution failed: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Execute a business function (extension point)
     *

     * @param string $functionId The business function ID
     * @param WorkflowContext $context The workflow context
     *

     * @return void
     */
    protected function executeBusinessFunction(string $functionId, WorkflowContext $context): void
    {
        // Extension point: application implements business function execution
        // The framework does not execute business logic
    }

    /**
     * Get the first step in the workflow
     *

     * @param WorkflowDefinition $workflow The workflow definition
     *

     * @return string|null The first step ID or null
     */
    private function getFirstStep(WorkflowDefinition $workflow): ?string
    {
        if (empty($workflow->steps)) {
            return null;
        }

        // Steps are typically ordered by sequence
        $steps = $workflow->steps;
        usort(
            $steps,
            fn($a, $b) => $a->sequence <=> $b->sequence
        );

        return $steps[0]->id ?? null;
    }

    /**
     * Get a step by ID
     *

     * @param WorkflowDefinition $workflow The workflow definition
     * @param string $stepId The step ID
     *

     * @return WorkflowStep|null The step or null
     */
    private function getStepById(WorkflowDefinition $workflow, string $stepId): ?WorkflowStep
    {
        foreach ($workflow->steps as $step) {
            if ($step->id === $stepId) {
                return $step;
            }
        }

        return null;
    }

    /**
     * Determine the next step (extension point)
     *

     * @param WorkflowDefinition $workflow The workflow definition
     * @param WorkflowStep $currentStep The current step
     * @param array<string> $completedSteps Completed step IDs
     *

     * @return string|null The next step ID or null
     */
    protected function determineNextStep(
        WorkflowDefinition $workflow,
        WorkflowStep $currentStep,
        array $completedSteps
    ): ?string {
        // Look for next step based on sequence
        foreach ($workflow->steps as $step) {
            if ($step->sequence > $currentStep->sequence) {
                return $step->id;
            }
        }

        return null;
    }

    /**
     * Publish a lifecycle event
     *

     * @param string $eventName The event name
     * @param string $workflowId The workflow ID
     * @param WorkflowContext $context The workflow context
     *

     * @return void
     */
    private function publishEvent(string $eventName, string $workflowId, WorkflowContext $context): void
    {
        if ($this->lifecycleManager === null) {
            return;
        }

        $event = new LifecycleEvent($eventName, [
            'workflowId' => $workflowId,
        ]);

        $lifecycleContext = new LifecycleContext(
            entityId: $context->executionId,
            entityType: 'Workflow',
            moduleId: $context->moduleId,
            businessFunctionId: $context->businessFunctionId,
            correlationId: $context->correlationId,
            metadata: $context->metadata
        );

        $this->lifecycleManager->dispatch($event, $lifecycleContext);
    }

    /**
     * Extension point: before workflow execution
     *

     * @param WorkflowDefinition $workflow The workflow definition
     * @param WorkflowContext $context The workflow context
     *

     * @return void
     */
    protected function beforeExecute(WorkflowDefinition $workflow, WorkflowContext $context): void
    {
        // Override in subclasses
    }

    /**
     * Extension point: before step execution
     *

     * @param WorkflowDefinition $workflow The workflow definition
     * @param WorkflowStep $step The step being executed
     * @param WorkflowContext $context The workflow context
     *

     * @return void
     */
    protected function beforeStep(
        WorkflowDefinition $workflow,
        WorkflowStep $step,
        WorkflowContext $context
    ): void {
        // Override in subclasses
    }

    /**
     * Extension point: after step execution
     *

     * @param WorkflowDefinition $workflow The workflow definition
     * @param WorkflowStep $step The step that was executed
     * @param WorkflowContext $context The workflow context
     *

     * @return void
     */
    protected function afterStep(
        WorkflowDefinition $workflow,
        WorkflowStep $step,
        WorkflowContext $context
    ): void {
        // Override in subclasses
    }

    /**
     * Extension point: before result completion
     *

     * @param WorkflowDefinition $workflow The workflow definition
     * @param WorkflowContext $context The workflow context
     * @param string $status The workflow status
     * @param array<string> $completedSteps Completed step IDs
     * @param array<string> $failedSteps Failed step IDs
     *

     * @return void
     */
    protected function beforeComplete(
        WorkflowDefinition $workflow,
        WorkflowContext $context,
        string $status,
        array $completedSteps,
        array $failedSteps
    ): void {
        // Override in subclasses
    }

    /**
     * Extension point: after result completion
     *

     * @param WorkflowDefinition $workflow The workflow definition
     * @param WorkflowContext $context The workflow context
     * @param WorkflowResult $result The workflow result
     *

     * @return void
     */
    protected function afterComplete(
        WorkflowDefinition $workflow,
        WorkflowContext $context,
        WorkflowResult $result
    ): void {
        // Override in subclasses
    }

    /**
     * Set lifecycle manager
     *

     * @param LifecycleManager $lifecycleManager The lifecycle manager
     *

     * @return self Returns self for method chaining
     */
    public function setLifecycleManager(LifecycleManager $lifecycleManager): self
    {
        $this->lifecycleManager = $lifecycleManager;
        return $this;
    }

    /**
     * Get lifecycle manager
     *

     * @return LifecycleManager|null The lifecycle manager or null
     */
    public function getLifecycleManager(): ?LifecycleManager
    {
        return $this->lifecycleManager;
    }

    /**
     * Get workflow registry
     *

     * @return WorkflowRegistry The workflow registry
     */
    public function getRegistry(): WorkflowRegistry
    {
        return $this->registry;
    }
}
