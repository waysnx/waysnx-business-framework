<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Traits;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * HasIdentifiers Trait
 *
 * Provides entity identification support including UUID generation and management.
 *
 * Responsibilities:
 * - Generate and manage UUID identifiers
 * - Provide entity ID access
 * - Support entity type identification
 *
 * Usage:
 * ```php
 * class MyEntity {
 *     use HasIdentifiers;
 * }
 *
 * $entity = new MyEntity();
 * $id = $entity->getEntityId();
 * $type = $entity->getEntityType();
 * ```
 *
 * @package WaysNX\BusinessFramework\Traits
 */
trait HasIdentifiers
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
     * Generate a new UUID identifier
     *
     * @return UuidInterface A new UUID instance
     */
    protected function generateUuid(): UuidInterface
    {
        return Uuid::uuid4();
    }

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
}
