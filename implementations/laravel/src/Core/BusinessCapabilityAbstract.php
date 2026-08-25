<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Core;

/**
 * BusinessCapabilityAbstract
 *
 * Framework-independent abstract base class for WBF Business Capability model.
 *
 * A Business Capability is a stable, business-centric function that describes what the
 * enterprise must be capable of doing, regardless of organizational structure or technology.
 * It is the third architectural layer: Domain → Capability → Workflow → BusinessFunction.
 *
 * This class implements the requirements defined in WBF-DOC-0008 Business Capability Specification:
 * - Capability Identity (ID, Name, Description)
 * - Capability Lifecycle (Identify, Analyze, Design, Review, Approve, Implement, Operate, Improve, Retire - 9 states)
 * - Capability Ownership (Business Owner)
 * - Capability Business Value (Business Outcome)
 * - Capability Metadata (Version, Status, KPIs, Dependencies, Workflows, Services, Rules, Policies, Events)
 * - Capability Domain Relationship (Capability belongs to exactly one Domain)
 * - Capability Governance (Versioning, Change Management, KPI Review, Documentation, Security)
 *
 * Architecture:
 * - Extends BaseModelAbstract (framework-independent base)
 * - Implements WBF architectural hierarchy (Domain → Capability → Workflow → Function)
 * - Provides framework-independent contract
 * - Framework-specific implementations extend this class
 *
 * Usage:
 * ```php
 * class BusinessCapability extends BusinessCapabilityAbstract {
 *     // Framework-specific implementation
 * }
 *
 * $capability = new BusinessCapability();
 * $capability->setCapabilityId('emp-search');
 * $capability->setCapabilityName('Employee Search');
 * $capability->setDescription('Search and filter employee records');
 * $capability->setDomainId('EMP_MGMT');
 * $capability->setBusinessOwner('hr-manager-001');
 * $capability->setStatus(BusinessCapabilityAbstract::IMPLEMENT);
 * $capability->setBusinessOutcome('Quickly locate employee information');
 * $capability->addWorkflow(['id' => 'wf-search', 'name' => 'Search Workflow']);
 * $capability->addService(['id' => 'svc-search', 'name' => 'Employee Search Service']);
 * ```
 *
 * @package WaysNX\BusinessFramework\Core
 */
abstract class BusinessCapabilityAbstract extends BaseModelAbstract
{
    /**
     * Business Capability Lifecycle States (WBF-DOC-0008 Section 9)
     *
     * Capabilities transition through these 9 lifecycle states (different from Module/Domain's 8 states):
     * Identify → Analyze → Design → Review → Approve → Implement → Operate → Improve → Retire
     */
    public const IDENTIFY = 'Identify';
    public const ANALYZE = 'Analyze';
    public const DESIGN = 'Design';
    public const REVIEW = 'Review';
    public const APPROVE = 'Approve';
    public const IMPLEMENT = 'Implement';
    public const OPERATE = 'Operate';
    public const IMPROVE = 'Improve';
    public const RETIRE = 'Retire';

    /**
     * Valid lifecycle states (WBF-DOC-0008 Section 9)
     *
     * @var array<string>
     */
    protected const VALID_STATUSES = [
        self::IDENTIFY,
        self::ANALYZE,
        self::DESIGN,
        self::REVIEW,
        self::APPROVE,
        self::IMPLEMENT,
        self::OPERATE,
        self::IMPROVE,
        self::RETIRE,
    ];

    /**
     * Capability identifier (WBF-DOC-0008 Section 10)
     *
     * Unique identifier for the capability within the domain.
     * Examples: 'emp-search', 'leave-approval', 'recruitment'
     *
     * @var string
     */
    protected string $capabilityId = '';

    /**
     * Capability name (WBF-DOC-0008 Section 10)
     *
     * Human-readable capability name.
     * Examples: 'Employee Search', 'Leave Approval', 'Recruitment'
     *
     * @var string
     */
    protected string $capabilityName = '';

