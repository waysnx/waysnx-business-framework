<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Core;

/**
 * ModuleAbstract
 *
 * Framework-independent abstract base class for WBF Module model.
 *
 * A Module represents the highest level of functional decomposition in WBF.
 * It groups related Domains that collectively deliver a significant business outcome.
 *
 * This class implements the requirements defined in WBF-DOC-0006 Module Specification:
 * - Module Identity (ID, Name, Description)
 * - Module Lifecycle (Identify, Design, Review, Approve, Implement, Operate, Improve, Retire)
 * - Module Ownership (Business Owner)
 * - Module Metadata (Version, Status, Domains, KPIs, Dependencies, Compliance Requirements)
 * - Module Domain Relationship (Module contains Domains)
 *
 * Architecture:
 * - Extends BaseModelAbstract (framework-independent base)
 * - Implements WBF architectural hierarchy (Application → Module → Domain → Capability → Function)
 * - Provides framework-independent contract
 * - Framework-specific implementations extend this class
 *
 * Usage:
 * ```php
 * class Module extends ModuleAbstract {
 *     // Framework-specific implementation
 * }
 *
 * $module = new Module();
 * $module->setModuleId('HR');
 * $module->setModuleName('Human Resources');
 * $module->setDescription('Core HR capabilities');
 * $module->setBusinessOwner('user-123');
 * $module->setStatus(ModuleAbstract::IMPLEMENT);
 * $module->addDomain(['id' => 'emp-mgmt', 'name' => 'Employee Management']);
 * ```
 *
 * @package WaysNX\BusinessFramework\Core
 */
abstract class ModuleAbstract extends BaseModelAbstract
{
    /**
     * Module Lifecycle States (WBF-DOC-0006 Section 10)
     *
     * Modules transition through these lifecycle states:
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
     * Valid lifecycle states (WBF-DOC-0006 Section 10)
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
     * Module identifier (WBF-DOC-0006 Section 11)
     *
     * Unique identifier for the module within the enterprise.
     * Examples: 'HR', 'FINANCE', 'CRM', 'PROCUREMENT'
     *
     * @var string
     */
    protected string $moduleId = '';

    /**
     * Module name (WBF-DOC-0006 Section 11)
     *
     * Human-readable module name.
     * Examples: 'Human Resources', 'Finance', 'Customer Relationship Management'
     *
     * @var string
     */
    protected string $moduleName = '';

    /**
     * Module description (WBF-DOC-0006 Section 11)
     *
     * Detailed description of the module's business purpose and scope.
     *
     * @var string
     */
    protected string $description = '';

    /**
     * Module business owner (WBF-DOC-0006 Section 11-12)
     *
     * The responsible business owner/stakeholder for this module.
     * Can be a user ID, actor ID, or organizational unit identifier.
     *
     * @var string|int
     */
    protected string|int $businessOwner = '';

    /**
     * Module lifecycle status (WBF-DOC-0006 Section 10)
     *
     * Current state in the module lifecycle.
     * Must be one of: Identify, Design, Review, Approve, Implement, Operate, Improve, Retire
     *
     * @var string
     */
    protected string $status = self::IDENTIFY;

    /**
     * Module domains (WBF-DOC-0006 Section 9, AR-01)
     *
     * Collection of domains contained within this module.
     * Each domain is represented as an array with minimal reference information.
     * Format: [['id' => 'domain-id', 'name' => 'domain-name', ...], ...]
     *
     * @var array
     */
    protected array $domains = [];

    /**
     * Module KPIs (WBF-DOC-0006 Section 11)
     *
     * Key Performance Indicators for measuring module effectiveness.
     * Format: [['name' => 'kpi-name', 'target' => value, 'current' => value], ...]
     *
     * @var array
     */
    protected array $kpis = [];

    /**
     * Module dependencies (WBF-DOC-0006 Section 11, Section 13)
     *
     * List of module IDs that this module depends on.
     * Per WBF-DOC-0006 Section 13: No cyclic dependencies allowed.
     *
     * @var array<string>
     */
    protected array $dependencies = [];

    /**
     * Module compliance requirements (WBF-DOC-0006 Section 11)
     *
     * Governance and compliance requirements for this module.
     * Format: [['requirement' => 'name', 'standard' => 'standard-ref', ...], ...]
     *
     * @var array
     */
    protected array $complianceRequirements = [];

    /**
     * Initialize the Module
     *
     * Sets up entity type and prepares the module for use.
     * Must be called during construction after setting module properties.
     *
     * @return void
     */
    protected function initializeModule(): void
    {
        $this->setEntityType('Module');
        $this->validateMandatoryModuleFields();
    }

