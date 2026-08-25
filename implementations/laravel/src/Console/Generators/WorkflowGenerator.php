<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Console\Generators;

/**
 * WorkflowGenerator
 *
 * Generates workflow artifacts:
 * 1. WorkflowDefinition - immutable registry definition (execution specification)
 * 2. Workflow model - governance model (business ownership, lifecycle, SLA, KPIs)
 *
 * Both are generated because they serve different purposes:
 * - WorkflowDefinition describes the technical execution (immutable, registered)
 * - Workflow handles business governance (mutable, manages ownership and SLA)
 *
 * @package WaysNX\BusinessFramework\Console\Generators
 */
class WorkflowGenerator extends ArtifactGenerator
{
    /**
     * Optional domain name
     *
     * @var string|null
     */
    private ?string $domain;

    /**
     * Optional capability name
     *
     * @var string|null
     */
    private ?string $capability;

    /**
     * Optional description
     *
     * @var string|null
     */
    private ?string $description;

    /**
     * Set domain name
     *
     * @param string $domain
     * @return self
     */
    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Set capability name
     *
     * @param string $capability
     * @return self
     */
    public function setCapability(string $capability): self
    {
        $this->capability = $capability;
        return $this;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Generate the workflow artifacts
     *
     * @return GenerationResult
     */
    public function generate(): GenerationResult
    {
        try {
            $files = [];

            // Generate WorkflowDefinition
            $definitionFile = $this->generateWorkflowDefinition();
            if ($definitionFile) {
                $files[] = $definitionFile;
            }

            // Generate Workflow model (optional, governance object)
            $modelFile = $this->generateWorkflowModel();
            if ($modelFile) {
                $files[] = $modelFile;
            }

            return GenerationResult::success($files, [
                'type' => 'workflow',
                'name' => $this->name,
                'module' => $this->module,
                'namespace' => $this->namespace,
            ]);
        } catch (\Throwable $e) {
            return GenerationResult::failure(
                "Failed to generate workflow: {$e->getMessage()}",
                'generation_error'
            );
        }
    }

    /**
     * Generate WorkflowDefinition factory class
     *
     * Generates a factory that creates the immutable WorkflowDefinition.
     * This definition is registered in WorkflowRegistry and provides
     * the technical execution specification.
     *
     * @return string|null
     */
    private function generateWorkflowDefinition(): ?string
    {
        $className = $this->getDefinitionClassName();
        $displayName = $this->toPascalCase($this->name);
        $workflowId = $this->toSnakeCase($this->name);
        $description = $this->description ?? "Workflow: {$this->name}";
        $domain = $this->domain ?? 'default';

        $code = <<<PHP
declare(strict_types=1);

namespace {$this->namespace};

use WaysNX\\BusinessFramework\\Registry\\WorkflowDefinition;

/**
 * {$className}
 *
 * Factory for creating the {$displayName} workflow definition.
 *
 * This class generates the immutable WorkflowDefinition that is registered
 * in WorkflowRegistry. It serves as the technical execution specification.
 *
 * The definition describes:
 * - Workflow identity and metadata
 * - Trigger mechanism
 * - Entry/exit functions
 * - Workflow steps
 * - Supported entity types
 *
 * @package {$this->namespace}
 */
class {$className}
{
    /**
     * Create the workflow definition
     *
     * @return WorkflowDefinition
     */
    public static function create(): WorkflowDefinition
    {
        return new WorkflowDefinition(
            id: '{$workflowId}',
            name: '{$displayName}',
            displayName: '{$displayName}',
            description: '{$description}',
            version: '1.0.0',
            moduleId: '{$this->module}',
            category: '{$domain}',
            triggerType: 'manual',
            triggerEvent: '',
            entryFunction: '',
            exitFunction: '',
            steps: [],
            supportedEntityTypes: [],
            priority: 0,
            enabled: true,
            tags: [],
            metadata: []
        );
    }
}
PHP;

        $filePath = $this->outputPath . '/' . $className . '.php';
        if ($this->writeFile($filePath, $this->generateClassFile($code))) {
            return $filePath;
        }

        return null;
    }

    /**
     * Generate Workflow governance model class
     *
     * Generates a concrete Workflow implementation for business governance.
     * This class handles:
     * - Business ownership
     * - SLA definitions
     * - KPI tracking
     * - Lifecycle management
     * - Dependencies
     *
     * Coexists with WorkflowDefinition which handles technical execution.
     *
     * @return string|null
     */
    private function generateWorkflowModel(): ?string
    {
        $className = 'Workflow' . $this->getClassName();
        $displayName = $this->toPascalCase($this->name);
        $workflowId = $this->toSnakeCase($this->name);
        $description = $this->description ?? "Workflow for {$this->name}";
        $capability = $this->capability ?? 'default';

        $code = <<<PHP
declare(strict_types=1);

namespace {$this->namespace};

use WaysNX\\BusinessFramework\\Models\\Workflow as BaseWorkflow;

/**
 * {$className}
 *
 * Generated workflow governance model for {$displayName}.
 *
 * This class represents the business governance aspects of the workflow.
 * It coexists with the WorkflowDefinition which handles execution specifics.
 *
 * Responsibilities:
 * - Business ownership and stewardship
 * - SLA and performance expectations
 * - KPI definitions and tracking
 * - Workflow dependencies
 * - Lifecycle and status management
 *
 * @package {$this->namespace}
 */
class {$className} extends BaseWorkflow
{
    /**
     * Initialize the workflow with governance defaults
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        
        // Set workflow identity
        \$this->setWorkflowId('{$workflowId}');
        \$this->setWorkflowName('{$displayName}');
        \$this->setDescription('{$description}');
        
        // Set business context
        \$this->setCapabilityId('{$capability}');
        \$this->setStatus('Active');
        
        // Reference the technical definition
        // This should match the WorkflowDefinition ID
        \$this->setWorkflowDefinitionId('{$workflowId}');
        
        // Set ownership (TODO: update with actual stakeholders)
        \$this->setBusinessOwner('to-be-assigned');
        
        // Add optional KPIs and SLA as needed
        // Call parent methods directly:
        // \$this->addKpi('completion-rate');
        // \$this->setSla('5 business days');
    }
}
PHP;

        $filePath = $this->outputPath . '/' . $className . '.php';
        if ($this->writeFile($filePath, $this->generateClassFile($code))) {
            return $filePath;
        }

        return null;
    }
}
