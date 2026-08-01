<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Validation;

use Ramsey\Uuid\Uuid;
use WaysNX\BusinessFramework\Exceptions\ValidationExecutionException;
use WaysNX\BusinessFramework\Exceptions\ValidationRuleException;
use WaysNX\BusinessFramework\Lifecycle\LifecycleContext;
use WaysNX\BusinessFramework\Lifecycle\LifecycleEvent;
use WaysNX\BusinessFramework\Lifecycle\LifecycleManager;
use WaysNX\BusinessFramework\Registry\ValidationDefinition;
use WaysNX\BusinessFramework\Registry\ValidationRegistry;
use WaysNX\BusinessFramework\Registry\ValidationRuleDefinition;

/**
 * ValidationFramework
 *
 * Executes validation definitions and produces structured results.
 *
 * Purpose:
 * Execute validation definitions against entities/data and generate results.
 *
 * Responsibilities:
 * - Load validation definitions from registry
 * - Execute validation rule sets
 * - Execute individual validation rules
 * - Generate validation issues
 * - Aggregate validation results
 * - Support stop-on-error option
 * - Support continue-on-error option
 * - Integrate with LifecycleManager
 * - Maintain execution context
 * - Provide extension points
 *
 * Usage:
 * ```php
 * $framework = new ValidationFramework(
 *     registry: $validationRegistry,
 *     lifecycleManager: $lifecycleManager
 * );
 *
 * $context = new ValidationContext(
 *     entity: $customer,
 *     inputData: $customerData,
 *     options: ['stopOnError' => false]
 * );
 *
 * $result = $framework->execute('customer-validation', $context);
 *
 * if (!$result->passed) {
 *     foreach ($result->errors() as $error) {
 *         echo $error->message;
 *     }
 * }
 * ```
 *
 * Extension Points:
 * - Override beforeExecute() to customize pre-execution
 * - Override afterExecute() to customize post-execution
 * - Override beforeRule() to customize pre-rule execution
 * - Override afterRule() to customize post-rule execution
 * - Override beforeComplete() to customize result finalization
 * - Override afterComplete() to customize post-completion
 *
 * @package WaysNX\BusinessFramework\Validation
 */
class ValidationFramework
{
    /**
     * The validation registry
     *

     * @var ValidationRegistry
     */
    private ValidationRegistry $registry;

    /**
     * The lifecycle manager
     *

     * @var LifecycleManager|null
     */
    private ?LifecycleManager $lifecycleManager;

    /**
     * Initialize a new ValidationFramework
     *

     * @param ValidationRegistry $registry The validation registry
     * @param LifecycleManager|null $lifecycleManager The lifecycle manager
     */
    public function __construct(
        ValidationRegistry $registry,
        ?LifecycleManager $lifecycleManager = null
    ) {
        $this->registry = $registry;
        $this->lifecycleManager = $lifecycleManager;
    }

