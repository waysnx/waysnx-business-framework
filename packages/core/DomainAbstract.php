<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Core;

/**
 * DomainAbstract
 *
 * Framework-independent abstract base class for WBF Domain model.
 *
 * A Domain is a logical subdivision of a Module that groups closely related
 * Business Capabilities required to achieve a specific business objective.
 *
 * This class implements the requirements defined in WBF-DOC-0007 Domain Specification:
 * - Domain Identity (ID, Name, Description)
 * - Domain Lifecycle (Identify, Design, Review, Approve, Implement, Operate, Improve, Retire)
 * - Domain Ownership (Business Owner)
 * - Domain Metadata (Version, Status, Capabilities, KPIs, Dependencies, Compliance)
 * - Domain Module Relationship (Domain belongs to exactly one Module)
 * - Domain Capability Relationship (Domain contains Business Capabilities)
 *
 * Architecture:
 * - Extends BaseModelAbstract (framework-independent base)
 * - Implements WBF architectural hierarchy (Module → Domain → Capability → Function)
 * - Provides framework-independent contract
 * - Framework-specific implementations extend this class
 *
 * Usage:
 * ```php
 * class Domain extends DomainAbstract {
 *     // Framework-specific implementation
 * }
 *
 * $domain = new Domain();
 * $domain->setDomainId('EMP_MGMT');
 * $domain->setDomainName('Employee Management');
 * $domain->setDescription('Core employee management capabilities');
 * $domain->setModuleId('HR');
 * $domain->setBusinessOwner('hr-manager-001');
 * $domain->setStatus(DomainAbstract::IMPLEMENT);
 * $domain->addCapability(['id' => 'emp-search', 'name' => 'Employee Search']);
 * ```
 *
 * @package WaysNX\BusinessFramework\Core
 */
abstract class DomainAbstract extends BaseModelAbstract
{
    /**
     * Domain Lifecycle States (WBF-DOC-0007 Section 9)
     *
     * Domains transition through these lifecycle states (same as Module):
     * Identify → Design → Review → Approve → Implement → Operate → Improve → Retire
     */
    public const IDENTIFY = 'Identify';
    public const DESIGN = 'Design';
    public const REVIEW = 'Review';
    public const APPROVE = 'Approve';
    public const IMPLEMENT = 'Implement';
    public const OPERATE = 'Operate';
    public const IMPROVE = 'Improve';
    public const RETIRE = 'Retire';

    /**
     * Valid lifecycle states (WBF-DOC-0007 Section 9)
     *
     * @var array<string>
     */
    protected const VALID_STATUSES = [
        self::IDENTIFY,
        self::DESIGN,
        self::REVIEW,
        self::APPROVE,
        self::IMPLEMENT,
        self::OPERATE,
        self::IMPROVE,
        self::RETIRE,
    ];

    /**
     * Domain identifier (WBF-DOC-0007 Section 10)
     *
     * Unique identifier for the domain within the module.
     * Examples: 'EMP_MGMT', 'LEAVE_MGT', 'RECRUITMENT'
     *
     * @var string
     */
    protected string $domainId = '';

    /**
     * Domain name (WBF-DOC-0007 Section 10)
     *
     * Human-readable domain name.
     * Examples: 'Employee Management', 'Leave Management', 'Recruitment'
     *
     * @var string
     */
    protected string $domainName = '';

    /**
     * Domain description (WBF-DOC-0007 Section 10)
     *
     * Detailed description of the domain's business purpose and scope.
     *
     * @var string
     */
    protected string $description = '';

    /**
     * Parent Module identifier (WBF-DOC-0007 Section 10, WBF-DOC-0004 Section 5.2)
     *
     * Identifies the Module this Domain belongs to.
     * "A Domain SHALL belong to exactly one Module" (WBF-DOC-0004)
     *
     * @var string|int
     */
    protected string|int $moduleId = '';

    /**
     * Domain business owner (WBF-DOC-0007 Section 10-11)
     *
     * The responsible Domain Owner for this domain.
     * Can be a user ID, actor ID, or organizational unit identifier.
     *
     * @var string|int
     */
    protected string|int $businessOwner = '';

