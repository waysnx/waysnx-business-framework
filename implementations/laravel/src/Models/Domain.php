<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Models;

use WaysNX\BusinessFramework\Core\DomainAbstract;
use JsonSerializable;

/**
 * Domain
 *
 * Laravel-specific implementation of WBF Domain model.
 *
 * This class provides:
 * - Framework-independent domain model (from DomainAbstract)
 * - Laravel-specific integration
 * - JSON serialization support for Laravel
 * - Concrete implementation of abstract methods
 *
 * Architecture:
 * - Extends DomainAbstract (framework-independent)
 * - Implements JsonSerializable for Laravel
 * - Uses Laravel-compatible UUID generation
 * - DateTimeImmutable for timestamps
 * - Remains compatible with the WBF specification (WBF-DOC-0007)
 *
 * Usage:
 * ```php
 * $domain = new Domain();
 * $domain->setDomainId('EMP_MGMT');
 * $domain->setDomainName('Employee Management');
 * $domain->setDescription('Core employee management capabilities');
 * $domain->setModuleId('HR');
 * $domain->setBusinessOwner('hr-manager-123');
 * $domain->setStatus(Domain::IMPLEMENT);
 * $domain->addCapability(['id' => 'emp-search', 'name' => 'Employee Search']);
 * $domain->addCapability(['id' => 'emp-profile', 'name' => 'Employee Profile']);
 * $domain->setDependencies(['payroll']);
 * $domain->setKpis([['name' => 'Efficiency', 'target' => 95]]);
 *
 * echo $domain->toJson();
 * ```
 *
 * @extends DomainAbstract
 * @implements JsonSerializable
 * @package WaysNX\BusinessFramework\Models
 */
class Domain extends DomainAbstract implements JsonSerializable
{
    /**
     * Generate a unique UUID for the entity
     *
     * Uses a Laravel-compatible UUID generation method.
     * In production, use Ramsey\Uuid or Laravel\Framework UUID helpers.
     *
     * @return string
     */
    protected function generateUuid(): string
    {
        // Placeholder for testing - in real Laravel app, use proper UUID generation
        return 'domain-' . uniqid();
    }

    /**
     * Initialize audit timestamps
     *
     * Sets created, updated, and deleted timestamps to DateTimeImmutable.
     *
     * @return void
     */
    protected function initializeAuditTimestamps(): void
    {
        $now = new \DateTimeImmutable('now');
        $this->createdAt = $now;
        $this->updatedAt = null;
        $this->deletedAt = null;
    }

    /**
     * Update a specific timestamp
     *
     * Called when the domain is updated.
     * Sets the updatedAt timestamp to current time.
     *
     * @return void
     */
    protected function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable('now');
    }

    /**
     * Delete timestamp
     *
     * Sets the deletion timestamp for soft delete.
     *
     * @return void
     */
    protected function deleteTimestamp(): void
    {
        $this->deletedAt = new \DateTimeImmutable('now');
    }

    /**
     * Convert the Domain to an array
     *
     * Returns all domain properties plus BaseModel properties in array format.
     * Suitable for JSON encoding and framework serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            // Domain-specific fields
            'domain_id' => $this->getDomainId(),
            'domain_name' => $this->getDomainName(),
            'description' => $this->getDescription(),
            'module_id' => $this->getModuleId(),
            'business_owner' => $this->getBusinessOwner(),
            'status' => $this->getStatus(),
            'capabilities' => $this->getCapabilities(),
            'kpis' => $this->getKpis(),
            'dependencies' => $this->getDependencies(),
            'compliance_requirements' => $this->getComplianceRequirements(),

            // BaseModel fields (inherited)
            'entity_id' => $this->getEntityId(),
            'entity_type' => $this->getEntityType(),
            'entity_version' => $this->getEntityVersion(),
            'created_by' => $this->getCreatedBy(),
            'updated_by' => $this->getUpdatedBy(),
            'deleted_by' => $this->getDeletedBy(),
            'created_at' => $this->getCreatedAt() ? $this->getCreatedAt()->format('c') : null,
            'updated_at' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('c') : null,
            'deleted_at' => $this->getDeletedAt() ? $this->getDeletedAt()->format('c') : null,
            'metadata' => $this->getMetadata(),
        ];
    }

    /**
     * Convert the Domain to JSON
     *
     * Returns a JSON-encoded representation of the domain.
     * Uses JSON_UNESCAPED_UNICODE for better readability.
     *
     * @param int $options JSON encoding options
     * @return string
     * @throws \JsonException
     */
    public function toJson(int $options = 0): string
    {
        $defaultOptions = \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR;
        return json_encode($this->jsonSerialize(), $defaultOptions | $options);
    }

    /**
     * Convert the Domain to a JSON-serializable array
     *
     * Enables direct JSON serialization in Laravel contexts.
     * Implements JsonSerializable interface.
     *
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

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
