<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Registry;

/**
 * BusinessFunctionDefinition
 *
 * Immutable value object representing a business function definition.
 *
 * Encapsulates all metadata and information about a registered business function.
 * Provides a type-safe way to pass business function information throughout the system.
 *
 * Purpose:
 * Serve as a data container for business function metadata with immutability guarantees.
 *
 * Responsibilities:
 * - Store function identification (id, name, displayName)
 * - Store function classification (module, category)
 * - Store function metadata (description, version, owner)
 * - Manage input and output definitions
 * - Manage supported entity types
 * - Manage required permissions
 * - Store function state (enabled flag)
 * - Store tags and custom metadata
 * - Provide type-safe access to all properties
 * - Ensure data consistency
 *
 * Usage:
 * ```php
 * $definition = new BusinessFunctionDefinition(
 *     id: 'create-customer',
 *     name: 'CreateCustomer',
 *     displayName: 'Create Customer',
 *     description: 'Creates a new customer in the system',
 *     moduleId: 'crm',
 *     category: 'customer-management',
 *     version: '1.0.0',
 *     tags: ['core', 'customer'],
 *     inputDefinitions: [
 *         ['name' => 'firstName', 'type' => 'string'],
 *         ['name' => 'lastName', 'type' => 'string']
 *     ],
 *     outputDefinitions: [
 *         ['name' => 'customerId', 'type' => 'string']
 *     ],
 *     supportedEntityTypes: ['Customer'],
 *     requiredPermissions: ['customer.create'],
 *     metadata: ['owner' => 'acme']
 * );
 *
 * echo $definition->id;
 * echo $definition->displayName;
 * $inputs = $definition->inputDefinitions;
 * ```
 *
 * Immutability:
 * Once created, BusinessFunctionDefinition cannot be modified. All properties are public
 * readonly to enforce immutability while allowing direct property access.
 *
 * Definitions do NOT contain runtime state. They describe capabilities only.
 *
 * @package WaysNX\BusinessFramework\Registry
 */
class BusinessFunctionDefinition
{
    /**
     * The function identifier
     *
     * @var string
     */
    public readonly string $id;

    /**
     * The function name
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
     * The function description
     *
     * @var string
     */
    public readonly string $description;

    /**
     * The module ID this function belongs to
     *
     * @var string
     */
    public readonly string $moduleId;

    /**
     * The function category
     *
     * @var string
     */
    public readonly string $category;

    /**
     * The function version
     *
     * @var string
     */
    public readonly string $version;

    /**
     * Input parameter definitions
     *
     * @var array<array>
     */
    public readonly array $inputDefinitions;

    /**
     * Output parameter definitions
     *
     * @var array<array>
     */
    public readonly array $outputDefinitions;

    /**
     * Supported entity types
     *
     * @var array<string>
     */
    public readonly array $supportedEntityTypes;

    /**
     * Required permissions
     *
     * @var array<string>
     */
    public readonly array $requiredPermissions;

    /**
     * Whether function is enabled
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
     * Initialize a new BusinessFunctionDefinition
     *
     * @param string $id The function identifier
     * @param string $name The function name
     * @param string $displayName The display name for UI
     * @param string $description The function description
     * @param string $moduleId The module ID
     * @param string $category The function category
     * @param string $version The function version
     * @param array<array> $inputDefinitions Input parameter definitions
     * @param array<array> $outputDefinitions Output parameter definitions
     * @param array<string> $supportedEntityTypes Supported entity types
     * @param array<string> $requiredPermissions Required permissions
     * @param bool $enabled Whether function is enabled
     * @param array<string> $tags Tags for categorization
     * @param array $metadata Additional metadata
     */
    public function __construct(
        string $id,
        string $name,
        string $displayName,
        string $description = '',
        string $moduleId = '',
        string $category = '',
        string $version = '1.0.0',
        array $inputDefinitions = [],
        array $outputDefinitions = [],
        array $supportedEntityTypes = [],
        array $requiredPermissions = [],
        bool $enabled = true,
        array $tags = [],
        array $metadata = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->displayName = $displayName;
        $this->description = $description;
        $this->moduleId = $moduleId;
        $this->category = $category;
        $this->version = $version;
        $this->inputDefinitions = $inputDefinitions;
        $this->outputDefinitions = $outputDefinitions;
        $this->supportedEntityTypes = $supportedEntityTypes;
        $this->requiredPermissions = $requiredPermissions;
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
            'moduleId' => $this->moduleId,
            'category' => $this->category,
            'version' => $this->version,
            'inputDefinitions' => $this->inputDefinitions,
            'outputDefinitions' => $this->outputDefinitions,
            'supportedEntityTypes' => $this->supportedEntityTypes,
            'requiredPermissions' => $this->requiredPermissions,
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
     * Check if function has a specific tag
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
     * Check if function supports a specific entity type
     *
     * @param string $entityType The entity type to check
     *
     * @return bool True if entity type is supported
     */
    public function supportsEntityType(string $entityType): bool
    {
        return in_array($entityType, $this->supportedEntityTypes, true);
    }

    /**
     * Get count of supported entity types
     *
     * @return int The count of supported entity types
     */
    public function supportedEntityTypeCount(): int
    {
        return count($this->supportedEntityTypes);
    }

    /**
     * Check if function has supported entity types
     *
     * @return bool True if function has supported entity types
     */
    public function hasSupportedEntityTypes(): bool
    {
        return count($this->supportedEntityTypes) > 0;
    }

    /**
     * Check if function requires a specific permission
     *
     * @param string $permission The permission to check
     *
     * @return bool True if permission is required
     */
    public function requiresPermission(string $permission): bool
    {
        return in_array($permission, $this->requiredPermissions, true);
    }

    /**
     * Get count of required permissions
     *
     * @return int The count of required permissions
     */
    public function requiredPermissionCount(): int
    {
        return count($this->requiredPermissions);
    }

    /**
     * Check if function requires any permissions
     *
     * @return bool True if function requires any permissions
     */
    public function requiresPermissions(): bool
    {
        return count($this->requiredPermissions) > 0;
    }

    /**
     * Get count of input parameters
     *

     * @return int The count of input parameters
     */
    public function inputCount(): int
    {
        return count($this->inputDefinitions);
    }

    /**
     * Check if function has input parameters
     *

     * @return bool True if function has input parameters
     */
    public function hasInputs(): bool
    {
        return count($this->inputDefinitions) > 0;
    }

    /**
     * Get count of output parameters
     *

     * @return int The count of output parameters
     */
    public function outputCount(): int
    {
        return count($this->outputDefinitions);
    }

    /**
     * Check if function has output parameters
     *

     * @return bool True if function has output parameters
     */
    public function hasOutputs(): bool
    {
        return count($this->outputDefinitions) > 0;
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
     * Check if function is marked as experimental
     *

     * @return bool True if experimental
     */
    public function isExperimental(): bool
    {
        return (bool) $this->getMetadataValue('experimental', false);
    }

    /**
     * Check if function is marked as deprecated
     *

     * @return bool True if deprecated
     */
    public function isDeprecated(): bool
    {
        return (bool) $this->getMetadataValue('deprecated', false);
    }
}
