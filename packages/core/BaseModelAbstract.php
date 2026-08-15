<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Core;

use WaysNX\BusinessFramework\Types\AuditableInterface;
use WaysNX\BusinessFramework\Types\EntityInterface;
use WaysNX\BusinessFramework\Types\MetadataInterface;

/**
 * BaseModelAbstract
 *
 * Framework-independent foundation class for all WBF entities.
 *
 * Purpose:
 * Provide a common foundation for all business entities in the WaysNX Business Framework
 * that is NOT tied to any specific framework (Laravel, Node.js, etc.).
 *
 * This abstract class defines the core structure and responsibilities that every
 * WBF entity must implement. Framework-specific implementations (Laravel, Node.js)
 * will extend this class and provide framework-specific implementations of abstract methods.
 *
 * Responsibilities:
 * - Provide unique entity identification (UUID support)
 * - Track entity type and version
 * - Manage audit information (created_by, updated_by, deleted_by)
 * - Support standard timestamps (created_at, updated_at, deleted_at)
 * - Store flexible metadata
 * - Support soft deletes
 * - Provide lifecycle extension points for child classes
 * - Support serialization (toArray, toJson)
 *
 * Features:
 * - Strict typing throughout
 * - Framework-independent contracts
 * - Extensible via protected lifecycle hooks
 * - No business-specific logic
 * - Suitable for multi-framework implementations
 *
 * Architecture:
 * This class is abstract and cannot be instantiated directly. Framework-specific
 * implementations (Laravel BaseModel) extend this class and provide:
 * - UUID generation implementation
 * - Timestamp creation implementation
 * - Framework-specific serialization
 *
 * Usage:
 * ```php
 * // In Laravel implementation:
 * class BaseModel extends BaseModelAbstract {
 *     // Implement abstract methods using Laravel traits
 * }
 *
 * // In user code:
 * class Project extends BaseModel {
 *     protected function validate(): void {
 *         // Implement project-specific validation
 *     }
 * }
 * ```
 *
 * @implements EntityInterface
 * @implements AuditableInterface
 * @implements MetadataInterface
 * @package WaysNX\BusinessFramework\Core
 */
abstract class BaseModelAbstract implements EntityInterface, AuditableInterface, MetadataInterface
{
    /**
     * The entity's unique identifier (UUID or ID)
     *
     * @var string|int
     */
    protected string|int $entityId;

    /**
     * The entity's type (fully qualified class name)
     *
     * @var string
     */
    protected string $entityType;

    /**
     * The current version of the entity
     *
     * @var int
     */
    protected int $entityVersion = 1;

    /**
     * The metadata storage
     *
     * @var array
     */
    protected array $metadata = [];

    /**
     * The identifier of the user who created the entity
     *
     * @var string|int|null
     */
    protected string|int|null $createdBy = null;

    /**
     * The identifier of the user who last updated the entity
     *
     * @var string|int|null
     */
    protected string|int|null $updatedBy = null;

    /**
     * The identifier of the user who deleted the entity
     *
     * @var string|int|null
     */
    protected string|int|null $deletedBy = null;

    /**
     * The timestamp when the entity was created
     *
     * @var mixed (DateTimeImmutable in Laravel, Date in Node.js, etc.)
     */
    protected mixed $createdAt;

    /**
     * The timestamp when the entity was last updated
     *
     * @var mixed|null
     */
    protected mixed $updatedAt = null;

    /**
     * The timestamp when the entity was deleted (soft delete)
     *
     * @var mixed|null
     */
    protected mixed $deletedAt = null;

    /**
     * Initialize a new BaseModel instance
     *
     * Initializes all base entity properties including identifiers, versioning,
     * audit information, and metadata. Sets the entity type based on the class name.
     *
     * Subclasses should call parent::__construct() in their constructor.
     *
     * @return void
     */
    public function __construct()
    {
        // Initialize identifiers
        $this->setEntityId($this->generateUuid());
        $this->setEntityType(static::class);

        // Initialize versioning
        $this->setEntityVersion(1);

        // Initialize audit trail
        $this->initializeAuditTimestamps();

        // Initialize metadata (empty by default)
        $this->clearMetadata();
    }

