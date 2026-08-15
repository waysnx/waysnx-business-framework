<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Types;

/**
 * VersionableInterface
 *
 * Contract for entities that support versioning (framework-independent).
 *
 * Provides methods to manage entity versions for tracking entity changes over time.
 * Used for optimistic locking, change tracking, and version management.
 *
 * Framework Compliance:
 * - This is a framework-independent contract
 * - Implementations in Laravel: via HasVersioning trait
 * - Future implementations should implement this interface
 *
 * Version Semantics:
 * - Versions start at 1
 * - Versions increment on each update
 * - Used for optimistic locking and change tracking
 *
 * @package WaysNX\BusinessFramework\Types
 */
interface VersionableInterface
{
    /**
     * Get the current version of the entity
     *
     * @return int The version number (starting from 1)
     */
    public function getEntityVersion(): int;
}
