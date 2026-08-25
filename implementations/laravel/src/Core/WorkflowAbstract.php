<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Core;

/**
 * WorkflowAbstract
 *
 * Framework-independent abstract base class for WBF Workflow model.
 *
 * A Workflow is an ordered sequence of business activities performed to achieve a defined
 * business outcome. It is the fourth architectural layer: Capability → Workflow → Service/Step.
 *
 * This class implements the requirements defined in WBF-DOC-0009 Workflow Specification:
 * - Workflow Identity (ID, Name, Description)
 * - Workflow Lifecycle (Draft, Review, Approved, Active, Suspended, Retired - 6 states)
 * - Workflow Ownership (Business Owner)
 * - Workflow Governance (Status, SLA, KPIs, Dependencies)
 * - Workflow Metadata (Trigger, Inputs, Outputs, Services, Steps, Metadata)
 * - Workflow Capability Relationship (Workflow belongs to exactly one Business Capability)
 * - Workflow Definition Reference (Links to technical WorkflowDefinition for execution)
 *
 * IMPORTANT: Coexistence with WorkflowDefinition
 * - WorkflowDefinition: Technical execution definition (immutable, for registry)
 * - Workflow: Business governance model (mutable, for business management)
 * - Workflow.workflowDefinitionId references the technical definition
 * - Workflow does NOT duplicate WorkflowDefinition properties
 * - WorkflowEngine remains unchanged; application integrates both layers
 *
 * Architecture:
 * - Extends BaseModelAbstract (framework-independent base)
 * - Implements WBF architectural hierarchy (Capability → Workflow → Service/Step)
 * - Provides framework-independent contract
 * - Framework-specific implementations extend this class
 *
 * Usage:
 * ```php
 * class Workflow extends WorkflowAbstract {
 *     // Framework-specific implementation
 * }
 *
 * $workflow = new Workflow();
 * $workflow->setWorkflowId('emp-onboarding');
 * $workflow->setWorkflowName('Employee Onboarding');
 * $workflow->setDescription('Complete employee onboarding process');
 * $workflow->setCapabilityId('employee-mgmt');
 * $workflow->setBusinessOwner('hr-manager-001');
 * $workflow->setStatus(WorkflowAbstract::ACTIVE);
 * $workflow->setWorkflowDefinitionId('onboarding-v1');
 * $workflow->setSla('5 days');
 * $workflow->addKpi('completion-rate');
 * $workflow->addService('email-service');
 * $workflow->addDependency('pre-hire-workflow');
 * ```
 *
 * @package WaysNX\BusinessFramework\Core
 */
abstract class WorkflowAbstract extends BaseModelAbstract
{
    /**
     * Workflow Lifecycle States (WBF-DOC-0009 Section 7)
     *
     * Workflows transition through these 6 lifecycle states (different from Module/Domain's 8 states):
     * Draft → Review → Approved → Active → Suspended → Retired
     *
     * This is distinct from Capability's 9 states and represents the workflow execution lifecycle.
     */
    public const DRAFT = 'Draft';
    public const REVIEW = 'Review';
    public const APPROVED = 'Approved';
    public const ACTIVE = 'Active';
    public const SUSPENDED = 'Suspended';
    public const RETIRED = 'Retired';

    /**
     * Valid lifecycle states (WBF-DOC-0009 Section 7)
     *
     * @var array<string>
     */
    protected const VALID_STATUSES = [
        self::DRAFT,
        self::REVIEW,
        self::APPROVED,
        self::ACTIVE,
        self::SUSPENDED,
        self::RETIRED,
    ];

    /**
     * Workflow identifier (WBF-DOC-0009 Section 10)
     *
     * Unique identifier for the workflow within the capability.
     * Examples: 'emp-onboarding', 'leave-approval', 'recruitment-screening'
     *
     * @var string
     */
    protected string $workflowId = '';

    /**
     * Workflow name (WBF-DOC-0009 Section 10)
     *
     * Human-readable workflow name.
     * Examples: 'Employee Onboarding', 'Leave Approval', 'Recruitment Screening'
     *
     * @var string
     */
    protected string $workflowName = '';

    /**
     * Workflow description (WBF-DOC-0009 Section 10)
     *
     * Detailed description of what the workflow accomplishes.
     *
     * @var string
     */
    protected string $description = '';