    /**
     * Validate mandatory Module fields (WBF-DOC-0006 Section 11)
     *
     * Ensures the module has all required fields set before creation.
     * Required fields: moduleId, moduleName, description, businessOwner, status
     *
     * @return void
     * @throws \InvalidArgumentException if validation fails
     */
    protected function validateMandatoryModuleFields(): void
    {
        if (empty($this->moduleId)) {
            throw new \InvalidArgumentException('Module ID is required and cannot be empty');
        }

        if (!is_string($this->moduleId)) {
            throw new \InvalidArgumentException('Module ID must be a string');
        }

        if (empty($this->moduleName)) {
            throw new \InvalidArgumentException('Module name is required and cannot be empty');
        }

        if (!is_string($this->moduleName)) {
            throw new \InvalidArgumentException('Module name must be a string');
        }

        if (!is_string($this->description)) {
            throw new \InvalidArgumentException('Module description must be a string');
        }

        if (empty($this->businessOwner)) {
            throw new \InvalidArgumentException('Business owner is required and cannot be empty');
        }

        if (!is_string($this->businessOwner) && !is_int($this->businessOwner)) {
            throw new \InvalidArgumentException('Business owner must be a string or integer');
        }

        if (empty($this->status)) {
            throw new \InvalidArgumentException('Module status is required and cannot be empty');
        }

        if (!in_array($this->status, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid module status "%s". Must be one of: %s',
                    $this->status,
                    implode(', ', self::VALID_STATUSES)
                )
            );
        }
    }

    /**
     * Get the module identifier (WBF-DOC-0006 Section 11)
     *
     * @return string The module ID
     */
    public function getModuleId(): string
    {
        return $this->moduleId;
    }

    /**
     * Set the module identifier (WBF-DOC-0006 Section 11)
     *
     * @param string $moduleId The module ID
     * @return void
     * @throws \InvalidArgumentException if ID is invalid
     */
    public function setModuleId(string $moduleId): void
    {
        if (empty($moduleId)) {
            throw new \InvalidArgumentException('Module ID cannot be empty');
        }

        $this->moduleId = $moduleId;
    }

    /**
     * Get the module name (WBF-DOC-0006 Section 11)
     *
     * @return string The module name
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    /**
     * Set the module name (WBF-DOC-0006 Section 11)
     *
     * @param string $moduleName The module name
     * @return void
     * @throws \InvalidArgumentException if name is invalid
     */
    public function setModuleName(string $moduleName): void
    {
        if (empty($moduleName)) {
            throw new \InvalidArgumentException('Module name cannot be empty');
        }

        $this->moduleName = $moduleName;
    }

    /**
     * Get the module description (WBF-DOC-0006 Section 11)
     *
     * @return string The module description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set the module description (WBF-DOC-0006 Section 11)
     *
     * @param string $description The module description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Get the business owner (WBF-DOC-0006 Section 11-12)
     *
     * @return string|int The business owner identifier
     */
    public function getBusinessOwner(): string|int
    {
        return $this->businessOwner;
    }

    /**
     * Set the business owner (WBF-DOC-0006 Section 11-12)
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
     * Get the module lifecycle status (WBF-DOC-0006 Section 10)
     *
     * @return string The current lifecycle status
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set the module lifecycle status (WBF-DOC-0006 Section 10)
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
                    'Invalid module status "%s". Must be one of: %s',
                    $status,
                    implode(', ', self::VALID_STATUSES)
                )
            );
        }

        $this->status = $status;
    }

    /**
     * Get all domains (WBF-DOC-0006 Section 9)
     *
     * @return array The list of domains
     */
    public function getDomains(): array
    {
        return $this->domains;
    }

    /**
     * Add a domain to the module (WBF-DOC-0006 Section 9)
     *
     * Adds a domain reference to the module.
     * Domain must be an array with at least 'id' key.
     *
     * @param array $domain The domain reference ['id' => 'domain-id', 'name' => '...', ...]
     * @return void
     * @throws \InvalidArgumentException if domain is invalid
     */
    public function addDomain(array $domain): void
    {
        if (empty($domain['id'])) {
            throw new \InvalidArgumentException('Domain must have an id field');
        }

        // Prevent duplicate domains
        foreach ($this->domains as $existing) {
            if ($existing['id'] === $domain['id']) {
                throw new \InvalidArgumentException(
                    sprintf('Domain with id "%s" already exists in this module', $domain['id'])
                );
            }
        }

        $this->domains[] = $domain;
    }

    /**
     * Remove a domain from the module (WBF-DOC-0006 Section 9)
     *
     * @param string $domainId The domain ID to remove
     * @return void
     * @throws \InvalidArgumentException if domain not found
     */
    public function removeDomain(string $domainId): void
    {
        $index = null;
        foreach ($this->domains as $i => $domain) {
            if ($domain['id'] === $domainId) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            throw new \InvalidArgumentException(
                sprintf('Domain with id "%s" not found in this module', $domainId)
            );
        }

        array_splice($this->domains, $index, 1);
    }

    /**
     * Get a specific domain by ID (WBF-DOC-0006 Section 9)
     *
     * @param string $domainId The domain ID to retrieve
     * @return array|null The domain or null if not found
     */
    public function getDomain(string $domainId): ?array
    {
        foreach ($this->domains as $domain) {
            if ($domain['id'] === $domainId) {
                return $domain;
            }
        }

        return null;
    }

    /**
     * Check if module has a specific domain (WBF-DOC-0006 Section 9)
     *
     * @param string $domainId The domain ID to check
     * @return bool True if domain exists
     */
    public function hasDomain(string $domainId): bool
    {
        return $this->getDomain($domainId) !== null;
    }

    /**
     * Get module KPIs (WBF-DOC-0006 Section 11)
     *
     * @return array The list of KPIs
     */
    public function getKpis(): array
    {
        return $this->kpis;
    }

    /**
     * Set module KPIs (WBF-DOC-0006 Section 11)
     *
     * @param array $kpis The list of KPIs
     * @return void
     */
    public function setKpis(array $kpis): void
    {
        $this->kpis = $kpis;
    }

    /**
     * Get module dependencies (WBF-DOC-0006 Section 11, Section 13)
     *
     * @return array<string> The list of dependent module IDs
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Set module dependencies (WBF-DOC-0006 Section 11, Section 13)
     *
     * Sets the list of modules this module depends on.
     * Note: Cyclic dependency validation is done at governance level per WBF-DOC-0006 Section 13.
     *
     * @param array<string> $dependencies List of module IDs this module depends on
     * @return void
     */
    public function setDependencies(array $dependencies): void
    {
        $this->dependencies = $dependencies;
    }

    /**
     * Add a dependency (WBF-DOC-0006 Section 13)
     *
     * @param string $moduleId The module ID to add as a dependency
     * @return void
     * @throws \InvalidArgumentException if dependency already exists
     */
    public function addDependency(string $moduleId): void
    {
        if (in_array($moduleId, $this->dependencies, true)) {
            throw new \InvalidArgumentException(
                sprintf('Module dependency "%s" already exists', $moduleId)
            );
        }

        $this->dependencies[] = $moduleId;
    }

    /**
     * Remove a dependency (WBF-DOC-0006 Section 13)
     *
     * @param string $moduleId The module ID to remove from dependencies
     * @return void
     * @throws \InvalidArgumentException if dependency not found
     */
    public function removeDependency(string $moduleId): void
    {
        $key = array_search($moduleId, $this->dependencies, true);

        if ($key === false) {
            throw new \InvalidArgumentException(
                sprintf('Module dependency "%s" not found', $moduleId)
            );
        }

        unset($this->dependencies[$key]);
        $this->dependencies = array_values($this->dependencies);
    }

    /**
     * Check if module has a dependency (WBF-DOC-0006 Section 13)
     *
     * @param string $moduleId The module ID to check
     * @return bool True if dependency exists
     */
    public function hasDependency(string $moduleId): bool
    {
        return in_array($moduleId, $this->dependencies, true);
    }

    /**
     * Get compliance requirements (WBF-DOC-0006 Section 11)
     *
     * @return array The list of compliance requirements
     */
    public function getComplianceRequirements(): array
    {
        return $this->complianceRequirements;
    }

    /**
     * Set compliance requirements (WBF-DOC-0006 Section 11)
     *
     * @param array $requirements The list of compliance requirements
     * @return void
     */
    public function setComplianceRequirements(array $requirements): void
    {
        $this->complianceRequirements = $requirements;
    }

    /**
     * Convert the Module to an array (Framework-independent serialization)
     *
     * Returns all module properties plus BaseModel properties.
     * Suitable for JSON encoding (no closures, no framework objects).
     *
     * @return array The module as an array
     */
    abstract public function toArray(): array;

    /**
     * Convert the Module to JSON (Framework-independent serialization)
     *
     * Returns a JSON-encoded representation of the module.
     * Does not include framework objects or closures.
     *
     * @return string The module as JSON
     */
    abstract public function toJson(): string;

    /**
     * Get a string representation of the Module
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            'Module(%s | %s v%d)',
            $this->getModuleId(),
            $this->getModuleName(),
            $this->getEntityVersion()
        );
    }
}
