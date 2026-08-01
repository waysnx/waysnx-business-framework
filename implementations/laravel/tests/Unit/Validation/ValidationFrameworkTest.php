<?php

declare(strict_types=1);

namespace Tests\Unit\Validation;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Exceptions\ValidationExecutionException;
use WaysNX\BusinessFramework\Lifecycle\LifecycleManager;
use WaysNX\BusinessFramework\Registry\ValidationDefinition;
use WaysNX\BusinessFramework\Registry\ValidationRegistry;
use WaysNX\BusinessFramework\Registry\ValidationRuleDefinition;
use WaysNX\BusinessFramework\Validation\ValidationContext;
use WaysNX\BusinessFramework\Validation\ValidationFramework;
use WaysNX\BusinessFramework\Validation\ValidationIssue;
use WaysNX\BusinessFramework\Validation\ValidationResult;

/**
 * ValidationFrameworkTest
 *
 * Tests for ValidationFramework functionality.
 *
 * @package Tests\Unit\Validation
 */
class ValidationFrameworkTest extends TestCase
{
    /**
     * @var ValidationRegistry
     */
    private ValidationRegistry $registry;

    /**
     * @var ValidationFramework
     */
    private ValidationFramework $framework;

    /**
     * @var LifecycleManager
     */
    private LifecycleManager $lifecycleManager;

    protected function setUp(): void
    {
        $this->registry = new ValidationRegistry();
        $this->lifecycleManager = new LifecycleManager();
        $this->framework = new ValidationFramework(
            $this->registry,
            $this->lifecycleManager
        );
    }

    /**
     * Test successful validation
     */
    public function testSuccessfulValidation(): void
    {
        // Create a rule that passes
        $rule = new ValidationRuleDefinition(
            id: 'test-rule',
            name: 'TestRule',
            ruleType: 'required',
            target: 'email',
            message: 'Email is required',
            errorCode: 'VAL_001',
            severity: 'error'
        );

        // Create validation definition
        $validation = new ValidationDefinition(
            id: 'test-validation',
            name: 'TestValidation',
            displayName: 'Test Validation',
            rules: [$rule]
        );

        // Register validation
        $this->registry->register($validation);

        // Create context
        $context = new ValidationContext(
            entity: ['email' => 'test@example.com'],
            inputData: ['email' => 'test@example.com']
        );

        // Create a framework that passes all rules
        $framework = new class($this->registry, $this->lifecycleManager) extends ValidationFramework {
            protected function evaluateRule($rule, $context): bool
            {
                return true; // All rules pass
            }
        };

        // Execute validation
        $result = $framework->execute('test-validation', $context);

        // Assert success
        $this->assertTrue($result->passed);
        $this->assertFalse($result->failed());
        $this->assertEmpty($result->errors());
        $this->assertEquals('Validation passed', $result->summary());
    }

    /**
     * Test failed validation with errors
     */
    public function testFailedValidationWithErrors(): void
    {
        // Create a rule
        $rule = new ValidationRuleDefinition(
            id: 'email-required',
            name: 'EmailRequired',
            ruleType: 'required',
            target: 'email',
            message: 'Email is required',
            errorCode: 'VAL_EMAIL_001',
            severity: 'error'
        );

        // Create validation
        $validation = new ValidationDefinition(
            id: 'customer-validation',
            name: 'CustomerValidation',
            displayName: 'Customer Validation',
            rules: [$rule]
        );

        $this->registry->register($validation);

        $context = new ValidationContext();

        // Create framework that fails rules
        $framework = new class($this->registry, $this->lifecycleManager) extends ValidationFramework {
            protected function evaluateRule($rule, $context): bool
            {
                return false; // All rules fail
            }
        };

        $result = $framework->execute('customer-validation', $context);

        $this->assertFalse($result->passed);
        $this->assertTrue($result->failed());
        $this->assertCount(1, $result->errors());
        $this->assertEquals(1, $result->errorCount());
    }