    /**
     * Domain lifecycle status (WBF-DOC-0007 Section 9)
     *
     * Current state in the domain lifecycle.
     * Must be one of: Identify, Design, Review, Approve, Implement, Operate, Improve, Retire
     *
     * @var string
     */
    protected string $status = self::IDENTIFY;

    /**
     * Domain capabilities (WBF-DOC-0007 Section 10, WBF-DOC-0004 Section 5.2)
     *
     * Collection of business capabilities contained within this domain.
     * Each capability is represented as an array with minimal reference information.
     * Format: [['id' => 'capability-id', 'name' => 'capability-name', ...], ...]
     *
     * @var array
     */
    protected array $capabilities = [];

    /**
     * Domain KPIs (WBF-DOC-0007 Section 10)
     *
     * Key Performance Indicators for measuring domain effectiveness.
     * Format: [['name' => 'kpi-name', 'target' => value, 'current' => value], ...]
     *
     * @var array
     */
    protected array $kpis = [];

    /**
     * Domain dependencies (WBF-DOC-0007 Section 10, Section 12)
     *
     * List of domain IDs that this domain depends on.
     * Per WBF-DOC-0007 Section 12: No cyclic dependencies allowed.
     *
     * @var array<string>
     */
    protected array $dependencies = [];

    /**
     * Domain compliance requirements (WBF-DOC-0007 Section 13)
     *
     * Governance and compliance requirements for this domain.
     * Format: [['requirement' => 'name', 'standard' => 'standard-ref', ...], ...]
     *
     * @var array
     */
    protected array $complianceRequirements = [];

    /**
     * Initialize the Domain
     *
     * Sets up entity type and prepares the domain for use.
     * Must be called during construction after setting domain properties.
     *
     * @return void
     */
    protected function initializeDomain(): void
    {
        $this->setEntityType('Domain');
        $this->validateMandatoryDomainFields();
    }

