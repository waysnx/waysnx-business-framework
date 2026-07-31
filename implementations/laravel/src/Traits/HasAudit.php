<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Traits;

use DateTimeImmutable;

/**
 * HasAudit Trait
 *
 * Provides audit tracking capabilities for entities.
 *
 * Tracks who created, updated, and deleted entities, along with timestamps
 * for each action. Supports soft deletes.
 *
 * Responsibilities:
 * - Track entity creation with creator information
 * - Track updates with updater information
 * - Track soft deletes with deleter information
 * - Maintain audit timestamps
 * - Support deletion state checking
 *
 * Usage:
 * ```php
 * class MyEntity {
 *     use HasAudit;
 * }
 *
 * $entity = new MyEntity();
 * $entity->setCreatedBy('user-123');
 * $creator = $entity->getCreatedBy();
 * $createdAt = $entity->getCreatedAt();
 * ```
 *
 * @package WaysNX\BusinessFramework\Traits
 */
trait HasAudit
{
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
     * @var DateTimeImmutable
     */
    protected DateTimeImmutable $createdAt;

    /**
     * The timestamp when the entity was last updated
     *
     * @var DateTimeImmutable|null
     */
    protected ?DateTimeImmutable $updatedAt = null;

    /**
     * The timestamp when the entity was deleted (soft delete)
     *
     * @var DateTimeImmutable|null
     */
    protected ?DateTimeImmutable $deletedAt = null;

    /**
     * Initialize audit timestamps
     *
     * Called during entity construction to set initial timestamps.
     *
     * @return void
     */
    protected function initializeAuditTimestamps(): void
    {
        $this->createdAt = new DateTimeImmutable();
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
     * Get the creator of the entity
     *
     * @return string|int|null The creator's identifier or null if not tracked
     */
    public function getCreatedBy(): string|int|null
    {
        return $this->createdBy;
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
        $this->updatedAt = new DateTimeImmutable();
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
     * Set the deleter of the entity (soft delete)
     *
     * @param string|int $userId The user identifier
     *
     * @return void
     */
    public function setDeletedBy(string|int $userId): void
    {
        $this->deletedBy = $userId;
        $this->deletedAt = new DateTimeImmutable();
    }

    /**
     * Get the deleter of the entity
     *
     * @return string|int|null The deleter's identifier or null if not deleted
     */
    public function getDeletedBy(): string|int|null
    {
        return $this->deletedBy;
    }

    /**
     * Get the creation timestamp
     *
     * @return DateTimeImmutable The creation timestamp
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Get the last update timestamp
     *
     * @return DateTimeImmutable|null The last update timestamp or null if never updated
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Get the deletion timestamp (soft delete)
     *
     * @return DateTimeImmutable|null The deletion timestamp or null if not deleted
     */
    public function getDeletedAt(): ?DateTimeImmutable
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
}