    /**
     * Test multiple rule execution
     */
    public function testMultipleRuleExecution(): void
    {
        $rules = [
            new ValidationRuleDefinition(
                id: 'rule-1',
                name: 'Rule1',
                ruleType: 'required',
                target: 'name',
                message: 'Name is required',
                errorCode: 'VAL_001',
                severity: 'error'
            ),
            new ValidationRuleDefinition(
                id: 'rule-2',
                name: 'Rule2',
                ruleType: 'format',
                target: 'email',
                message: 'Email format invalid',
                errorCode: 'VAL_002',
                severity: 'error'
            ),
            new ValidationRuleDefinition(
                id: 'rule-3',
                name: 'Rule3',
                ruleType: 'range',
                target: 'age',
                message: 'Age must be 18 or older',
                errorCode: 'VAL_003',
                severity: 'warning'
            ),
        ];

        $validation = new ValidationDefinition(
            id: 'multi-rule-validation',
            name: 'MultiRuleValidation',
            displayName: 'Multi-Rule Validation',
            rules: $rules
        );

        $this->registry->register($validation);

        $context = new ValidationContext();

        // Track rule execution
        $executedRules = [];

        $framework = new class($this->registry, $this->lifecycleManager) extends ValidationFramework {
            public function __construct($registry, $lm, &$executed)
            {
                parent::__construct($registry, $lm);
                $this->executed = &$executed;
            }

            protected function evaluateRule($rule, $context): bool
            {
                $this->executed[] = $rule->id;
                return false; // All fail
            }

            private $executed;
        };

        $framework = new class($this->registry, $this->lifecycleManager) extends ValidationFramework {
            public $executedRules = [];

            protected function evaluateRule($rule, $context): bool
            {
                $this->executedRules[] = $rule->id;
                return false;
            }
        };

        $result = $framework->execute('multi-rule-validation', $context);

        // All rules should be executed
        $this->assertCount(3, $result->issues);
        $this->assertEquals(3, $result->issueCount());
    }

    /**
     * Test stop-on-error option
     */
    public function testStopOnErrorOption(): void
    {
        $rules = [
            new ValidationRuleDefinition(
                id: 'rule-1',
                name: 'Rule1',
                ruleType: 'required',
                target: 'field1',
                message: 'Field 1 required',
                errorCode: 'VAL_001',
                severity: 'error'
            ),
            new ValidationRuleDefinition(
                id: 'rule-2',
                name: 'Rule2',
                ruleType: 'required',
                target: 'field2',
                message: 'Field 2 required',
                errorCode: 'VAL_002',
                severity: 'error'
            ),
            new ValidationRuleDefinition(
                id: 'rule-3',
                name: 'Rule3',
                ruleType: 'required',
                target: 'field3',
                message: 'Field 3 required',
                errorCode: 'VAL_003',
                severity: 'error'
            ),
        ];

        $validation = new ValidationDefinition(
            id: 'stop-on-error-validation',
            name: 'StopOnErrorValidation',
            displayName: 'Stop On Error Validation',
            rules: $rules
        );

        $this->registry->register($validation);

        $context = new ValidationContext(
            options: ['stopOnError' => true]
        );

        $framework = new class($this->registry, $this->lifecycleManager) extends ValidationFramework {
            protected function evaluateRule($rule, $context): bool
            {
                return false; // All fail
            }
        };

        $result = $framework->execute('stop-on-error-validation', $context);

        // Should stop after first error rule
        $this->assertCount(1, $result->errors());
    }

    /**
     * Test continue-on-error option
     */
    public function testContinueOnErrorOption(): void
    {
        $rules = [
            new ValidationRuleDefinition(
                id: 'rule-1',
                name: 'Rule1',
                ruleType: 'required',
                target: 'field1',
                message: 'Field 1 required',
                errorCode: 'VAL_001',
                severity: 'error'
            ),
            new ValidationRuleDefinition(
                id: 'rule-2',
                name: 'Rule2',
                ruleType: 'required',
                target: 'field2',
                message: 'Field 2 required',
                errorCode: 'VAL_002',
                severity: 'error'
            ),
        ];

        $validation = new ValidationDefinition(
            id: 'continue-validation',
            name: 'ContinueValidation',
            displayName: 'Continue Validation',
            rules: $rules
        );

        $this->registry->register($validation);

        $context = new ValidationContext(
            options: ['stopOnError' => false]
        );

        $framework = new class($this->registry, $this->lifecycleManager) extends ValidationFramework {
            protected function evaluateRule($rule, $context): bool
            {
                return false; // All fail
            }
        };

        $result = $framework->execute('continue-validation', $context);

        // Should continue and collect all errors
        $this->assertCount(2, $result->errors());
    }