    /**
     * Generate a new unique identifier
     *
     * Abstract method - must be implemented by framework-specific subclass.
     *
     * @return string|int A new unique identifier (UUID string in Laravel, etc.)
     */
    abstract protected function generateUuid(): string|int;

    /**
     * Initialize audit timestamps
     *
     * Abstract method - must be implemented by framework-specific subclass.
     * Called during entity construction to set initial timestamps.
     *
     * @return void
     */
    abstract protected function initializeAuditTimestamps(): void;

    /**
     * Set the entity identifier
     *
     * @param string|int $id The entity identifier
     *
     * @return void
     */
    protected function setEntityId(string|int $id): void
    {
        $this->entityId = $id;
    }

    /**
     * Get the entity identifier
     *
     * @return string|int The entity identifier
     */
    public function getEntityId(): string|int
    {
        return $this->entityId;
    }

    /**
     * Set the entity type
     *
     * @param string $type The entity type (typically the class name)
     *
     * @return void
     */
    protected function setEntityType(string $type): void
    {
        $this->entityType = $type;
    }

    /**
     * Get the entity type
     *
     * @return string The entity type
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * Get the current entity version
     *
     * @return int The version number (starting from 1)
     */
    public function getEntityVersion(): int
    {
        return $this->entityVersion;
    }

    /**
     * Set the entity version
     *
     * @param int $version The version number to set
     *
     * @return void
     */
    protected function setEntityVersion(int $version): void
    {
        if ($version < 1) {
            $version = 1;
        }
        $this->entityVersion = $version;
    }

    /**
     * Increment the entity version
     *
     * Increments the version by 1. Typically called before an update operation.
     *
     * @return int The new version number
     */
    protected function incrementVersion(): int
    {
        return ++$this->entityVersion;
    }

    /**
     * Reset the entity version to 1
     *
     * @return void
     */
    protected function resetVersion(): void
    {
        $this->entityVersion = 1;
    }