    /**
     * Capability description (WBF-DOC-0008 Section 10)
     *
     * Detailed description of the capability's business purpose and scope.
     *
     * @var string
     */
    protected string $description = '';

    /**
     * Parent Domain identifier (WBF-DOC-0008 Section 10, WBF-DOC-0004 Section 5.2)
     *
     * Identifies the Domain this Capability belongs to.
     * "A Capability belongs to exactly one Domain" (per WBF-DOC-0004)
     *
     * @var string|int
     */
    protected string|int $domainId = '';

    /**
     * Capability business owner (WBF-DOC-0008 Section 10-11)
     *
     * The responsible Capability Owner for this capability.
     * Can be a user ID, actor ID, or organizational unit identifier.
     *
     * @var string|int
     */
    protected string|int $businessOwner = '';

    /**
     * Capability lifecycle status (WBF-DOC-0008 Section 9)
     *
     * Current state in the capability lifecycle.
     * Must be one of: Identify, Analyze, Design, Review, Approve, Implement, Operate, Improve, Retire
     *
     * @var string
     */
    protected string $status = self::IDENTIFY;

    /**
     * Business outcome (WBF-DOC-0008 Section 10)
     *
     * The measurable business outcome or value delivered by this capability.
     * Examples: 'Quickly locate employee information', 'Enable rapid recruitment process'
     *
     * @var string
     */
    protected string $businessOutcome = '';

    /**
     * Capability KPIs (WBF-DOC-0008 Section 10)
     *
     * Key Performance Indicators for measuring capability effectiveness.
     * Format: [['name' => 'kpi-name', 'target' => value, 'current' => value], ...]
     * Note: Stores KPI definitions only; does NOT implement measurement system per spec.
     *
     * @var array
     */
    protected array $kpis = [];

    /**
     * Capability dependencies (WBF-DOC-0008 Section 10, Section 12)
     *
     * List of capability IDs that this capability depends on.
     * Per WBF-DOC-0008 Section 12: No cyclic dependencies allowed.
     * Communication happens through services and events per spec.
     *
     * @var array<string>
     */
    protected array $dependencies = [];

    /**
     * Contained Workflows (WBF-DOC-0008 Section 10, Section 8)
     *
     * Collection of workflows contained within this capability.
     * Each workflow is represented as an array with minimal reference information.
     * Format: [['id' => 'workflow-id', 'name' => 'workflow-name', ...], ...]
     * Note: Workflow model may not be implemented yet; this stores references.
     *
     * @var array
     */
    protected array $workflows = [];

    /**
     * Exposed Business Services (WBF-DOC-0008 Section 10, Section 8)
     *
     * Collection of business services exposed by this capability.
     * Each service is represented as an array with minimal reference information.
     * Format: [['id' => 'service-id', 'name' => 'service-name', ...], ...]
     * Note: Business Service model may not be implemented yet; this stores references.
     *
     * @var array
     */
    protected array $services = [];

    /**
     * Business Rules (WBF-DOC-0008 Section 6, 7, 14)
     *
     * Business rules that apply to this capability.
     * Stores definitions and references only; does NOT implement rule engine per spec.
     * Format: [['ruleId' => 'rule-id', 'description' => '...'], ...]
     *
     * @var array
     */
    protected array $businessRules = [];

    /**
     * Business Policies (WBF-DOC-0008 Section 6, 7, 13-14)
     *
     * Business policies that apply to this capability.
     * Stores definitions and references only; does NOT implement policy engine per spec.
     * Format: [['policyId' => 'policy-id', 'description' => '...'], ...]
     *
     * @var array
     */
    protected array $policies = [];

    /**
     * Business Events (WBF-DOC-0008 Section 6, 14)
     *
     * Business events published and/or consumed by this capability.
     * Stores event definitions only; does NOT implement event bus per spec.
     * Format: ['published' => [...], 'consumed' => [...]]
     *
     * @var array
     */
    protected array $events = [];