    /**
     * Test context propagation
     */
    public function testContextPropagation(): void
    {
        $rule = new ValidationRuleDefinition(
            id: 'test-rule',
            name: 'TestRule',
            ruleType: 'required',
            target: 'email',
            message: 'Email required',
            errorCode: 'VAL_001'
        );

        $validation = new ValidationDefinition(
            id: 'context-validation',
            name: 'ContextValidation',
            displayName: 'Context Validation',
            rules: [$rule]
        );

        $this->registry->register($validation);

        $receivedContext = null;

        $framework = new class($this->registry, $this->lifecycleManager) extends ValidationFramework {
            public $receivedContext = null;

            protected function evaluateRule($rule, $context): bool
            {
                $this->receivedContext = $context;
                return false;
            }
        };

        $context = new ValidationContext(
            entity: ['id' => 123],
            moduleId: 'crm',
            businessFunctionId: 'validate-customer',
            correlationId: 'req-456',
            inputData: ['email' => 'test@example.com'],
            metadata: ['userId' => 789]
        );

        $framework->execute('context-validation', $context);

        // Verify context was passed
        $this->assertNotNull($framework->receivedContext);
        $this->assertEquals('crm', $framework->receivedContext->moduleId);
        $this->assertEquals('validate-customer', $framework->receivedContext->businessFunctionId);
        $this->assertEquals('req-456', $framework->receivedContext->correlationId);
        $this->assertEquals(789, $framework->receivedContext->getMetadataValue('userId'));
    }

    /**
     * Test lifecycle integration
     */
    public function testLifecycleIntegration(): void
    {
        $rule = new ValidationRuleDefinition(
            id: 'test-rule',
            name: 'TestRule',
            ruleType: 'required',
            target: 'email',
            message: 'Email required',
            errorCode: 'VAL_001'
        );

        $validation = new ValidationDefinition(
            id: 'lifecycle-validation',
            name: 'LifecycleValidation',
            displayName: 'Lifecycle Validation',
            rules: [$rule]
        );

        $this->registry->register($validation);

        $events = [];

        // Register handlers
        $this->lifecycleManager->register(
            new \WaysNX\BusinessFramework\Lifecycle\LifecycleHandler(
                id: 'before-handler',
                callable: function() use (&$events) {
                    $events[] = 'beforeValidation';
                },
                supportedEvents: ['beforeValidation']
            )
        );

        $this->lifecycleManager->register(
            new \WaysNX\BusinessFramework\Lifecycle\LifecycleHandler(
                id: 'after-handler',
                callable: function() use (&$events) {
                    $events[] = 'afterValidation';
                },
                supportedEvents: ['afterValidation']
            )
        );

        $this->lifecycleManager->register(
            new \WaysNX\BusinessFramework\Lifecycle\LifecycleHandler(
                id: 'completed-handler',
                callable: function() use (&$events) {
                    $events[] = 'validationCompleted';
                },
                supportedEvents: ['validationCompleted']
            )
        );

        $framework = new class($this->registry, $this->lifecycleManager) extends ValidationFramework {
            protected function evaluateRule($rule, $context): bool
            {
                return true; // Pass
            }
        };

        $context = new ValidationContext();
        $result = $framework->execute('lifecycle-validation', $context);

        // Verify events were fired
        $this->assertContains('beforeValidation', $events);
        $this->assertContains('afterValidation', $events);
        $this->assertContains('validationCompleted', $events);
    }