    /**
     * Get all metadata as an associative array
     *
     * @return array The complete metadata collection
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get a specific metadata value by key
     *
     * @param string $key The metadata key
     * @param mixed $default Default value if key doesn't exist
     *
     * @return mixed The metadata value or default
     */
    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Set a metadata value
     *
     * @param string $key The metadata key
     * @param mixed $value The metadata value
     *
     * @return void
     */
    public function setMetadataValue(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    /**
     * Check if a metadata key exists
     *

     * @param string $key The metadata key
     *

     * @return bool True if the key exists, false otherwise
     */
    public function hasMetadata(string $key): bool
    {
        return isset($this->metadata[$key]);
    }

    /**
     * Remove a metadata key
     *

     * @param string $key The metadata key
     *

     * @return void
     */
    public function removeMetadata(string $key): void
    {
        unset($this->metadata[$key]);
    }

    /**
     * Clear all metadata
     *

     * @return void
     */
    public function clearMetadata(): void
    {
        $this->metadata = [];
    }

    /**
     * Get the creator of the entity
     *

     * @return string|int|null The creator's identifier or null if not tracked
     */
    public function getCreatedBy(): string|int|null
    {
        return $this->createdBy;
    }

    /**
     * Set the creator of the entity
     *

     * @param string|int $userId The user identifier
     *

     * @return void
     */
    public function setCreatedBy(string|int $userId): void
    {
        $this->createdBy = $userId;
    }

    /**
     * Get the last updater of the entity
     *

     * @return string|int|null The updater's identifier or null if never updated
     */
    public function getUpdatedBy(): string|int|null
    {
        return $this->updatedBy;
    }

    /**
     * Set the last updater of the entity
     *

     * @param string|int $userId The user identifier
     *

     * @return void
     */
    public function setUpdatedBy(string|int $userId): void
    {
        $this->updatedBy = $userId;
        $this->updateTimestamp();
    }

    /**
     * Get the deleter of the entity (soft delete)
     *

     * @return string|int|null The deleter's identifier or null if not deleted
     */
    public function getDeletedBy(): string|int|null
    {
        return $this->deletedBy;
    }

    /**
     * Set the deleter of the entity (soft delete)
     *

     * @param string|int $userId The user identifier
     *

     * @return void
     */
    public function setDeletedBy(string|int $userId): void
    {
        $this->deletedBy = $userId;
        $this->deleteTimestamp();
    }

    /**
     * Get the creation timestamp
     *

     * @return mixed The creation timestamp (framework-specific datetime type)
     */
    public function getCreatedAt(): mixed
    {
        return $this->createdAt;
    }

    /**
     * Get the last update timestamp
     *

     * @return mixed|null The last update timestamp or null if never updated
     */
    public function getUpdatedAt(): mixed
    {
        return $this->updatedAt;
    }

    /**
     * Get the deletion timestamp (soft delete)
     *

     * @return mixed|null The deletion timestamp or null if not deleted
     */
    public function getDeletedAt(): mixed
    {
        return $this->deletedAt;
    }

    /**
     * Determine if the entity is soft deleted
     *

     * @return bool True if deleted, false otherwise
     */
    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    /**
     * Restore a soft-deleted entity
     *

     * @return void
     */
    public function restore(): void
    {
        $this->deletedAt = null;
        $this->deletedBy = null;
    }

    /**
     * Update the last modification timestamp
     *

     * Abstract method - must be implemented by framework-specific subclass.
     *

     * @return void
     */
    abstract protected function updateTimestamp(): void;

    /**
     * Update the deletion timestamp
     *

     * Abstract method - must be implemented by framework-specific subclass.
     *

     * @return void
     */
    abstract protected function deleteTimestamp(): void;

    /**
     * Convert the entity to an array representation
     *

     * Abstract method - must be implemented by framework-specific subclass.
     * Subclass should call parent::toArray() and extend with additional fields.
     *

     * @return array The entity as an associative array
     */
    abstract public function toArray(): array;

    /**
     * Convert the entity to JSON representation
     *

     * Abstract method - must be implemented by framework-specific subclass.
     *

     * @return string The entity as a JSON string
     */
    abstract public function toJson(): string;

    /**
     * Hook called before entity validation
     *

     * Override in child classes to customize validation preparation.
     *

     * @return void
     */
    protected function beforeValidate(): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity validation
     *

     * Override in child classes to customize post-validation logic.
     *

     * @return void
     */
    protected function afterValidate(): void
    {
        // Override in child class
    }

    /**
     * Hook called before entity creation
     *

     * Override in child classes to customize pre-creation logic.
     *

     * @return void
     */
    protected function beforeCreate(): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity creation
     *

     * Override in child classes to customize post-creation logic.
     *

     * @return void
     */
    protected function afterCreate(): void
    {
        // Override in child class
    }

    /**
     * Hook called before entity update
     *

     * Override in child classes to customize pre-update logic.
     *

     * @return void
     */
    protected function beforeUpdate(): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity update
     *

     * Override in child classes to customize post-update logic.
     *

     * @return void
     */
    protected function afterUpdate(): void
    {
        // Override in child class
    }

    /**
     * Hook called before entity deletion
     *

     * Override in child classes to customize pre-deletion logic.
     *

     * @return void
     */
    protected function beforeDelete(): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity deletion
     *

     * Override in child classes to customize post-deletion logic.
     *

     * @return void
     */
    protected function afterDelete(): void
    {
        // Override in child class
    }

    /**
     * Validation hook
     *

     * Override in child classes to implement validation rules.
     *

     * @return void
     */
    protected function validate(): void
    {
        // Override in child class to implement validation
    }
}
