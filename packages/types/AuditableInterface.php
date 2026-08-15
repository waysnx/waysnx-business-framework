<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Types;

/**
 * AuditableInterface
 *
 * Contract for entities that support audit tracking (framework-independent).
 *
 * Provides methods to retrieve audit information including who created, updated,
 * or deleted the entity, and when these actions occurred. Supports soft deletes
 * with deletion tracking.
 *
 * Framework Compliance:
 * - This is a framework-independent contract
 * - Implementations in Laravel: WaysNX\BusinessFramework\Contracts\AuditableInterface
 * - Future implementations should implement this interface
 *
 * Responsibilities:
 * - Track entity creation and modification
 * - Record who performed each action
 * - Support soft deletes with deletion tracking
 * - Provide audit trail access
 *
 * @package WaysNX\BusinessFramework\Types
 */
interface AuditableInterface
{
    /**
     * Get the identifier of the user who created the entity
     *
     * @return string|int|null The creator's identifier or null if not tracked
     */
    public function getCreatedBy(): string|int|null;

    /**
     * Get the identifier of the user who last updated the entity
     *
     * @return string|int|null The updater's identifier or null if not tracked
     */
    public function getUpdatedBy(): string|int|null;

    /**
     * Get the identifier of the user who deleted the entity
     *
     * @return string|int|null The deleter's identifier or null if not deleted
     */
    public function getDeletedBy(): string|int|null;

    /**
     * Get the timestamp when the entity was created
     *
     * Framework-specific implementations will return appropriate datetime objects:
     * - Laravel: DateTimeImmutable
     * - Node.js: Date or equivalent
     *
     * @return mixed The creation timestamp (framework-specific datetime type)
     */
    public function getCreatedAt(): mixed;

    /**
     * Get the timestamp when the entity was last updated
     *
     * Framework-specific implementations will return appropriate datetime objects:
     * - Laravel: DateTimeImmutable|null
     * - Node.js: Date|null or equivalent
     *
     * @return mixed|null The last update timestamp or null if never updated
     */
    public function getUpdatedAt(): mixed;

    /**
     * Get the timestamp when the entity was deleted
     *
     * Framework-specific implementations will return appropriate datetime objects:
     * - Laravel: DateTimeImmutable|null
     * - Node.js: Date|null or equivalent
     *
     * @return mixed|null The deletion timestamp or null if not deleted
     */
    public function getDeletedAt(): mixed;

    /**
     * Determine if the entity is soft deleted
     *
     * @return bool True if the entity is soft deleted, false otherwise
     */
    public function isDeleted(): bool;
}