    /**
     * Parent capability identifier (WBF-DOC-0009 Section 10)
     *
     * Reference to the Business Capability that owns this workflow.
     * A workflow belongs to exactly one capability.
     *
     * @var string|int
     */
    protected string|int $capabilityId = '';

    /**
     * Business owner identifier (WBF-DOC-0009 Section 10)
     *
     * Reference to the person or role responsible for this workflow.
     *
     * @var string|int
     */
    protected string|int $businessOwner = '';

    /**
     * Workflow status (WBF-DOC-0009 Section 7)
     *
     * Current lifecycle state: Draft, Review, Approved, Active, Suspended, Retired
     *
     * @var string
     */
    protected string $status = self::DRAFT;

    /**
     * Technical workflow definition identifier (WBF-DOC-0009 Section 10)
     *
     * Reference to the WorkflowDefinition in WorkflowRegistry.
     * This links the business workflow model to the technical execution definition.
     * Optional: workflow may exist in planning phase before technical definition.
     *
     * @var string
     */
    protected string $workflowDefinitionId = '';

    /**
     * Workflow trigger (WBF-DOC-0009 Section 10)
     *
     * Describes how the workflow is initiated.
     * Examples: 'new-employee', 'leave-request', 'manual'
     *
     * @var string
     */
    protected string $trigger = '';

    /**
     * Workflow inputs (WBF-DOC-0009 Section 10)
     *
     * Array of input parameters or documents required by the workflow.
     *
     * @var array
     */
    protected array $inputs = [];

    /**
     * Workflow outputs (WBF-DOC-0009 Section 10)
     *
     * Array of output parameters or documents produced by the workflow.
     *
     * @var array
     */
    protected array $outputs = [];

    /**
     * Referenced services (WBF-DOC-0009 Section 10)
     *
     * Array of service IDs that this workflow utilizes.
     * Stores references (IDs) only, not full service objects.
     *
     * @var array<string>
     */
    protected array $services = [];

    /**
     * Referenced steps (WBF-DOC-0009 Section 10)
     *
     * Array of step IDs in this workflow.
     * Note: Full step implementations come from WorkflowDefinition.
     * This array may store business-level step references for governance purposes.
     *
     * @var array<string>
     */
    protected array $steps = [];

    /**
     * Key Performance Indicators (WBF-DOC-0009 Section 10)
     *
     * Array of KPI identifiers or definitions that measure workflow effectiveness.
     *
     * @var array
     */
    protected array $kpis = [];

    /**
     * Workflow dependencies (WBF-DOC-0009 Section 10)
     *
     * Array of workflow IDs that must complete before this workflow can execute.
     *
     * @var array<string>
     */
    protected array $dependencies = [];

    /**
     * Service Level Agreement (WBF-DOC-0009 Section 10)
     *
     * Description of expected performance or completion timeframe.
     * Examples: '24 hours', '5 business days', 'same-day'
     *
     * @var string
     */
    protected string $sla = '';

    // ========================================
    // INITIALIZATION
    // ========================================

    /**
     * Initialize workflow after construction
     *
     * Subclasses should call this method during their construction process.
     * Sets up default values and validates mandatory fields.
     *
     * @return void
     */
    protected function initializeWorkflow(): void
    {
        $this->status = self::DRAFT;
        $this->services = [];
        $this->steps = [];
        $this->kpis = [];
        $this->dependencies = [];
        $this->inputs = [];
        $this->outputs = [];
    }