    /**
     * Initialize the Capability
     *
     * Sets up entity type and prepares the capability for use.
     * Must be called during construction after setting capability properties.
     *
     * @return void
     */
    protected function initializeCapability(): void
    {
        $this->setEntityType('Capability');
        $this->validateMandatoryCapabilityFields();
    }

    /**
     * Validate mandatory Capability fields (WBF-DOC-0008 Section 10)
     *
     * Ensures the capability has all required fields set before creation.
     * Required fields: capabilityId, capabilityName, description, domainId, businessOwner, status, businessOutcome
     *
     * @return void
     * @throws \InvalidArgumentException if validation fails
     */
    protected function validateMandatoryCapabilityFields(): void
    {
        if (empty($this->capabilityId)) {
            throw new \InvalidArgumentException('Capability ID is required and cannot be empty');
        }

        if (!is_string($this->capabilityId)) {
            throw new \InvalidArgumentException('Capability ID must be a string');
        }

        if (empty($this->capabilityName)) {
            throw new \InvalidArgumentException('Capability name is required and cannot be empty');
        }

        if (!is_string($this->capabilityName)) {
            throw new \InvalidArgumentException('Capability name must be a string');
        }

        if (!is_string($this->description)) {
            throw new \InvalidArgumentException('Capability description must be a string');
        }

        if (empty($this->domainId)) {
            throw new \InvalidArgumentException('Domain ID is required and cannot be empty');
        }

        if (!is_string($this->domainId) && !is_int($this->domainId)) {
            throw new \InvalidArgumentException('Domain ID must be a string or integer');
        }

        if (empty($this->businessOwner)) {
            throw new \InvalidArgumentException('Business owner is required and cannot be empty');
        }

        if (!is_string($this->businessOwner) && !is_int($this->businessOwner)) {
            throw new \InvalidArgumentException('Business owner must be a string or integer');
        }

        if (empty($this->status)) {
            throw new \InvalidArgumentException('Capability status is required and cannot be empty');
        }

        if (!in_array($this->status, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid capability status "%s". Must be one of: %s',
                    $this->status,
                    implode(', ', self::VALID_STATUSES)
                )
            );
        }

        if (empty($this->businessOutcome)) {
            throw new \InvalidArgumentException('Business outcome is required and cannot be empty');
        }