    /**
     * Validate mandatory Domain fields (WBF-DOC-0007 Section 10)
     *
     * Ensures the domain has all required fields set before creation.
     * Required fields: domainId, domainName, description, moduleId, businessOwner, status
     *
     * @return void
     * @throws \InvalidArgumentException if validation fails
     */
    protected function validateMandatoryDomainFields(): void
    {
        if (empty($this->domainId)) {
            throw new \InvalidArgumentException('Domain ID is required and cannot be empty');
        }

        if (!is_string($this->domainId)) {
            throw new \InvalidArgumentException('Domain ID must be a string');
        }

        if (empty($this->domainName)) {
            throw new \InvalidArgumentException('Domain name is required and cannot be empty');
        }

        if (!is_string($this->domainName)) {
            throw new \InvalidArgumentException('Domain name must be a string');
        }

        if (!is_string($this->description)) {
            throw new \InvalidArgumentException('Domain description must be a string');
        }

        if (empty($this->moduleId)) {
            throw new \InvalidArgumentException('Module ID is required and cannot be empty');
        }

        if (!is_string($this->moduleId) && !is_int($this->moduleId)) {
            throw new \InvalidArgumentException('Module ID must be a string or integer');
        }

        if (empty($this->businessOwner)) {
            throw new \InvalidArgumentException('Business owner is required and cannot be empty');
        }

        if (!is_string($this->businessOwner) && !is_int($this->businessOwner)) {
            throw new \InvalidArgumentException('Business owner must be a string or integer');
        }

        if (empty($this->status)) {
            throw new \InvalidArgumentException('Domain status is required and cannot be empty');
        }

        if (!in_array($this->status, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid domain status "%s". Must be one of: %s',
                    $this->status,
                    implode(', ', self::VALID_STATUSES)
                )
            );
        }
    }

    /**
     * Get the domain identifier (WBF-DOC-0007 Section 10)
     *
     * @return string The domain ID
     */
    public function getDomainId(): string
    {
        return $this->domainId;
    }

    /**
     * Set the domain identifier (WBF-DOC-0007 Section 10)
     *
     * @param string $domainId The domain ID
     * @return void
     * @throws \InvalidArgumentException if ID is invalid
     */
    public function setDomainId(string $domainId): void
    {
        if (empty($domainId)) {
            throw new \InvalidArgumentException('Domain ID cannot be empty');
        }

        $this->domainId = $domainId;
    }

    /**
     * Get the domain name (WBF-DOC-0007 Section 10)
     *
     * @return string The domain name
     */
    public function getDomainName(): string
    {
        return $this->domainName;
    }

    /**
     * Set the domain name (WBF-DOC-0007 Section 10)
     *
     * @param string $domainName The domain name
     * @return void
     * @throws \InvalidArgumentException if name is invalid
     */
    public function setDomainName(string $domainName): void
    {
        if (empty($domainName)) {
            throw new \InvalidArgumentException('Domain name cannot be empty');
        }

        $this->domainName = $domainName;
    }

    /**
     * Get the domain description (WBF-DOC-0007 Section 10)
     *
     * @return string The domain description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set the domain description (WBF-DOC-0007 Section 10)
     *
     * @param string $description The domain description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Get the parent Module identifier (WBF-DOC-0007 Section 10, WBF-DOC-0004 Section 5.2)
     *
     * @return string|int The module ID
     */
    public function getModuleId(): string|int
    {
        return $this->moduleId;
    }

    /**
     * Set the parent Module identifier (WBF-DOC-0007 Section 10, WBF-DOC-0004 Section 5.2)
     *
     * @param string|int $moduleId The module ID
     * @return void
     * @throws \InvalidArgumentException if ID is invalid
     */
    public function setModuleId(string|int $moduleId): void
    {
        if (empty($moduleId)) {
            throw new \InvalidArgumentException('Module ID cannot be empty');
        }

        $this->moduleId = $moduleId;
    }

    /**
     * Get the business owner (WBF-DOC-0007 Section 10-11)
     *
     * @return string|int The business owner identifier
     */
    public function getBusinessOwner(): string|int
    {
        return $this->businessOwner;
    }

    /**
     * Set the business owner (WBF-DOC-0007 Section 10-11)
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
     * Get the domain lifecycle status (WBF-DOC-0007 Section 9)
     *
     * @return string The current lifecycle status
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set the domain lifecycle status (WBF-DOC-0007 Section 9)
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
                    'Invalid domain status "%s". Must be one of: %s',
                    $status,
                    implode(', ', self::VALID_STATUSES)
                )
            );
        }

        $this->status = $status;
    }

    /**
     * Get all capabilities (WBF-DOC-0007 Section 10, WBF-DOC-0004 Section 5.2)
     *
     * @return array The list of capabilities
     */
    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    /**
     * Add a capability to the domain (WBF-DOC-0007 Section 10)
     *
     * Adds a capability reference to the domain.
     * Capability must be an array with at least 'id' key.
     *
     * @param array $capability The capability reference ['id' => 'cap-id', 'name' => '...', ...]
     * @return void
     * @throws \InvalidArgumentException if capability is invalid
     */
    public function addCapability(array $capability): void
    {
        if (empty($capability['id'])) {
            throw new \InvalidArgumentException('Capability must have an id field');
        }

        // Prevent duplicate capabilities
        foreach ($this->capabilities as $existing) {
            if ($existing['id'] === $capability['id']) {
                throw new \InvalidArgumentException(
                    sprintf('Capability with id "%s" already exists in this domain', $capability['id'])
                );
            }
        }

        $this->capabilities[] = $capability;
    }

    /**
     * Remove a capability from the domain (WBF-DOC-0007 Section 10)
     *
     * @param string $capabilityId The capability ID to remove
     * @return void
     * @throws \InvalidArgumentException if capability not found
     */
    public function removeCapability(string $capabilityId): void
    {
        $index = null;
        foreach ($this->capabilities as $i => $capability) {
            if ($capability['id'] === $capabilityId) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            throw new \InvalidArgumentException(
                sprintf('Capability with id "%s" not found in this domain', $capabilityId)
            );
        }

        array_splice($this->capabilities, $index, 1);
    }

    /**
     * Get a specific capability by ID (WBF-DOC-0007 Section 10)
     *
     * @param string $capabilityId The capability ID to retrieve
     * @return array|null The capability or null if not found
     */
    public function getCapability(string $capabilityId): ?array
    {
        foreach ($this->capabilities as $capability) {
            if ($capability['id'] === $capabilityId) {
                return $capability;
            }
        }

        return null;
    }

    /**
     * Check if domain has a specific capability (WBF-DOC-0007 Section 10)
     *
     * @param string $capabilityId The capability ID to check
     * @return bool True if capability exists
     */
    public function hasCapability(string $capabilityId): bool
    {
        return $this->getCapability($capabilityId) !== null;
    }

    /**
     * Get domain KPIs (WBF-DOC-0007 Section 10)
     *
     * @return array The list of KPIs
     */
    public function getKpis(): array
    {
        return $this->kpis;
    }

    /**
     * Set domain KPIs (WBF-DOC-0007 Section 10)
     *
     * @param array $kpis The list of KPIs
     * @return void
     */
    public function setKpis(array $kpis): void
    {
        $this->kpis = $kpis;
    }

    /**
     * Get domain dependencies (WBF-DOC-0007 Section 10, Section 12)
     *
     * @return array<string> The list of dependent domain IDs
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Set domain dependencies (WBF-DOC-0007 Section 10, Section 12)
     *
     * Sets the list of domains this domain depends on.
     * Note: Cyclic dependency validation is done at governance level per WBF-DOC-0007 Section 12.
     *
     * @param array<string> $dependencies List of domain IDs this domain depends on
     * @return void
     */
    public function setDependencies(array $dependencies): void
    {
        $this->dependencies = $dependencies;
    }

    /**
     * Add a dependency (WBF-DOC-0007 Section 12)
     *
     * @param string $domainId The domain ID to add as a dependency
     * @return void
     * @throws \InvalidArgumentException if dependency already exists
     */
    public function addDependency(string $domainId): void
    {
        if (in_array($domainId, $this->dependencies, true)) {
            throw new \InvalidArgumentException(
                sprintf('Domain dependency "%s" already exists', $domainId)
            );
        }

        $this->dependencies[] = $domainId;
    }

    /**
     * Remove a dependency (WBF-DOC-0007 Section 12)
     *
     * @param string $domainId The domain ID to remove from dependencies
     * @return void
     * @throws \InvalidArgumentException if dependency not found
     */
    public function removeDependency(string $domainId): void
    {
        $key = array_search($domainId, $this->dependencies, true);

        if ($key === false) {
            throw new \InvalidArgumentException(
                sprintf('Domain dependency "%s" not found', $domainId)
            );
        }

        unset($this->dependencies[$key]);
        $this->dependencies = array_values($this->dependencies);
    }

    /**
     * Check if domain has a dependency (WBF-DOC-0007 Section 12)
     *
     * @param string $domainId The domain ID to check
     * @return bool True if dependency exists
     */
    public function hasDependency(string $domainId): bool
    {
        return in_array($domainId, $this->dependencies, true);
    }

    /**
     * Get compliance requirements (WBF-DOC-0007 Section 13)
     *
     * @return array The list of compliance requirements
     */
    public function getComplianceRequirements(): array
    {
        return $this->complianceRequirements;
    }

    /**
     * Set compliance requirements (WBF-DOC-0007 Section 13)
     *
     * @param array $requirements The list of compliance requirements
     * @return void
     */
    public function setComplianceRequirements(array $requirements): void
    {
        $this->complianceRequirements = $requirements;
    }

    /**
     * Convert the Domain to an array (Framework-independent serialization)
     *
     * Returns all domain properties plus BaseModel properties.
     * Suitable for JSON encoding (no closures, no framework objects).
     *
     * @return array The domain as an array
     */
    abstract public function toArray(): array;

    /**
     * Convert the Domain to JSON (Framework-independent serialization)
     *
     * Returns a JSON-encoded representation of the domain.
     * Does not include framework objects or closures.
     *
     * @return string The domain as JSON
     */
    abstract public function toJson(): string;

    /**
     * Get a string representation of the Domain
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            'Domain(%s | %s v%d)',
            $this->getDomainId(),
            $this->getDomainName(),
            $this->getEntityVersion()
        );
    }
}