    /**
     * Validate mandatory workflow fields
     *
     * Called by subclasses before saving/persisting the workflow.
     * Ensures all required fields have valid values.
     *
     * @return void
     * @throws \InvalidArgumentException If any mandatory field is invalid
     */
    protected function validateMandatoryWorkflowFields(): void
    {
        if (empty($this->workflowId)) {
            throw new \InvalidArgumentException('Workflow ID is required');
        }

        if (empty($this->workflowName)) {
            throw new \InvalidArgumentException('Workflow Name is required');
        }

        if (empty($this->description)) {
            throw new \InvalidArgumentException('Workflow Description is required');
        }

        if (empty($this->capabilityId)) {
            throw new \InvalidArgumentException('Capability ID (parent) is required');
        }

        if (empty($this->businessOwner)) {
            throw new \InvalidArgumentException('Business Owner is required');
        }

        if (!in_array($this->status, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException(
                'Invalid status: ' . $this->status . '. Must be one of: ' . implode(', ', self::VALID_STATUSES)
            );
        }
    }

    // ========================================
    // WORKFLOW IDENTITY ACCESSORS
    // ========================================

    /**
     * Get workflow identifier
     *
     * @return string The workflow ID
     */
    public function getWorkflowId(): string
    {
        return $this->workflowId;
    }

    /**
     * Set workflow identifier
     *
     * @param string $workflowId The workflow ID
     * @return void
     */
    public function setWorkflowId(string $workflowId): void
    {
        if (empty($workflowId)) {
            throw new \InvalidArgumentException('Workflow ID cannot be empty');
        }
        $this->workflowId = $workflowId;
    }

    /**
     * Get workflow name
     *
     * @return string The workflow name
     */
    public function getWorkflowName(): string
    {
        return $this->workflowName;
    }

    /**
     * Set workflow name
     *
     * @param string $workflowName The workflow name
     * @return void
     */
    public function setWorkflowName(string $workflowName): void
    {
        if (empty($workflowName)) {
            throw new \InvalidArgumentException('Workflow Name cannot be empty');
        }
        $this->workflowName = $workflowName;
    }

    /**
     * Get workflow description
     *
     * @return string The description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set workflow description
     *
     * @param string $description The description
     * @return void
     */
    public function setDescription(string $description): void
    {
        if (empty($description)) {
            throw new \InvalidArgumentException('Workflow Description cannot be empty');
        }
        $this->description = $description;
    }

    // ========================================
    // RELATIONSHIP ACCESSORS
    // ========================================

    /**
     * Get parent capability identifier
     *
     * @return string|int The capability ID
     */
    public function getCapabilityId(): string|int
    {
        return $this->capabilityId;
    }

    /**
     * Set parent capability identifier
     *
     * @param string|int $capabilityId The capability ID
     * @return void
     */
    public function setCapabilityId(string|int $capabilityId): void
    {
        if (empty($capabilityId)) {
            throw new \InvalidArgumentException('Capability ID cannot be empty');
        }
        $this->capabilityId = $capabilityId;
    }

    /**
     * Get business owner identifier
     *
     * @return string|int The business owner
     */
    public function getBusinessOwner(): string|int
    {
        return $this->businessOwner;
    }

    /**
     * Set business owner identifier
     *
     * @param string|int $businessOwner The business owner
     * @return void
     */
    public function setBusinessOwner(string|int $businessOwner): void
    {
        if (empty($businessOwner)) {
            throw new \InvalidArgumentException('Business Owner cannot be empty');
        }
        $this->businessOwner = $businessOwner;
    }

    // ========================================
    // STATUS/LIFECYCLE ACCESSORS
    // ========================================

    /**
     * Get workflow status
     *
     * @return string The status
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set workflow status
     *
     * @param string $status The status (must be one of VALID_STATUSES)
     * @return void
     */
    public function setStatus(string $status): void
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException(
                'Invalid status: ' . $status . '. Must be one of: ' . implode(', ', self::VALID_STATUSES)
            );
        }
        $this->status = $status;
    }

    /**
     * Check if workflow is in draft status
     *
     * @return bool True if in draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::DRAFT;
    }

    /**
     * Check if workflow is in review status
     *
     * @return bool True if in review
     */
    public function isInReview(): bool
    {
        return $this->status === self::REVIEW;
    }

    /**
     * Check if workflow is approved
     *
     * @return bool True if approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    /**
     * Check if workflow is active
     *
     * @return bool True if active
     */
    public function isActive(): bool
    {
        return $this->status === self::ACTIVE;
    }

    /**
     * Check if workflow is suspended
     *
     * @return bool True if suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === self::SUSPENDED;
    }

    /**
     * Check if workflow is retired
     *
     * @return bool True if retired
     */
    public function isRetired(): bool
    {
        return $this->status === self::RETIRED;
    }

    // ========================================
    // DEFINITION REFERENCE ACCESSORS
    // ========================================

    /**
     * Get technical workflow definition identifier
     *
     * @return string The definition ID (may be empty if in planning phase)
     */
    public function getWorkflowDefinitionId(): string
    {
        return $this->workflowDefinitionId;
    }

    /**
     * Set technical workflow definition identifier
     *
     * @param string $workflowDefinitionId The definition ID
     * @return void
     */
    public function setWorkflowDefinitionId(string $workflowDefinitionId): void
    {
        $this->workflowDefinitionId = $workflowDefinitionId;
    }

    /**
     * Check if workflow has a definition reference
     *
     * @return bool True if definition ID is set
     */
    public function hasWorkflowDefinition(): bool
    {
        return !empty($this->workflowDefinitionId);
    }

    // ========================================
    // TRIGGER/INPUTS/OUTPUTS ACCESSORS
    // ========================================

    /**
     * Get workflow trigger
     *
     * @return string The trigger
     */
    public function getTrigger(): string
    {
        return $this->trigger;
    }

    /**
     * Set workflow trigger
     *
     * @param string $trigger The trigger
     * @return void
     */
    public function setTrigger(string $trigger): void
    {
        $this->trigger = $trigger;
    }

    /**
     * Get workflow inputs
     *
     * @return array The inputs array
     */
    public function getInputs(): array
    {
        return $this->inputs;
    }

    /**
     * Set workflow inputs
     *
     * @param array $inputs The inputs array
     * @return void
     */
    public function setInputs(array $inputs): void
    {
        $this->inputs = $inputs;
    }

    /**
     * Get workflow outputs
     *
     * @return array The outputs array
     */
    public function getOutputs(): array
    {
        return $this->outputs;
    }

    /**
     * Set workflow outputs
     *
     * @param array $outputs The outputs array
     * @return void
     */
    public function setOutputs(array $outputs): void
    {
        $this->outputs = $outputs;
    }

    // ========================================
    // SERVICES COLLECTION MANAGEMENT
    // ========================================

    /**
     * Get all referenced services
     *
     * @return array<string> Array of service IDs
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * Add a service reference
     *
     * @param string $serviceId The service ID to add
     * @return void
     */
    public function addService(string $serviceId): void
    {
        if (empty($serviceId)) {
            throw new \InvalidArgumentException('Service ID cannot be empty');
        }

        if (!in_array($serviceId, $this->services, true)) {
            $this->services[] = $serviceId;
        }
    }

    /**
     * Remove a service reference
     *
     * @param string $serviceId The service ID to remove
     * @return void
     */
    public function removeService(string $serviceId): void
    {
        $this->services = array_values(array_filter(
            $this->services,
            fn($id) => $id !== $serviceId
        ));
    }

    /**
     * Check if service is referenced
     *
     * @param string $serviceId The service ID to check
     * @return bool True if service is referenced
     */
    public function hasService(string $serviceId): bool
    {
        return in_array($serviceId, $this->services, true);
    }

    /**
     * Get service count
     *
     * @return int The number of services
     */
    public function getServiceCount(): int
    {
        return count($this->services);
    }

    // ========================================
    // STEPS COLLECTION MANAGEMENT
    // ========================================

    /**
     * Get all referenced steps
     *
     * @return array<string> Array of step IDs
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * Add a step reference
     *
     * @param string $stepId The step ID to add
     * @return void
     */
    public function addStep(string $stepId): void
    {
        if (empty($stepId)) {
            throw new \InvalidArgumentException('Step ID cannot be empty');
        }

        if (!in_array($stepId, $this->steps, true)) {
            $this->steps[] = $stepId;
        }
    }

    /**
     * Remove a step reference
     *
     * @param string $stepId The step ID to remove
     * @return void
     */
    public function removeStep(string $stepId): void
    {
        $this->steps = array_values(array_filter(
            $this->steps,
            fn($id) => $id !== $stepId
        ));
    }

    /**
     * Check if step is referenced
     *
     * @param string $stepId The step ID to check
     * @return bool True if step is referenced
     */
    public function hasStep(string $stepId): bool
    {
        return in_array($stepId, $this->steps, true);
    }

    /**
     * Get step count
     *
     * @return int The number of steps
     */
    public function getStepCount(): int
    {
        return count($this->steps);
    }

    // ========================================
    // KPI MANAGEMENT
    // ========================================

    /**
     * Get all KPIs
     *
     * @return array The KPIs array
     */
    public function getKpis(): array
    {
        return $this->kpis;
    }

    /**
     * Set all KPIs
     *
     * @param array $kpis The KPIs array
     * @return void
     */
    public function setKpis(array $kpis): void
    {
        $this->kpis = $kpis;
    }

    /**
     * Add a KPI
     *
     * @param string $kpiId The KPI identifier or definition
     * @return void
     */
    public function addKpi(string $kpiId): void
    {
        if (empty($kpiId)) {
            throw new \InvalidArgumentException('KPI cannot be empty');
        }

        if (!in_array($kpiId, $this->kpis, true)) {
            $this->kpis[] = $kpiId;
        }
    }

    /**
     * Remove a KPI
     *
     * @param string $kpiId The KPI identifier or definition
     * @return void
     */
    public function removeKpi(string $kpiId): void
    {
        $this->kpis = array_values(array_filter(
            $this->kpis,
            fn($id) => $id !== $kpiId
        ));
    }

    /**
     * Check if KPI exists
     *
     * @param string $kpiId The KPI identifier or definition
     * @return bool True if KPI exists
     */
    public function hasKpi(string $kpiId): bool
    {
        return in_array($kpiId, $this->kpis, true);
    }

    /**
     * Get KPI count
     *
     * @return int The number of KPIs
     */
    public function getKpiCount(): int
    {
        return count($this->kpis);
    }

    // ========================================
    // DEPENDENCIES MANAGEMENT
    // ========================================

    /**
     * Get all dependencies
     *
     * @return array<string> Array of dependent workflow IDs
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Set all dependencies
     *
     * @param array $dependencies Array of dependent workflow IDs
     * @return void
     */
    public function setDependencies(array $dependencies): void
    {
        $this->dependencies = [];
        foreach ($dependencies as $workflowId) {
            $this->addDependency($workflowId);
        }
    }

    /**
     * Add a dependency
     *
     * @param string $workflowId The workflow ID this depends on
     * @return void
     */
    public function addDependency(string $workflowId): void
    {
        if (empty($workflowId)) {
            throw new \InvalidArgumentException('Workflow ID for dependency cannot be empty');
        }

        if ($workflowId === $this->workflowId) {
            throw new \InvalidArgumentException('Workflow cannot depend on itself');
        }

        if (!in_array($workflowId, $this->dependencies, true)) {
            $this->dependencies[] = $workflowId;
        }
    }

    /**
     * Remove a dependency
     *
     * @param string $workflowId The workflow ID to remove from dependencies
     * @return void
     */
    public function removeDependency(string $workflowId): void
    {
        $this->dependencies = array_values(array_filter(
            $this->dependencies,
            fn($id) => $id !== $workflowId
        ));
    }

    /**
     * Check if has dependency
     *
     * @param string $workflowId The workflow ID to check
     * @return bool True if dependency exists
     */
    public function hasDependency(string $workflowId): bool
    {
        return in_array($workflowId, $this->dependencies, true);
    }

    /**
     * Get dependency count
     *
     * @return int The number of dependencies
     */
    public function getDependencyCount(): int
    {
        return count($this->dependencies);
    }

    // ========================================
    // SLA ACCESSORS
    // ========================================

    /**
     * Get Service Level Agreement
     *
     * @return string The SLA description
     */
    public function getSla(): string
    {
        return $this->sla;
    }

    /**
     * Set Service Level Agreement
     *
     * @param string $sla The SLA description
     * @return void
     */
    public function setSla(string $sla): void
    {
        $this->sla = $sla;
    }

    // ========================================
    // SERIALIZATION (Abstract Methods)
    // ========================================

    /**
     * Convert workflow to array
     *
     * Framework-specific implementations should override this method
     * to provide their own serialization format.
     *
     * @return array The workflow as array
     */
    abstract public function toArray(): array;

    /**
     * Convert workflow to JSON
     *
     * Framework-specific implementations should override this method
     * to provide their own JSON serialization.
     *
     * @return string The workflow as JSON
     */
    abstract public function toJson(): string;

    /**
     * Convert workflow to string
     *
     * @return string The workflow representation
     */
    public function __toString(): string
    {
        return $this->workflowName . ' (' . $this->workflowId . ')';
    }
}
