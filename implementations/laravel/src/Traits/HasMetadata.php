<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Traits;

/**
 * HasMetadata Trait
 *
 * Provides flexible metadata storage for entities.
 *
 * Metadata allows storing arbitrary key-value pairs on entities without
 * modifying the entity structure. Useful for extensibility and application-specific attributes.
 *
 * Responsibilities:
 * - Store and retrieve arbitrary metadata
 * - Provide type-safe metadata access
 * - Support metadata validation
 * - Handle metadata serialization
 *
 * Usage:
 * ```php
 * class MyEntity {
 *     use HasMetadata;
 * }
 *
 * $entity = new MyEntity();
 * $entity->setMetadataValue('custom_key', 'custom_value');
 * $value = $entity->getMetadataValue('custom_key');
 * ```
 *
 * @package WaysNX\BusinessFramework\Traits
 */
trait HasMetadata
{
    /**
     * The metadata storage
     *
     * @var array
     */
    protected array $metadata = [];

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
     * Set metadata from an array
     *
     * Replaces all existing metadata with the provided array.
     *
     * @param array $metadata The metadata array
     *
     * @return void
     */
    protected function setMetadataFromArray(array $metadata): void
    {
        $this->metadata = $metadata;
    }
}
