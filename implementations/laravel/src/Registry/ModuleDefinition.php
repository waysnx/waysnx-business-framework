<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Registry;

/**
 * ModuleDefinition
 *
 * Immutable value object representing a module definition.
 *
 * Encapsulates all metadata and information about a registered module.
 * Provides a type-safe way to pass module information throughout the system.
 *
 * Purpose:
 * Serve as a data container for module metadata with immutability guarantees.
 *
 * Responsibilities:
 * - Store module identification (id, name, displayName)
 * - Store module classification (namespace, category)
 * - Store module metadata (version, author, description)
 * - Manage module dependencies
 * - Store module state (enabled flag)
 * - Store module priority and tags
 * - Provide type-safe access to all properties
 * - Ensure data consistency
 *
 * Usage:
 * ```php
 * $definition = new ModuleDefinition(
 *     id: 'project-management',
 *     name: 'ProjectManagement',
 *     displayName: 'Project Management',
 *     description: 'Core project management module',
 *     namespace: 'App\\Modules\\ProjectManagement',
 *     version: '1.0.0',
 *     author: 'acme',
 *     category: 'business',
 *     dependencies: ['core', 'user'],
 *     priority: 10,
 *     enabled: true,
 *     tags: ['core', 'business'],
 *     metadata: ['license' => 'MIT']
 * );
 *
 * echo $definition->id;
 * echo $definition->displayName;
 * $deps = $definition->dependencies;
 * ```
 *
 * Immutability:
 * Once created, ModuleDefinition cannot be modified. All properties are public
 * readonly to enforce immutability while allowing direct property access.
 *
 * @package WaysNX\BusinessFramework\Registry
 */
class ModuleDefinition
{
    /**
     * The module identifier
     *
     * @var string
     */
    public readonly string $id;

    /**
     * The module name
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
     * The module description
     *
     * @var string
     */
    public readonly string $description;

    /**
     * The module namespace
     *
     * @var string
     */
    public readonly string $namespace;

    /**
     * The module version
     *
     * @var string
     */
    public readonly string $version;

    /**
     * The module author
     *
     * @var string
     */
    public readonly string $author;

    /**
     * The module category
     *
     * @var string
     */
    public readonly string $category;

    /**
     * Module dependencies (module IDs)
     *
     * @var array<string>
     */
    public readonly array $dependencies;

    /**
     * Module priority (higher = higher priority)
     *
     * @var int
     */
    public readonly int $priority;

    /**
     * Whether module is enabled
     *
     * @var bool
     */
    public readonly bool $enabled;

    /**
     * Tags for categorization
     *
     * @var array<string>
     */
    public readonly array $tags;

    /**
     * Additional metadata
     *
     * @var array
     */
    public readonly array $metadata;

    /**
     * Initialize a new ModuleDefinition
     *
     * @param string $id The module identifier
     * @param string $name The module name
     * @param string $displayName The display name for UI
     * @param string $description The module description
     * @param string $namespace The module namespace
     * @param string $version The module version
     * @param string $author The module author
     * @param string $category The module category
     * @param array<string> $dependencies Module dependencies (array of module IDs)
     * @param int $priority Module priority
     * @param bool $enabled Whether module is enabled
     * @param array<string> $tags Tags for categorization
     * @param array $metadata Additional metadata
     */
    public function __construct(
        string $id,
        string $name,
        string $displayName,
        string $description = '',
        string $namespace = '',
        string $version = '1.0.0',
        string $author = '',
        string $category = '',
        array $dependencies = [],
        int $priority = 0,
        bool $enabled = true,
        array $tags = [],
        array $metadata = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->displayName = $displayName;
        $this->description = $description;
        $this->namespace = $namespace;
        $this->version = $version;
        $this->author = $author;
        $this->category = $category;
        $this->dependencies = $dependencies;
        $this->priority = $priority;
        $this->enabled = $enabled;
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
            'description' => $this->description,
            'namespace' => $this->namespace,
            'version' => $this->version,
            'author' => $this->author,
            'category' => $this->category,
            'dependencies' => $this->dependencies,
            'priority' => $this->priority,
            'enabled' => $this->enabled,
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
     * Check if module has a specific tag
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
     * Check if module has a specific dependency
     *
     * @param string $dependencyId The dependency module ID
     *
     * @return bool True if dependency exists
     */
    public function hasDependency(string $dependencyId): bool
    {
        return in_array($dependencyId, $this->dependencies, true);
    }

    /**
     * Get count of dependencies
     *
     * @return int The count of dependencies
     */
    public function dependencyCount(): int
    {
        return count($this->dependencies);
    }

    /**
     * Check if module has dependencies
     *
     * @return bool True if module has dependencies
     */
    public function hasDependencies(): bool
    {
        return count($this->dependencies) > 0;
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
     * Check if module is marked as experimental
     *
     * @return bool True if experimental
     */
    public function isExperimental(): bool
    {
        return (bool) $this->getMetadataValue('experimental', false);
    }

    /**
     * Check if module is marked as deprecated
     *
     * @return bool True if deprecated
     */
    public function isDeprecated(): bool
    {
        return (bool) $this->getMetadataValue('deprecated', false);
    }
}
