<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Registry;

/**
 * EntityDefinition
 *
 * Immutable value object representing an entity definition.
 *
 * Encapsulates all metadata and information about a registered entity type.
 * Provides a type-safe way to pass entity information throughout the system.
 *
 * Purpose:
 * Serve as a data container for entity metadata with immutability guarantees.
 *
 * Responsibilities:
 * - Store entity identification (id, name, displayName)
 * - Store entity classification (class, namespace)
 * - Store entity metadata (description, version, tags, metadata)
 * - Provide type-safe access to all properties
 * - Ensure data consistency
 *
 * Usage:
 * ```php
 * $definition = new EntityDefinition(
 *     id: 'project',
 *     name: 'Project',
 *     displayName: 'Business Project',
 *     className: 'App\\Models\\Project',
 *     namespace: 'App\\Models',
 *     description: 'Core business project entity',
 *     version: '1.0.0',
 *     tags: ['core', 'business'],
 *     metadata: ['author' => 'acme', 'category' => 'business']
 * );
 *
 * echo $definition->id;
 * echo $definition->displayName;
 * ```
 *
 * Immutability:
 * Once created, EntityDefinition cannot be modified. All properties are public
 * readonly to enforce immutability while allowing direct property access.
 *
 * @package WaysNX\BusinessFramework\Registry
 */
class EntityDefinition
{
    /**
     * The entity identifier
     *
     * @var string
     */
    public readonly string $id;

    /**
     * The entity name
     *
     * @var string
     */
    public readonly string $name;

    /**
     * The display name for UI
     *
     * @var string
     */
    public readonly string $displayName;

    /**
     * The fully qualified class name
     *
     * @var string
     */
    public readonly string $className;

    /**
     * The namespace of the entity
     *
     * @var string
     */
    public readonly string $namespace;

    /**
     * The entity description
     *

     * @var string
     */
    public readonly string $description;

    /**
     * The entity version
     *

     * @var string
     */
    public readonly string $version;

    /**
     * Tags for categorization
     *

     * @var array
     */
    public readonly array $tags;

    /**
     * Additional metadata
     *

     * @var array
     */
    public readonly array $metadata;

    /**
     * Initialize a new EntityDefinition
     *

     * @param string $id The entity identifier
     * @param string $name The entity name
     * @param string $displayName The display name for UI
     * @param string $className The fully qualified class name
     * @param string $namespace The entity namespace
     * @param string $description The entity description
     * @param string $version The entity version
     * @param array $tags Tags for categorization
     * @param array $metadata Additional metadata
     */
    public function __construct(
        string $id,
        string $name,
        string $displayName,
        string $className,
        string $namespace,
        string $description = '',
        string $version = '1.0.0',
        array $tags = [],
        array $metadata = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->displayName = $displayName;
        $this->className = $className;
        $this->namespace = $namespace;
        $this->description = $description;
        $this->version = $version;
        $this->tags = $tags;
        $this->metadata = $metadata;
    }

    /**
     * Convert definition to array
     *

     * @return array The definition as array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'displayName' => $this->displayName,
            'className' => $this->className,
            'namespace' => $this->namespace,
            'description' => $this->description,
            'version' => $this->version,
            'tags' => $this->tags,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Convert definition to JSON
     *

     * @return string The definition as JSON
     */
    public function toJson(): string
    {
        return (string) json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * Check if entity has a specific tag
     *

     * @param string $tag The tag to check
     *

     * @return bool True if tag exists
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags, true);
    }

    /**
     * Get metadata value
     *

     * @param string $key The metadata key
     * @param mixed $default Default value
     *

     * @return mixed The metadata value
     */
    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Check if metadata key exists
     *

     * @param string $key The metadata key
     *

     * @return bool True if key exists
     */
    public function hasMetadata(string $key): bool
    {
        return isset($this->metadata[$key]);
    }

    /**
     * Check if entity is marked as experimental
     *

     * @return bool True if experimental
     */
    public function isExperimental(): bool
    {
        return (bool) $this->getMetadataValue('experimental', false);
    }

    /**
     * Check if entity is marked as deprecated
     *

     * @return bool True if deprecated
     */
    public function isDeprecated(): bool
    {
        return (bool) $this->getMetadataValue('deprecated', false);
    }
}