    /**
     * Test result aggregation
     */
    public function testResultAggregation(): void
    {
        $rules = [
            new ValidationRuleDefinition(
                id: 'error-rule',
                name: 'ErrorRule',
                ruleType: 'required',
                target: 'field1',
                message: 'Field 1 required',
                errorCode: 'VAL_001',
                severity: 'error'
            ),
            new ValidationRuleDefinition(
                id: 'warning-rule',
                name: 'WarningRule',
                ruleType: 'format',
                target: 'field2',
                message: 'Field 2 format invalid',
                errorCode: 'VAL_002',
                severity: 'warning'
            ),
            new ValidationRuleDefinition(
                id: 'info-rule',
                name: 'InfoRule',
                ruleType: 'info',
                target: 'field3',
                message: 'Field 3 info',
                errorCode: 'VAL_003',
                severity: 'info'
            ),
        ];

        $validation = new ValidationDefinition(
            id: 'aggregation-validation',
            name: 'AggregationValidation',
            displayName: 'Aggregation Validation',
            rules: $rules
        );

        $this->registry->register($validation);

        $framework = new class($this->registry, $this->lifecycleManager) extends ValidationFramework {
            protected function evaluateRule($rule, $context): bool
            {
                return false; // All fail
            }
        };

        $context = new ValidationContext();
        $result = $framework->execute('aggregation-validation', $context);

        // Verify aggregation
        $this->assertFalse($result->passed);
        $this->assertTrue($result->failed());
        $this->assertEquals(1, $result->errorCount());
        $this->assertEquals(1, $result->warningCount());
        $this->assertEquals(1, $result->infoCount());
        $this->assertEquals(3, $result->issueCount());
        $this->assertTrue($result->hasErrors());
        $this->assertTrue($result->hasWarnings());
        $this->assertTrue($result->hasIssues());
    }

    /**
     * Test issue generation
     */
    public function testIssueGeneration(): void
    {
        $rule = new ValidationRuleDefinition(
            id: 'email-format',
            name: 'EmailFormat',
            ruleType: 'format',
            target: 'email',
            message: 'Invalid email format',
            errorCode: 'VAL_EMAIL_001',
            severity: 'error'
        );

        $validation = new ValidationDefinition(
            id: 'email-validation',
            name: 'EmailValidation',
            displayName: 'Email Validation',
            rules: [$rule]
        );

        $this->registry->register($validation);

        $framework = new class($this->registry, $this->lifecycleManager) extends ValidationFramework {
            protected function evaluateRule($rule, $context): bool
            {
                return false;
            }
        };

        $context = new ValidationContext();
        $result = $framework->execute('email-validation', $context);

        $this->assertCount(1, $result->issues);

        $issue = $result->issues[0];
        $this->assertEquals('email-format', $issue->ruleId);
        $this->assertEquals('error', $issue->severity);
        $this->assertEquals('Invalid email format', $issue->message);
        $this->assertEquals('email', $issue->target);
        $this->assertEquals('VAL_EMAIL_001', $issue->errorCode);
        $this->assertEquals('format', $issue->category);
    }

    /**
     * Test exception on validation not found
     */
    public function testExecutionFailureOnNotFound(): void
    {
        $this->expectException(ValidationExecutionException::class);
        $this->expectExceptionMessageMatches("/validation.*not found/i");

        $context = new ValidationContext();
        $this->framework->execute('non-existent-validation', $context);
    }

    /**
     * Test extension points are called
     */
    public function testExtensionPointsCalled(): void
    {
        $rule = new ValidationRuleDefinition(
            id: 'test-rule',
            name: 'TestRule',
            ruleType: 'required',
            target: 'email',
            message: 'Email required',
            errorCode: 'VAL_001'
        );

        $validation = new ValidationDefinition(
            id: 'extension-validation',
            name: 'ExtensionValidation',
            displayName: 'Extension Validation',
            rules: [$rule]
        );

        $this->registry->register($validation);

        $calls = [];

        $framework = new class($this->registry, $this->lifecycleManager) extends ValidationFramework {
            public $calls = [];

            protected function beforeExecute($validation, $context): void
            {
                $this->calls[] = 'beforeExecute';
            }

            protected function beforeRule($validation, $rule, $context): void
            {
                $this->calls[] = 'beforeRule';
            }

            protected function afterRule($validation, $rule, $context): void
            {
                $this->calls[] = 'afterRule';
            }

            protected function beforeComplete($validation, $context, $passed, $issues): void
            {
                $this->calls[] = 'beforeComplete';
            }

            protected function afterComplete($validation, $context, $result): void
            {
                $this->calls[] = 'afterComplete';
            }

            protected function evaluateRule($rule, $context): bool
            {
                return true;
            }
        };

        $context = new ValidationContext();
        $framework->execute('extension-validation', $context);

        // Verify all extension points were called
        $this->assertContains('beforeExecute', $framework->calls);
        $this->assertContains('beforeRule', $framework->calls);
        $this->assertContains('afterRule', $framework->calls);
        $this->assertContains('beforeComplete', $framework->calls);
        $this->assertContains('afterComplete', $framework->calls);
    }

