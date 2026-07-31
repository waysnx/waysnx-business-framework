<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Models;

use JsonSerializable;
use WaysNX\BusinessFramework\Contracts\AuditableInterface;
use WaysNX\BusinessFramework\Contracts\EntityInterface;
use WaysNX\BusinessFramework\Contracts\MetadataInterface;
use WaysNX\BusinessFramework\Traits\HasAudit;
use WaysNX\BusinessFramework\Traits\HasIdentifiers;
use WaysNX\BusinessFramework\Traits\HasLifecycleHooks;
use WaysNX\BusinessFramework\Traits\HasMetadata;
use WaysNX\BusinessFramework\Traits\HasVersioning;

/**
 * BaseModel
 *
 * Foundation class for all WaysNX Business Framework entities.
 *
 * BaseModel provides a common foundation for all business entities in the WBF.
 * Every future business entity should inherit from this class to ensure consistent
 * behavior, identification, and lifecycle management.
 *
 * Purpose:
 * Serve as the parent class for all WBF entities including Project, Requirement,
 * Module, BusinessFunction, Workflow, Screen, API, Task, Document, Rule, and more.
 *
 * Responsibilities:
 * - Provide unique entity identification (UUID support)
 * - Track entity type and version
 * - Manage audit information (created_by, updated_by, deleted_by)
 * - Support standard timestamps (created_at, updated_at, deleted_at)
 * - Store flexible metadata
 * - Support soft deletes
 * - Provide lifecycle extension points for child classes
 * - Provide event hook methods for future event dispatching
 * - Support serialization (toArray, toJson)
 *
 * Features:
 * - Strict typing throughout
 * - Composable via traits
 * - Extensible via protected lifecycle hooks
 * - No business-specific logic
 * - Production-ready architecture
 * - PSR-12 compliant
 * - Suitable for open-source release
 *
 * Usage:
 * ```php
 * class Project extends BaseModel {
 *     protected function validate(): void {
 *         // Implement project-specific validation
 *     }
 *
 *     protected function beforeCreate(): void {
 *         // Custom logic before project creation
 *     }
 * }
 *
 * $project = new Project();
 * $project->setCreatedBy('user-123');
 * $project->setMetadataValue('department', 'Engineering');
 * ```
 *
 * @implements EntityInterface
 * @implements AuditableInterface
 * @implements MetadataInterface
 * @package WaysNX\BusinessFramework\Models
 */
abstract class BaseModel implements EntityInterface, AuditableInterface, MetadataInterface, JsonSerializable
{
    use HasIdentifiers;
    use HasVersioning;
    use HasMetadata;
    use HasAudit;
    use HasLifecycleHooks;

    /**
     * Initialize a new BaseModel instance
     *
     * Initializes all base entity properties including identifiers, versioning,
     * audit information, and metadata. Sets the entity type based on the class name.
     *
     * @return void
     */
    public function __construct()
    {
        // Initialize identifiers
        $this->setEntityId($this->generateUuid()->toString());
        $this->setEntityType(static::class);

        // Initialize versioning
        $this->setEntityVersion(1);

        // Initialize audit trail
        $this->initializeAuditTimestamps();

        // Initialize metadata (empty by default)
        $this->clearMetadata();
    }

    /**
     * Convert the entity to an array representation
     *
     * Converts the entity and all its properties into a simple associative array.
     * Includes identifiers, versioning, audit information, and metadata.
     *
     * @return array The entity as an associative array
     */
    public function toArray(): array
    {
        return [
            'entity_id' => $this->getEntityId(),
            'entity_type' => $this->getEntityType(),
            'entity_version' => $this->getEntityVersion(),
            'created_by' => $this->getCreatedBy(),
            'created_at' => $this->getCreatedAt()->toDateTimeImmutable(),
            'updated_by' => $this->getUpdatedBy(),
            'updated_at' => $this->getUpdatedAt()?->toDateTimeImmutable(),
            'deleted_by' => $this->getDeletedBy(),
            'deleted_at' => $this->getDeletedAt()?->toDateTimeImmutable(),
            'is_deleted' => $this->isDeleted(),
            'metadata' => $this->getMetadata(),
        ];
    }

    /**
     * Convert the entity to JSON representation
     *
     * Converts the entity to a JSON string using the array representation.
     * Uses JSON_UNESCAPED_UNICODE flag for better readability.
     *
     * @return string The entity as a JSON string
     */
    public function toJson(): string
    {
        return (string) json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * Implements JsonSerializable interface for automatic JSON serialization.
     *
     * @return array The data to be serialized
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Get a human-readable representation of the entity
     *
     * Useful for debugging and logging. Shows entity ID, type, version, and deletion state.
     *
     * @return string A string representation of the entity
     */
    public function __toString(): string
    {
        $deleted = $this->isDeleted() ? ' (DELETED)' : '';
        return sprintf(
            '%s#%s v%d%s',
            $this->getEntityType(),
            substr((string) $this->getEntityId(), 0, 8),
            $this->getEntityVersion(),
            $deleted
        );
    }
}
