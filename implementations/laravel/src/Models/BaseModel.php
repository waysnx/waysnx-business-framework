<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Models;

use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use DateTimeImmutable;
use WaysNX\BusinessFramework\Core\BaseModelAbstract;
use WaysNX\BusinessFramework\Contracts\AuditableInterface;
use WaysNX\BusinessFramework\Contracts\EntityInterface;
use WaysNX\BusinessFramework\Contracts\MetadataInterface;

/**
 * BaseModel
 *
 * Laravel-specific foundation class for all WaysNX Business Framework entities.
 *
 * BaseModel extends the framework-independent BaseModelAbstract and provides
 * Laravel-specific implementations of abstract methods, including UUID generation
 * and DateTimeImmutable timestamp handling.
 *
 * Purpose:
 * Serve as the parent class for all business entities in the WBF Laravel implementation,
 * ensuring consistent behavior, identification, and lifecycle management.
 *
 * Architecture:
 * - Extends: WaysNX\BusinessFramework\Core\BaseModelAbstract (framework-independent)
 * - Implements: EntityInterface, AuditableInterface, MetadataInterface (Laravel contracts)
 * - Uses: Ramsey\Uuid for UUID generation
 * - Uses: DateTimeImmutable for timestamp handling
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
 * - Composable architecture
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
class BaseModel extends BaseModelAbstract implements EntityInterface, AuditableInterface, MetadataInterface, JsonSerializable
{
    /**
     * Generate a new UUID identifier
     *

     * Uses Ramsey\Uuid library for UUID v4 generation.
     *

     * @return string The UUID as a string
     */
    protected function generateUuid(): string|int
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Initialize audit timestamps
     *

     * Sets the createdAt timestamp to current time using DateTimeImmutable.
     *

     * @return void
     */
    protected function initializeAuditTimestamps(): void
    {
        $this->createdAt = new DateTimeImmutable();
    }

    /**
     * Update the last modification timestamp
     *

     * Called when an entity is updated. Sets updatedAt to current time.
     *

     * @return void
     */
    protected function updateTimestamp(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Update the deletion timestamp
     *

     * Called when an entity is soft deleted. Sets deletedAt to current time.
     *

     * @return void
     */
    protected function deleteTimestamp(): void
    {
        $this->deletedAt = new DateTimeImmutable();
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
            'created_at' => $this->getCreatedAt(),
            'updated_by' => $this->getUpdatedBy(),
            'updated_at' => $this->getUpdatedAt(),
            'deleted_by' => $this->getDeletedBy(),
            'deleted_at' => $this->getDeletedAt(),
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
