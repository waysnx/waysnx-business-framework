<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Console\Commands;

use WaysNX\BusinessFramework\Console\BaseWbfCommand;
use WaysNX\BusinessFramework\Registry\WorkflowRegistry;
use WaysNX\BusinessFramework\Registry\EntityRegistry;
use WaysNX\BusinessFramework\Registry\ModuleRegistry;
use WaysNX\BusinessFramework\Registry\ValidationRegistry;
use WaysNX\BusinessFramework\Registry\BusinessFunctionRegistry;
use WaysNX\BusinessFramework\Registry\ModuleDefinition;
use WaysNX\BusinessFramework\Registry\EntityDefinition;
use WaysNX\BusinessFramework\Registry\BusinessFunctionDefinition;
use WaysNX\BusinessFramework\Registry\ValidationDefinition;
use WaysNX\BusinessFramework\Registry\WorkflowDefinition;
use WaysNX\BusinessFramework\Registry\WorkflowStep;
use WaysNX\BusinessFramework\Workflow\WorkflowEngine;
use WaysNX\BusinessFramework\Workflow\WorkflowContext;
use WaysNX\BusinessFramework\Lifecycle\LifecycleManager;

/**
 * CreateDemoCommand
 *
 * Create and execute a complete WBF demonstration.
 *
 * Demonstrates all WBF components in action:
 * - Module definition
 * - Entity definition
 * - Business Functions
 * - Validations
 * - Workflow orchestration
 * - Lifecycle management
 * - End-to-end execution
 *
 * Usage:
 *   php artisan wbf:createdemo
 *   php artisan wbf:createdemo --json
 *
 * @package WaysNX\BusinessFramework\Console\Commands
 */
