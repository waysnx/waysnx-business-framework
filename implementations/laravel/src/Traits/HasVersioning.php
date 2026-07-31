<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Traits;

/**
 * HasVersioning Trait
 *
 * Provides entity versioning support for tracking entity changes over time.
 *
 * Responsibilities:
 * - Track and manage entity version numbers
 * - Support version increments
 * - Provide version access methods
 *
 * Version Semantics:
 * - Versions start at 1
 * - Versions increment on each update
 * - Used for optimistic locking and change tracking
 *
 * Usage:
 * ```php
 * class MyEntity {
 *     use HasVersioning;
 * }
 *
 * $entity = new MyEntity();
 * $version = $entity->getEntityVersion(); // Returns 1
 * $entity->incrementVersion();
 * $version = $entity->getEntityVersion(); // Returns 2
 * ```
 *
 * @package WaysNX\BusinessFramework\Traits
 */
trait HasVersioning
{
    /**
     * The current version of the entity
     *
     * @var int
     */
    protected int $entityVersion = 1;

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
}
