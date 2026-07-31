<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Contracts;

/**
 * MetadataInterface
 *
 * Contract for entities that support metadata storage.
 *
 * Provides methods to store and retrieve arbitrary metadata about an entity.
 * Metadata is useful for storing application-specific or extensible attributes
 * without modifying the entity structure.
 *
 * Responsibilities:
 * - Store arbitrary metadata as key-value pairs
 * - Retrieve metadata with type safety
 * - Support metadata validation and serialization
 * - Provide metadata existence checking
 *
 * @package WaysNX\BusinessFramework\Contracts
 */
interface MetadataInterface
{
    /**
     * Get all metadata as an associative array
     *
     * @return array The complete metadata collection
     */
    public function getMetadata(): array;

    /**
     * Get a specific metadata value by key
     *
     * @param string $key The metadata key
     * @param mixed $default Default value if key doesn't exist
     *
     * @return mixed The metadata value or default
     */
    public function getMetadataValue(string $key, mixed $default = null): mixed;

    /**
     * Set a metadata value
     *
     * @param string $key The metadata key
     * @param mixed $value The metadata value
     *
     * @return void
     */
    public function setMetadataValue(string $key, mixed $value): void;

    /**
     * Check if a metadata key exists
     *
     * @param string $key The metadata key
     *
     * @return bool True if the key exists, false otherwise
     */
    public function hasMetadata(string $key): bool;

    /**
     * Remove a metadata key
     *
     * @param string $key The metadata key
     *
     * @return void
     */
    public function removeMetadata(string $key): void;

    /**
     * Clear all metadata
     *
     * @return void
     */
    public function clearMetadata(): void;
}
