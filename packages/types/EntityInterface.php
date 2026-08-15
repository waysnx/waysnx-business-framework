<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Types;

/**
 * EntityInterface
 *
 * Core contract for all WBF entities (framework-independent).
 *
 * This interface defines the contract that every business entity in the WaysNX
 * Business Framework must implement to ensure consistent behavior, identification,
 * and lifecycle management across all implementations (Laravel, Node.js, etc.).
 *
 * Responsibilities:
 * - Define the contract for entity identification
 * - Ensure all entities have a unique identifier
 * - Provide access to entity type information
 * - Support entity versioning
 * - Define the contract for serialization
 *
 * Framework Compliance:
 * - This is a framework-independent contract
 * - Implementations in Laravel: WaysNX\BusinessFramework\Contracts\EntityInterface
 * - Future implementations should implement this interface
 *
 * @package WaysNX\BusinessFramework\Types
 */
interface EntityInterface
{
    /**
     * Get the unique entity identifier
     *
     * @return string|int The entity's unique identifier (typically UUID or numeric ID)
     */
    public function getEntityId(): string|int;

    /**
     * Get the entity type
     *
     * The entity type identifies the class or category of the entity.
     * Used for runtime type checking and serialization.
     *
     * @return string The fully qualified entity type (e.g., 'WaysNX\Project\Models\Project')
     */
    public function getEntityType(): string;

    /**
     * Get the current version of the entity
     *
     * @return int The current version number (starting from 1)
     */
    public function getEntityVersion(): int;

    /**
     * Convert the entity to an array representation
     *
     * @return array The entity as an associative array
     */
    public function toArray(): array;

    /**
     * Convert the entity to JSON representation
     *
     * @return string The entity as a JSON string
     */
    public function toJson(): string;
}