    /**
     * Test execution time tracking
     */
    public function testExecutionTimeTracking(): void
    {
        $rule = new ValidationRuleDefinition(
            id: 'test-rule',
            name: 'TestRule',
            ruleType: 'required',
            target: 'email',
            message: 'Email required',
            errorCode: 'VAL_001'
        );

        $validation = new ValidationDefinition(
            id: 'timing-validation',
            name: 'TimingValidation',
            displayName: 'Timing Validation',
            rules: [$rule]
        );

        $this->registry->register($validation);

        $framework = new class($this->registry, $this->lifecycleManager) extends ValidationFramework {
            protected function evaluateRule($rule, $context): bool
            {
                return true;
            }
        };

        $context = new ValidationContext();
        $result = $framework->execute('timing-validation', $context);

        // Execution time should be recorded (>= 0)
        $this->assertGreaterThanOrEqual(0, $result->executionTime);
    }

    /**
     * Test lifecycle manager can be set
     */
    public function testLifecycleManagerCanBeSet(): void
    {
        $framework = new ValidationFramework($this->registry);
        $this->assertNull($framework->getLifecycleManager());

        $result = $framework->setLifecycleManager($this->lifecycleManager);
        $this->assertSame($framework, $result);
        $this->assertSame($this->lifecycleManager, $framework->getLifecycleManager());
    }

    /**
     * Test validation without lifecycle manager
     */
    public function testValidationWithoutLifecycleManager(): void
    {
        $rule = new ValidationRuleDefinition(
            id: 'test-rule',
            name: 'TestRule',
            ruleType: 'required',
            target: 'email',
            message: 'Email required',
            errorCode: 'VAL_001'
        );

        $validation = new ValidationDefinition(
            id: 'no-lifecycle-validation',
            name: 'NoLifecycleValidation',
            displayName: 'No Lifecycle Validation',
            rules: [$rule]
        );

        $this->registry->register($validation);

        $framework = new ValidationFramework($this->registry); // No lifecycle manager

        $framework = new class($this->registry) extends ValidationFramework {
            protected function evaluateRule($rule, $context): bool
            {
                return true;
            }
        };

        $context = new ValidationContext();
        $result = $framework->execute('no-lifecycle-validation', $context);

        $this->assertTrue($result->passed);
    }

    /**
     * Test result to array
     */
    public function testResultToArray(): void
    {
        $rule = new ValidationRuleDefinition(
            id: 'test-rule',
            name: 'TestRule',
            ruleType: 'required',
            target: 'email',
            message: 'Email required',
            errorCode: 'VAL_001'
        );

        $validation = new ValidationDefinition(
            id: 'array-validation',
            name: 'ArrayValidation',
            displayName: 'Array Validation',
            rules: [$rule]
        );

        $this->registry->register($validation);

        $framework = new class($this->registry, $this->lifecycleManager) extends ValidationFramework {
            protected function evaluateRule($rule, $context): bool
            {
                return false;
            }
        };

        $context = new ValidationContext();
        $result = $framework->execute('array-validation', $context);

        $array = $result->toArray();

        $this->assertArrayHasKey('validationId', $array);
        $this->assertArrayHasKey('executionId', $array);
        $this->assertArrayHasKey('passed', $array);
        $this->assertArrayHasKey('issues', $array);
        $this->assertArrayHasKey('errorCount', $array);
        $this->assertArrayHasKey('summary', $array);
    }

    /**
     * Test empty rules validation
     */
    public function testEmptyRulesValidation(): void
    {
        $validation = new ValidationDefinition(
            id: 'empty-rules-validation',
            name: 'EmptyRulesValidation',
            displayName: 'Empty Rules Validation',
            rules: []
        );

        $this->registry->register($validation);

        $framework = new ValidationFramework($this->registry, $this->lifecycleManager);

        $context = new ValidationContext();
        $result = $framework->execute('empty-rules-validation', $context);

        // No rules = pass
        $this->assertTrue($result->passed);
        $this->assertEmpty($result->issues);
    }
}
