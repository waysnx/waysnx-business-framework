<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Models;

use WaysNX\BusinessFramework\Core\ModuleAbstract;
use JsonSerializable;

/**
 * Module
 *
 * Laravel-specific implementation of WBF Module model.
 *
 * This class provides:
 * - Framework-independent module model (from ModuleAbstract)
 * - Laravel-specific integration
 * - JSON serialization support for Laravel
 * - Concrete implementation of abstract methods
 *
 * Architecture:
 * - Extends ModuleAbstract (framework-independent)
 * - Implements JsonSerializable for Laravel
 * - Uses Laravel-compatible UUID generation
 * - DateTimeImmutable for timestamps
 * - Remains compatible with the WBF specification (WBF-DOC-0006)
 *
 * Usage:
 * ```php
 * $module = new Module();
 * $module->setModuleId('HR');
 * $module->setModuleName('Human Resources');
 * $module->setDescription('Core HR capabilities');
 * $module->setBusinessOwner('manager-123');
 * $module->setStatus(Module::IMPLEMENT);
 * $module->addDomain(['id' => 'emp-mgmt', 'name' => 'Employee Management']);
 * $module->addDomain(['id' => 'leave-mgmt', 'name' => 'Leave Management']);
 * $module->setDependencies(['core']);
 * $module->setKpis([['name' => 'Employee Satisfaction', 'target' => 85]]);
 *
 * echo $module->toJson();
 * ```
 *
 * @extends ModuleAbstract
 * @implements JsonSerializable
 * @package WaysNX\BusinessFramework\Models
 */
class Module extends ModuleAbstract implements JsonSerializable
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
        return 'module-' . uniqid();
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
     * Called when the module is updated.
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
     * Convert the Module to an array
     *
     * Returns all module properties plus BaseModel properties in array format.
     * Suitable for JSON encoding and framework serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            // Module-specific fields
            'module_id' => $this->getModuleId(),
            'module_name' => $this->getModuleName(),
            'description' => $this->getDescription(),
            'business_owner' => $this->getBusinessOwner(),
            'status' => $this->getStatus(),
            'domains' => $this->getDomains(),
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
     * Convert the Module to JSON
     *
     * Returns a JSON-encoded representation of the module.
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
     * Convert the Module to a JSON-serializable array
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
