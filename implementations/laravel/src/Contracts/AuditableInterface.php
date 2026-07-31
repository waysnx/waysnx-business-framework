<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Contracts;

use DateTimeImmutable;

/**
 * AuditableInterface
 *
 * Contract for entities that support audit tracking.
 *
 * Provides methods to retrieve audit information including who created, updated,
 * or deleted the entity, and when these actions occurred.
 *
 * Responsibilities:
 * - Track entity creation and modification
 * - Record who performed each action
 * - Support soft deletes with deletion tracking
 * - Provide audit trail access
 *
 * @package WaysNX\BusinessFramework\Contracts
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
     * @return DateTimeImmutable The creation timestamp
     */
    public function getCreatedAt(): DateTimeImmutable;

    /**
     * Get the timestamp when the entity was last updated
     *
     * @return DateTimeImmutable|null The last update timestamp or null if never updated
     */
    public function getUpdatedAt(): ?DateTimeImmutable;

    /**
     * Get the timestamp when the entity was deleted
     *
     * @return DateTimeImmutable|null The deletion timestamp or null if not deleted
     */
    public function getDeletedAt(): ?DateTimeImmutable;

    /**
     * Determine if the entity is soft deleted
     *
     * @return bool True if the entity is soft deleted, false otherwise
     */
    public function isDeleted(): bool;
}
