<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Types;

/**
 * SerializableInterface
 *
 * Contract for entities that support serialization (framework-independent).
 *
 * Defines how entities can be converted to portable formats for transmission,
 * storage, or cross-framework communication.
 *
 * Framework Compliance:
 * - This is a framework-independent contract
 * - Implementations in Laravel: via BaseModel toArray() and toJson() methods
 * - Future implementations should implement this interface
 *
 * @package WaysNX\BusinessFramework\Types
 */
interface SerializableInterface
{
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