class CreateDemoCommand extends BaseWbfCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wbf:createdemo
        {--json : Output as JSON format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and execute a complete WBF demonstration';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            $result = $this->runDemo();

            if ($this->jsonOutput) {
                $this->line(json_encode($result['json'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                return $result['success'] ? self::EXIT_SUCCESS : self::EXIT_ERROR;
            }

            $this->line($result['output']);
            return $result['success'] ? self::EXIT_SUCCESS : self::EXIT_ERROR;
        } catch (\Throwable $e) {
            return $this->errorOutput(
                "Demo execution failed: {$e->getMessage()}",
                self::EXIT_SYSTEM_FAILURE,
                'demo_error'
            );
        }
    }

    /**
     * Run the complete demo
     *
     * @return array Demo result [success, output, json]
     */
    private function runDemo(): array
    {
        $output = "\n";
        $output .= "╔════════════════════════════════════════════════════════════════╗\n";
        $output .= "║  WaysNX Business Framework - Complete Demonstration            ║\n";
        $output .= "║  Hospital Audit Management Example                            ║\n";
        $output .= "╚════════════════════════════════════════════════════════════════╝\n";
        $output .= "\n";

        // ========== PHASE 1: Define Artifacts ==========
        $output .= "📋 PHASE 1: DEFINING WBF ARTIFACTS\n";
        $output .= "═══════════════════════════════════\n";
        $output .= "\n";

        // Define Module
        $auditModule = new ModuleDefinition(
            id: 'AUDIT',
            name: 'AuditManagement',
            displayName: 'Hospital Audit Management',
            description: 'Manages hospital audit processes, findings, and compliance reviews'
        );
        $output .= "✓ Module: \"{$auditModule->displayName}\"\n";

        // Define Entity
        $auditEntity = new EntityDefinition(
            id: 'AUDIT',
            name: 'Audit',
            displayName: 'Audit',
            className: 'WaysNX\\Demo\\Entities\\Audit',
            namespace: 'WaysNX\\Demo\\Entities',
            description: 'Hospital audit record'
        );
        $output .= "✓ Entity: \"{$auditEntity->displayName}\"\n";

        // Define Business Functions
        $validateAuditFn = new BusinessFunctionDefinition(
            id: 'AUDIT.VALIDATE.AUDIT.VALIDATE_AUDIT',
            name: 'ValidateAudit',
            displayName: 'Validate Audit',
            description: 'Validate audit record completeness and data quality',
            moduleId: 'AUDIT',
            category: 'validation',
            inputDefinitions: [['name' => 'auditId', 'type' => 'string']],
            outputDefinitions: [['name' => 'valid', 'type' => 'boolean']]
        );
        $output .= "✓ Business Function: \"{$validateAuditFn->displayName}\"\n";

        $recordFindingFn = new BusinessFunctionDefinition(
            id: 'AUDIT.RECORD.FINDING.RECORD_FINDING',
            name: 'RecordFinding',
            displayName: 'Record Finding',
            description: 'Record audit findings and compliance issues',
            moduleId: 'AUDIT',
            category: 'record',
            inputDefinitions: [['name' => 'auditId', 'type' => 'string']],
            outputDefinitions: [['name' => 'findingId', 'type' => 'string']]
        );
        $output .= "✓ Business Function: \"{$recordFindingFn->displayName}\"\n";

        $completeAuditFn = new BusinessFunctionDefinition(
            id: 'AUDIT.COMPLETE.AUDIT.COMPLETE_AUDIT',
            name: 'CompleteAudit',
            displayName: 'Complete Audit',
            description: 'Mark audit as complete and generate report',
            moduleId: 'AUDIT',
            category: 'completion',
            inputDefinitions: [['name' => 'auditId', 'type' => 'string']],
            outputDefinitions: [['name' => 'completed', 'type' => 'boolean']]
        );
        $output .= "✓ Business Function: \"{$completeAuditFn->displayName}\"\n";

        // Define Validations
        $dataValidation = new ValidationDefinition(
            id: 'AUDIT_DATA_VALID',
            name: 'AuditDataValidation',
            displayName: 'Audit Data Validation',
            description: 'Validate audit record has required fields',
            moduleId: 'AUDIT',
            scope: 'Audit',
            severity: 'error'
        );
        $output .= "✓ Validation: \"{$dataValidation->displayName}\"\n";

        // Define Workflow with steps
        $workflowSteps = [
            new WorkflowStep(
                id: 'validate-step',
                businessFunctionId: 'AUDIT.VALIDATE.AUDIT.VALIDATE_AUDIT',
                sequence: 0,
                description: 'Validate Audit',
                metadata: ['displayName' => 'Validate Audit']
            ),
            new WorkflowStep(
                id: 'record-step',
                businessFunctionId: 'AUDIT.RECORD.FINDING.RECORD_FINDING',
                sequence: 1,
                description: 'Record Finding',
                metadata: ['displayName' => 'Record Finding']
            ),
            new WorkflowStep(
                id: 'complete-step',
                businessFunctionId: 'AUDIT.COMPLETE.AUDIT.COMPLETE_AUDIT',
                sequence: 2,
                description: 'Complete Audit',
                metadata: ['displayName' => 'Complete Audit']
            ),
        ];

        $auditWorkflow = new WorkflowDefinition(
            id: 'audit-review',
            name: 'AuditReview',
            displayName: 'Audit Review',
            description: 'Complete audit review and compliance process',
            moduleId: 'AUDIT',
            steps: $workflowSteps
        );
        $output .= "✓ Workflow: \"{$auditWorkflow->displayName}\" (" . count($auditWorkflow->steps) . " steps)\n";
        $output .= "\n";

        // ========== PHASE 2: Register Artifacts ==========
        $output .= "📚 PHASE 2: REGISTERING IN REGISTRIES\n";
        $output .= "═════════════════════════════════════\n";
        $output .= "\n";

        $moduleRegistry = $this->resolve(ModuleRegistry::class);
        $entityRegistry = $this->resolve(EntityRegistry::class);
        $functionRegistry = $this->resolve(BusinessFunctionRegistry::class);
        $validationRegistry = $this->resolve(ValidationRegistry::class);
        $workflowRegistry = $this->resolve(WorkflowRegistry::class);

        // Clear registries to allow repeatability
        $moduleRegistry->clear();
        $entityRegistry->clear();
        $functionRegistry->clear();
        $validationRegistry->clear();
        $workflowRegistry->clear();

        // Register all artifacts
        $moduleRegistry->register($auditModule);
        $output .= "✓ Registered Module ({$moduleRegistry->count()} total)\n";

        $entityRegistry->register($auditEntity);
        $output .= "✓ Registered Entity ({$entityRegistry->count()} total)\n";

        $functionRegistry->register($validateAuditFn);
        $functionRegistry->register($recordFindingFn);
        $functionRegistry->register($completeAuditFn);
        $output .= "✓ Registered Business Functions ({$functionRegistry->count()} total)\n";

        $validationRegistry->register($dataValidation);
        $output .= "✓ Registered Validation ({$validationRegistry->count()} total)\n";

        $workflowRegistry->register($auditWorkflow);
        $output .= "✓ Registered Workflow ({$workflowRegistry->count()} total)\n";
        $output .= "\n";

        // ========== PHASE 3: Discover Artifacts ==========
        $output .= "🔍 PHASE 3: DISCOVERING REGISTERED ARTIFACTS\n";
        $output .= "═════════════════════════════════════════════\n";
        $output .= "\n";

        $foundModule = $moduleRegistry->findById('AUDIT');
        $output .= "✓ Found Module: {$foundModule->displayName}\n";

        $foundWorkflow = $workflowRegistry->findById('audit-review');
        $output .= "✓ Found Workflow: {$foundWorkflow->displayName}\n";
        $output .= "\n";

        // ========== PHASE 4: Execute Workflow ==========
        $output .= "⚙️  PHASE 4: EXECUTING WORKFLOW WITH RUNTIME\n";
        $output .= "════════════════════════════════════════════\n";
        $output .= "\n";

        // Create workflow context with test audit entity
        $testAudit = [
            'id' => 'AUDIT-2024-001',
            'hospital' => 'Memorial Hospital',
            'department' => 'Emergency',
            'auditDate' => date('c'),
        ];

        $executionId = 'exec-' . time();
        $correlationId = 'corr-' . time();

        $context = new WorkflowContext(
            workflowId: 'audit-review',
            executionId: $executionId,
            correlationId: $correlationId,
            entity: $testAudit,
            moduleId: 'AUDIT',
            inputData: ['auditData' => $testAudit]
        );

        $output .= "Executing workflow \"{$auditWorkflow->displayName}\"...\n";
        $output .= "Entity: Audit {$testAudit['id']} at {$testAudit['hospital']}\n";
        $output .= "Correlation ID: {$context->correlationId}\n";
        $output .= "\n";

        // Resolve engine and execute workflow
        $workflowEngine = $this->resolve(WorkflowEngine::class);
        $result = $workflowEngine->execute('audit-review', $context);

        $output .= "📊 WORKFLOW EXECUTION RESULT\n";
        $output .= "════════════════════════════\n";
        $output .= "\n";

        $output .= "Status: " . strtoupper($result->status) . "\n";
        $output .= "Execution ID: {$result->executionId}\n";
        $output .= "Duration: {$result->duration}ms\n";
        $output .= "\n";
        $output .= "Steps:\n";
        $output .= "  ✓ Completed: " . count($result->completedSteps) . "\n";
        foreach ($result->completedSteps as $step) {
            $output .= "    - {$step}\n";
        }
        if (count($result->failedSteps) > 0) {
            $output .= "  ✗ Failed: " . count($result->failedSteps) . "\n";
            foreach ($result->failedSteps as $step) {
                $output .= "    - {$step}\n";
            }
        }
        $output .= "\n";

        // ========== PHASE 5: Display Summary ==========
        $output .= "✨ WBF DEMONSTRATION SUMMARY\n";
        $output .= "═════════════════════════════\n";
        $output .= "\n";

        $output .= "✓ Module: Hospital Audit Management\n";
        $output .= "✓ Entity: Audit record\n";
        $output .= "✓ Business Functions: 3 (Validate, Record, Complete)\n";
        $output .= "✓ Validations: Business rules defined and registered\n";
        $output .= "✓ Workflow: Multi-step orchestration\n";
        $output .= "✓ Execution: " . ($result->succeeded() ? 'Successful completion' : 'Completed with issues') . "\n";
        $output .= "✓ Lifecycle: Event dispatch\n";
        $output .= "✓ Result: Immutable outcome tracking\n";

        $output .= "\n";
        $output .= "📚 WBF CONCEPTS DEMONSTRATED:\n";
        $output .= "\n";

        $output .= "1. Business-First Modeling\n";
        $output .= "   → All artifacts defined in business terms\n";
        $output .= "\n";

        $output .= "2. Explicit Business Functions\n";
        $output .= "   → Named, versioned operations with contracts\n";
        $output .= "\n";

        $output .= "3. Business Validation\n";
        $output .= "   → Business rules defined and available for validation\n";
        $output .= "\n";

        $output .= "4. Workflow Orchestration\n";
        $output .= "   → " . count($result->completedSteps) . " steps executed in sequence\n";
        $output .= "\n";

        $output .= "5. Lifecycle Management\n";
        $output .= "   → Events dispatched throughout execution\n";
        $output .= "\n";

        $output .= "6. Structured Results\n";
        $output .= "   → Immutable outcome with complete tracking\n";
        $output .= "\n";

        $output .= "7. Registry-Based Discovery\n";
        $output .= "   → All artifacts discoverable and inspectable\n";
        $output .= "\n";

        $output .= "8. Framework-Neutral Architecture\n";
        $output .= "   → Pure business logic, no HTTP/ORM coupling\n";
        $output .= "\n";

        $output .= "╔════════════════════════════════════════════════════════════════╗\n";
        $output .= "║                  ✓ DEMO COMPLETED SUCCESSFULLY               ║\n";
        $output .= "╚════════════════════════════════════════════════════════════════╝\n";

        // Prepare JSON output
        $jsonData = [
            'success' => $result->succeeded(),
            'demo' => [
                'name' => 'Hospital Audit Management',
                'domain' => 'Audit',
            ],
            'artifacts' => [
                'module' => [
                    'id' => $auditModule->id,
                    'displayName' => $auditModule->displayName,
                ],
                'entities' => [
                    [
                        'id' => $auditEntity->id,
                        'displayName' => $auditEntity->displayName,
                    ],
                ],
                'businessFunctions' => [
                    ['id' => $validateAuditFn->id, 'displayName' => $validateAuditFn->displayName],
                    ['id' => $recordFindingFn->id, 'displayName' => $recordFindingFn->displayName],
                    ['id' => $completeAuditFn->id, 'displayName' => $completeAuditFn->displayName],
                ],
                'validations' => [
                    [
                        'id' => $dataValidation->id,
                        'displayName' => $dataValidation->displayName,
                    ],
                ],
                'workflow' => [
                    'id' => $auditWorkflow->id,
                    'displayName' => $auditWorkflow->displayName,
                    'steps' => count($auditWorkflow->steps),
                ],
            ],
            'execution' => [
                'executionId' => $result->executionId,
                'workflowId' => $result->workflowId,
                'status' => $result->status,
                'completedSteps' => $result->completedSteps,
                'failedSteps' => $result->failedSteps,
                'duration' => $result->duration,
            ],
        ];

        return [
            'success' => $result->succeeded(),
            'output' => $output,
            'json' => $jsonData,
        ];
    }
}