        if (!is_string($this->businessOutcome)) {
            throw new \InvalidArgumentException('Business outcome must be a string');
        }
    }

    /**
     * Get the capability identifier (WBF-DOC-0008 Section 10)
     *
     * @return string The capability ID
     */
    public function getCapabilityId(): string
    {
        return $this->capabilityId;
    }

    /**
     * Set the capability identifier (WBF-DOC-0008 Section 10)
     *
     * @param string $capabilityId The capability ID
     * @return void
     * @throws \InvalidArgumentException if ID is invalid
     */
    public function setCapabilityId(string $capabilityId): void
    {
        if (empty($capabilityId)) {
            throw new \InvalidArgumentException('Capability ID cannot be empty');
        }

        $this->capabilityId = $capabilityId;
    }

    /**
     * Get the capability name (WBF-DOC-0008 Section 10)
     *
     * @return string The capability name
     */
    public function getCapabilityName(): string
    {
        return $this->capabilityName;
    }

    /**
     * Set the capability name (WBF-DOC-0008 Section 10)
     *
     * @param string $capabilityName The capability name
     * @return void
     * @throws \InvalidArgumentException if name is invalid
     */
    public function setCapabilityName(string $capabilityName): void
    {
        if (empty($capabilityName)) {
            throw new \InvalidArgumentException('Capability name cannot be empty');
        }

        $this->capabilityName = $capabilityName;
    }

    /**
     * Get the capability description (WBF-DOC-0008 Section 10)
     *
     * @return string The capability description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set the capability description (WBF-DOC-0008 Section 10)
     *
     * @param string $description The capability description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Get the parent Domain identifier (WBF-DOC-0008 Section 10, WBF-DOC-0004 Section 5.2)
     *
     * @return string|int The domain ID
     */
    public function getDomainId(): string|int
    {
        return $this->domainId;
    }

    /**
     * Set the parent Domain identifier (WBF-DOC-0008 Section 10, WBF-DOC-0004 Section 5.2)
     *
     * @param string|int $domainId The domain ID
     * @return void
     * @throws \InvalidArgumentException if ID is invalid
     */
    public function setDomainId(string|int $domainId): void
    {
        if (empty($domainId)) {
            throw new \InvalidArgumentException('Domain ID cannot be empty');
        }

        $this->domainId = $domainId;
    }

    /**
     * Get the business owner (WBF-DOC-0008 Section 10-11)
     *
     * @return string|int The business owner identifier
     */
    public function getBusinessOwner(): string|int
    {
        return $this->businessOwner;
    }

    /**
     * Set the business owner (WBF-DOC-0008 Section 10-11)
     *
     * @param string|int $businessOwner The business owner identifier
     * @return void
     * @throws \InvalidArgumentException if owner is invalid
     */
    public function setBusinessOwner(string|int $businessOwner): void
    {
        if (empty($businessOwner)) {
            throw new \InvalidArgumentException('Business owner cannot be empty');
        }

        $this->businessOwner = $businessOwner;
    }

    /**
     * Get the capability lifecycle status (WBF-DOC-0008 Section 9)
     *
     * @return string The current lifecycle status
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set the capability lifecycle status (WBF-DOC-0008 Section 9)
     *
     * @param string $status The new lifecycle status
     * @return void
     * @throws \InvalidArgumentException if status is invalid
     */
    public function setStatus(string $status): void
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid capability status "%s". Must be one of: %s',
                    $status,
                    implode(', ', self::VALID_STATUSES)
                )
            );
        }

        $this->status = $status;
    }

    /**
     * Get the business outcome (WBF-DOC-0008 Section 10)
     *
     * @return string The business outcome
     */
    public function getBusinessOutcome(): string
    {
        return $this->businessOutcome;
    }

    /**
     * Set the business outcome (WBF-DOC-0008 Section 10)
     *
     * @param string $businessOutcome The business outcome
     * @return void
     * @throws \InvalidArgumentException if outcome is invalid
     */
    public function setBusinessOutcome(string $businessOutcome): void
    {
        if (empty($businessOutcome)) {
            throw new \InvalidArgumentException('Business outcome cannot be empty');
        }

        $this->businessOutcome = $businessOutcome;
    }

    /**
     * Get all KPIs (WBF-DOC-0008 Section 10)
     *
     * @return array The list of KPIs
     */
    public function getKpis(): array
    {
        return $this->kpis;
    }

    /**
     * Set all KPIs (WBF-DOC-0008 Section 10)
     *
     * @param array $kpis The list of KPIs
     * @return void
     */
    public function setKpis(array $kpis): void
    {
        $this->kpis = $kpis;
    }

    /**
     * Get all dependencies (WBF-DOC-0008 Section 10, Section 12)
     *
     * @return array<string> The list of dependent capability IDs
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Set all dependencies (WBF-DOC-0008 Section 10, Section 12)
     *
     * Sets the list of capabilities this capability depends on.
     * Note: Cyclic dependency validation is done at governance level per WBF-DOC-0008 Section 12.
     *
     * @param array<string> $dependencies List of capability IDs this capability depends on
     * @return void
     */
    public function setDependencies(array $dependencies): void
    {
        $this->dependencies = $dependencies;
    }

    /**
     * Add a dependency (WBF-DOC-0008 Section 12)
     *
     * @param string $capabilityId The capability ID to add as a dependency
     * @return void
     * @throws \InvalidArgumentException if dependency already exists
     */
    public function addDependency(string $capabilityId): void
    {
        if (in_array($capabilityId, $this->dependencies, true)) {
            throw new \InvalidArgumentException(
                sprintf('Capability dependency "%s" already exists', $capabilityId)
            );
        }

        $this->dependencies[] = $capabilityId;
    }

    /**
     * Remove a dependency (WBF-DOC-0008 Section 12)
     *
     * @param string $capabilityId The capability ID to remove from dependencies
     * @return void
     * @throws \InvalidArgumentException if dependency not found
     */
    public function removeDependency(string $capabilityId): void
    {
        $key = array_search($capabilityId, $this->dependencies, true);

        if ($key === false) {
            throw new \InvalidArgumentException(
                sprintf('Capability dependency "%s" not found', $capabilityId)
            );
        }

        unset($this->dependencies[$key]);
        $this->dependencies = array_values($this->dependencies);
    }

    /**
     * Check if capability has a dependency (WBF-DOC-0008 Section 12)
     *
     * @param string $capabilityId The capability ID to check
     * @return bool True if dependency exists
     */
    public function hasDependency(string $capabilityId): bool
    {
        return in_array($capabilityId, $this->dependencies, true);
    }

    /**
     * Get all workflows (WBF-DOC-0008 Section 10, Section 8)
     *
     * @return array The list of workflows
     */
    public function getWorkflows(): array
    {
        return $this->workflows;
    }

    /**
     * Add a workflow to the capability (WBF-DOC-0008 Section 10)
     *
     * Adds a workflow reference to the capability.
     * Workflow must be an array with at least 'id' key.
     *
     * @param array $workflow The workflow reference ['id' => 'wf-id', 'name' => '...', ...]
     * @return void
     * @throws \InvalidArgumentException if workflow is invalid
     */
    public function addWorkflow(array $workflow): void
    {
        if (empty($workflow['id'])) {
            throw new \InvalidArgumentException('Workflow must have an id field');
        }

        // Prevent duplicate workflows
        foreach ($this->workflows as $existing) {
            if ($existing['id'] === $workflow['id']) {
                throw new \InvalidArgumentException(
                    sprintf('Workflow with id "%s" already exists in this capability', $workflow['id'])
                );
            }
        }

        $this->workflows[] = $workflow;
    }

    /**
     * Remove a workflow from the capability (WBF-DOC-0008 Section 10)
     *
     * @param string $workflowId The workflow ID to remove
     * @return void
     * @throws \InvalidArgumentException if workflow not found
     */
    public function removeWorkflow(string $workflowId): void
    {
        $index = null;
        foreach ($this->workflows as $i => $workflow) {
            if ($workflow['id'] === $workflowId) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            throw new \InvalidArgumentException(
                sprintf('Workflow with id "%s" not found in this capability', $workflowId)
            );
        }

        array_splice($this->workflows, $index, 1);
    }

    /**
     * Get a specific workflow by ID (WBF-DOC-0008 Section 10)
     *
     * @param string $workflowId The workflow ID to retrieve
     * @return array|null The workflow or null if not found
     */
    public function getWorkflow(string $workflowId): ?array
    {
        foreach ($this->workflows as $workflow) {
            if ($workflow['id'] === $workflowId) {
                return $workflow;
            }
        }

        return null;
    }

    /**
     * Check if capability has a specific workflow (WBF-DOC-0008 Section 10)
     *
     * @param string $workflowId The workflow ID to check
     * @return bool True if workflow exists
     */
    public function hasWorkflow(string $workflowId): bool
    {
        return $this->getWorkflow($workflowId) !== null;
    }

    /**
     * Get all services (WBF-DOC-0008 Section 10, Section 8)
     *
     * @return array The list of services
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * Add a service to the capability (WBF-DOC-0008 Section 10)
     *
     * Adds a service reference to the capability.
     * Service must be an array with at least 'id' key.
     *
     * @param array $service The service reference ['id' => 'svc-id', 'name' => '...', ...]
     * @return void
     * @throws \InvalidArgumentException if service is invalid
     */
    public function addService(array $service): void
    {
        if (empty($service['id'])) {
            throw new \InvalidArgumentException('Service must have an id field');
        }

        // Prevent duplicate services
        foreach ($this->services as $existing) {
            if ($existing['id'] === $service['id']) {
                throw new \InvalidArgumentException(
                    sprintf('Service with id "%s" already exists in this capability', $service['id'])
                );
            }
        }

        $this->services[] = $service;
    }

    /**
     * Remove a service from the capability (WBF-DOC-0008 Section 10)
     *
     * @param string $serviceId The service ID to remove
     * @return void
     * @throws \InvalidArgumentException if service not found
     */
    public function removeService(string $serviceId): void
    {
        $index = null;
        foreach ($this->services as $i => $service) {
            if ($service['id'] === $serviceId) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            throw new \InvalidArgumentException(
                sprintf('Service with id "%s" not found in this capability', $serviceId)
            );
        }

        array_splice($this->services, $index, 1);
    }

    /**
     * Get a specific service by ID (WBF-DOC-0008 Section 10)
     *
     * @param string $serviceId The service ID to retrieve
     * @return array|null The service or null if not found
     */
    public function getService(string $serviceId): ?array
    {
        foreach ($this->services as $service) {
            if ($service['id'] === $serviceId) {
                return $service;
            }
        }

        return null;
    }

    /**
     * Check if capability has a specific service (WBF-DOC-0008 Section 10)
     *
     * @param string $serviceId The service ID to check
     * @return bool True if service exists
     */
    public function hasService(string $serviceId): bool
    {
        return $this->getService($serviceId) !== null;
    }

    /**
     * Get business rules (WBF-DOC-0008 Section 6, 7, 14)
     *
     * @return array The list of business rules
     */
    public function getBusinessRules(): array
    {
        return $this->businessRules;
    }

    /**
     * Set business rules (WBF-DOC-0008 Section 6, 7, 14)
     *
     * @param array $businessRules The list of business rules
     * @return void
     */
    public function setBusinessRules(array $businessRules): void
    {
        $this->businessRules = $businessRules;
    }

    /**
     * Get policies (WBF-DOC-0008 Section 6, 7, 13-14)
     *
     * @return array The list of policies
     */
    public function getPolicies(): array
    {
        return $this->policies;
    }

    /**
     * Set policies (WBF-DOC-0008 Section 6, 7, 13-14)
     *
     * @param array $policies The list of policies
     * @return void
     */
    public function setPolicies(array $policies): void
    {
        $this->policies = $policies;
    }

    /**
     * Get events (WBF-DOC-0008 Section 6, 14)
     *
     * @return array The event definitions (published and consumed)
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Set events (WBF-DOC-0008 Section 6, 14)
     *
     * @param array $events The event definitions
     * @return void
     */
    public function setEvents(array $events): void
    {
        $this->events = $events;
    }

    /**
     * Convert the Capability to an array (Framework-independent serialization)
     *
     * Returns all capability properties plus BaseModel properties.
     * Suitable for JSON encoding (no closures, no framework objects).
     *
     * @return array The capability as an array
     */
    abstract public function toArray(): array;

    /**
     * Convert the Capability to JSON (Framework-independent serialization)
     *
     * Returns a JSON-encoded representation of the capability.
     * Does not include framework objects or closures.
     *
     * @return string The capability as JSON
     */
    abstract public function toJson(): string;

    /**
     * Get a string representation of the Capability
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            'Capability(%s | %s v%d)',
            $this->getCapabilityId(),
            $this->getCapabilityName(),
            $this->getEntityVersion()
        );
    }
}