    /**
     * Execute a validation by ID
     *

     * @param string $validationId The validation ID
     * @param ValidationContext $context The validation context
     *

     * @return ValidationResult The validation result
     *

     * @throws ValidationExecutionException If validation execution fails
     */
    public function execute(string $validationId, ValidationContext $context): ValidationResult
    {
        try {
            // Load validation definition
            $validation = $this->registry->findById($validationId);

            // Execute validation
            return $this->executeValidation($validation, $context);
        } catch (\Exception $e) {
            throw new ValidationExecutionException(
                "Validation '{$validationId}' execution failed: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Execute a validation definition
     *

     * @param ValidationDefinition $validation The validation definition
     * @param ValidationContext $context The validation context
     *

     * @return ValidationResult The validation result
     *

     * @throws ValidationExecutionException If execution fails
     */
    public function executeValidation(
        ValidationDefinition $validation,
        ValidationContext $context
    ): ValidationResult {
        // Generate execution ID if needed
        $executionId = $context->executionId ?: (string) Uuid::uuid4();

        // Publish beforeValidation event
        $this->publishEvent('beforeValidation', $validation->id, $context);

        // Call extension point
        $this->beforeExecute($validation, $context);

        // Start tracking execution
        $startTime = hrtime(true);
        $issues = [];
        $rulesExecuted = 0;

        try {
            // Execute rules in order
            foreach ($validation->rules as $rule) {
                $this->beforeRule($validation, $rule, $context);

                try {
                    // Execute the rule
                    if ($this->evaluateRule($rule, $context)) {
                        // Rule passed - continue
                    } else {
                        // Rule failed - create issue
                        $issue = $this->createIssue($rule, $context);
                        $issues[] = $issue;

                        // Stop on error if configured
                        if ($context->stopOnError() && $rule->isError()) {
                            break;
                        }
                    }
                } catch (\Throwable $e) {
                    throw new ValidationRuleException(
                        "Rule '{$rule->id}' execution failed: {$e->getMessage()}",
                        previous: $e
                    );
                }

                $this->afterRule($validation, $rule, $context);
                $rulesExecuted++;
            }
        } finally {
            $endTime = hrtime(true);
        }

        // Calculate execution time in milliseconds
        $executionTime = (int) (($endTime - $startTime) / 1_000_000);

        // Determine pass/fail
        $passed = empty(array_filter(
            $issues,
            fn(ValidationIssue $issue) => $issue->isError()
        ));

        // Call extension point before completion
        $this->beforeComplete($validation, $context, $passed, $issues);

        // Create result
        $result = new ValidationResult(
            validationId: $validation->id,
            executionId: $executionId,
            passed: $passed,
            issues: $issues,
            executionTime: $executionTime,
            metadata: [
                'validationName' => $validation->name,
                'rulesExecuted' => $rulesExecuted,
                'ruleCount' => count($validation->rules),
            ]
        );

        // Call extension point after completion
        $this->afterComplete($validation, $context, $result);

        // Publish completion events
        if ($result->passed) {
            $this->publishEvent('validationCompleted', $validation->id, $context);
        } else {
            $this->publishEvent('validationFailed', $validation->id, $context);
        }

        // Publish afterValidation event
        $this->publishEvent('afterValidation', $validation->id, $context);

        return $result;
    }

    /**
     * Evaluate a single validation rule
     *

     * @param ValidationRuleDefinition $rule The rule to evaluate
     * @param ValidationContext $context The validation context
     *

     * @return bool True if rule passed
     */
    protected function evaluateRule(ValidationRuleDefinition $rule, ValidationContext $context): bool
    {
        // Rule evaluation is delegated to custom implementations
        // This is the extension point for rule evaluation
        // The framework does not contain business logic

        // Default: assume all rules pass (application must override this method)
        return true;
    }

    /**
     * Create a validation issue from a failed rule
     *

     * @param ValidationRuleDefinition $rule The failed rule
     * @param ValidationContext $context The validation context
     *

     * @return ValidationIssue The created issue
     */
    protected function createIssue(
        ValidationRuleDefinition $rule,
        ValidationContext $context
    ): ValidationIssue {
        return new ValidationIssue(
            id: (string) Uuid::uuid4(),
            ruleId: $rule->id,
            severity: $rule->severity,
            message: $rule->message,
            target: $rule->target,
            errorCode: $rule->errorCode,
            category: $rule->ruleType,
            metadata: [
                'ruleName' => $rule->name,
                'ruleType' => $rule->ruleType,
            ]
        );
    }

    /**
     * Publish a lifecycle event
     *

     * @param string $eventName The event name
     * @param string $validationId The validation ID
     * @param ValidationContext $context The validation context
     *

     * @return void
     */
    private function publishEvent(string $eventName, string $validationId, ValidationContext $context): void
    {
        if ($this->lifecycleManager === null) {
            return;
        }

        $event = new LifecycleEvent($eventName, [
            'validationId' => $validationId,
        ]);

        $lifecycleContext = new LifecycleContext(
            entityId: $context->executionId,
            entityType: 'Validation',
            moduleId: $context->moduleId,
            businessFunctionId: $context->businessFunctionId,
            workflowId: $context->workflowId,
            correlationId: $context->correlationId,
            metadata: $context->metadata
        );

        $this->lifecycleManager->dispatch($event, $lifecycleContext);
    }

    /**
     * Extension point: before validation execution
     *

     * @param ValidationDefinition $validation The validation definition
     * @param ValidationContext $context The validation context
     *

     * @return void
     */
    protected function beforeExecute(ValidationDefinition $validation, ValidationContext $context): void
    {
        // Override in subclasses for custom behavior
    }

    /**
     * Extension point: before rule execution
     *

     * @param ValidationDefinition $validation The validation definition
     * @param ValidationRuleDefinition $rule The rule being executed
     * @param ValidationContext $context The validation context
     *

     * @return void
     */
    protected function beforeRule(
        ValidationDefinition $validation,
        ValidationRuleDefinition $rule,
        ValidationContext $context
    ): void {
        // Override in subclasses for custom behavior
    }

    /**
     * Extension point: after rule execution
     *

     * @param ValidationDefinition $validation The validation definition
     * @param ValidationRuleDefinition $rule The rule that was executed
     * @param ValidationContext $context The validation context
     *

     * @return void
     */
    protected function afterRule(
        ValidationDefinition $validation,
        ValidationRuleDefinition $rule,
        ValidationContext $context
    ): void {
        // Override in subclasses for custom behavior
    }

    /**
     * Extension point: before result completion
     *

     * @param ValidationDefinition $validation The validation definition
     * @param ValidationContext $context The validation context
     * @param bool $passed Whether validation passed
     * @param array<ValidationIssue> $issues Validation issues
     *

     * @return void
     */
    protected function beforeComplete(
        ValidationDefinition $validation,
        ValidationContext $context,
        bool $passed,
        array $issues
    ): void {
        // Override in subclasses for custom behavior
    }

    /**
     * Extension point: after result completion
     *

     * @param ValidationDefinition $validation The validation definition
     * @param ValidationContext $context The validation context
     * @param ValidationResult $result The validation result
     *

     * @return void
     */
    protected function afterComplete(
        ValidationDefinition $validation,
        ValidationContext $context,
        ValidationResult $result
    ): void {
        // Override in subclasses for custom behavior
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
     * Get validation registry
     *

     * @return ValidationRegistry The validation registry
     */
    public function getRegistry(): ValidationRegistry
    {
        return $this->registry;
    }
}
