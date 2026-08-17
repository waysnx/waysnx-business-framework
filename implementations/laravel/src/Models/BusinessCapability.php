<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Models;

use WaysNX\BusinessFramework\Core\BusinessCapabilityAbstract;
use JsonSerializable;

/**
 * BusinessCapability
 *
 * Laravel-specific implementation of WBF Business Capability model.
 *
 * This class provides:
 * - Framework-independent capability model (from BusinessCapabilityAbstract)
 * - Laravel-specific integration
 * - JSON serialization support for Laravel
 * - Concrete implementation of abstract methods
 *
 * Architecture:
 * - Extends BusinessCapabilityAbstract (framework-independent)
 * - Implements JsonSerializable for Laravel
 * - Uses Laravel-compatible UUID generation
 * - DateTimeImmutable for timestamps
 * - Remains compatible with the WBF specification (WBF-DOC-0008)
 *
 * Usage:
 * ```php
 * $capability = new BusinessCapability();
 * $capability->setCapabilityId('emp-search');
 * $capability->setCapabilityName('Employee Search');
 * $capability->setDescription('Search and filter employee records');
 * $capability->setDomainId('EMP_MGMT');
 * $capability->setBusinessOwner('hr-manager-123');
 * $capability->setStatus(BusinessCapability::IMPLEMENT);
 * $capability->setBusinessOutcome('Quickly locate employee information');
 * $capability->addWorkflow(['id' => 'wf-search', 'name' => 'Search Workflow']);
 * $capability->addService(['id' => 'svc-search', 'name' => 'Employee Search Service']);
 * $capability->setKpis([['name' => 'Search Efficiency', 'target' => 95]]);
 * $capability->addDependency('emp-profile');
 *
 * echo $capability->toJson();
 * ```
 *
 * @extends BusinessCapabilityAbstract
 * @implements JsonSerializable
 * @package WaysNX\BusinessFramework\Models
 */
class BusinessCapability extends BusinessCapabilityAbstract implements JsonSerializable
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
        return 'capability-' . uniqid();
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
     * Called when the capability is updated.
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
     * Convert the Capability to an array
     *
     * Returns all capability properties plus BaseModel properties in array format.
     * Suitable for JSON encoding and framework serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            // Capability-specific fields
            'capability_id' => $this->getCapabilityId(),
            'capability_name' => $this->getCapabilityName(),
            'description' => $this->getDescription(),
            'domain_id' => $this->getDomainId(),
            'business_owner' => $this->getBusinessOwner(),
            'status' => $this->getStatus(),
            'business_outcome' => $this->getBusinessOutcome(),
            'kpis' => $this->getKpis(),
            'dependencies' => $this->getDependencies(),
            'workflows' => $this->getWorkflows(),
            'services' => $this->getServices(),
            'business_rules' => $this->getBusinessRules(),
            'policies' => $this->getPolicies(),
            'events' => $this->getEvents(),

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
     * Convert the Capability to JSON
     *
     * Returns a JSON-encoded representation of the capability.
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
     * Convert the Capability to a JSON-serializable array
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
