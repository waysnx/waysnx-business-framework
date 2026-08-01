<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Registry;

/**
 * ValidationDefinition
 *
 * Immutable value object representing a validation definition.
 *
 * Encapsulates all metadata and information about a registered validation.
 * Defines what should be validated and how failures should be reported.
 *
 * Purpose:
 * Serve as a data container for validation metadata with immutability guarantees.
 *
 * Responsibilities:
 * - Store validation identification (id, name, displayName)
 * - Store validation classification (module, category, scope)
 * - Store validation metadata (version, description, owner)
 * - Manage validation rules (sequences of validation rules)
 * - Store severity and priority information
 * - Manage supported entity types
 * - Store validation state (enabled flag)
 * - Provide type-safe access to all properties
 *
 * Usage:
 * ```php
 * $definition = new ValidationDefinition(
 *     id: 'customer-validation',
 *     name: 'CustomerValidation',
 *     displayName: 'Customer Validation',
 *     moduleId: 'crm',
 *     scope: 'entity',
 *     severity: 'error',
 *     rules: [new ValidationRuleDefinition(...)]
 * );
 *
 * echo $definition->id;
 * $rules = $definition->rules;
 * ```
 *
 * Immutability:
 * Once created, ValidationDefinition cannot be modified.
 * Validations describe metadata only, no execution logic.
 *
 * @package WaysNX\BusinessFramework\Registry
 */
class ValidationDefinition
{
    /**
     * The validation identifier
     *

     * @var string
     */
    public readonly string $id;

    /**
     * The validation name
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
     * The validation description
     *

     * @var string
     */
    public readonly string $description;

    /**
     * The validation version
     *

     * @var string
     */
    public readonly string $version;

    /**
     * The module ID this validation belongs to
     *

     * @var string
     */
    public readonly string $moduleId;

    /**
     * The validation category
     *

     * @var string
     */
    public readonly string $category;

    /**
     * The validation scope (entity, form, api, workflow, etc.)
     *

     * @var string
     */
    public readonly string $scope;

    /**
     * The severity level (error, warning, info)
     *

     * @var string
     */
    public readonly string $severity;

    /**
     * Validation rules
     *

     * @var array<ValidationRuleDefinition>
     */
    public readonly array $rules;

    /**
     * Supported entity types
     *

     * @var array<string>
     */
    public readonly array $supportedEntityTypes;

    /**
     * Referenced business function IDs
     *

     * @var array<string>
     */
    public readonly array $businessFunctionIds;

    /**
     * Referenced workflow IDs
     *

     * @var array<string>
     */
    public readonly array $workflowIds;

    /**
     * Validation priority
     *

     * @var int
     */
    public readonly int $priority;

    /**
     * Whether validation is enabled
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
     * Initialize a new ValidationDefinition
     *

     * @param string $id The validation identifier
     * @param string $name The validation name
     * @param string $displayName The display name for UI
     * @param string $description The validation description
     * @param string $version The validation version
     * @param string $moduleId The module ID
     * @param string $category The validation category
     * @param string $scope The validation scope
     * @param string $severity The severity level
     * @param array<ValidationRuleDefinition> $rules The validation rules
     * @param array<string> $supportedEntityTypes Supported entity types
     * @param array<string> $businessFunctionIds Referenced business function IDs
     * @param array<string> $workflowIds Referenced workflow IDs
     * @param int $priority The validation priority
     * @param bool $enabled Whether validation is enabled
     * @param array<string> $tags Tags for categorization
     * @param array $metadata Additional metadata
     */
    public function __construct(
        string $id,
        string $name,
        string $displayName,
        string $description = '',
        string $version = '1.0.0',
        string $moduleId = '',
        string $category = '',
        string $scope = 'entity',
        string $severity = 'error',
        array $rules = [],
        array $supportedEntityTypes = [],
        array $businessFunctionIds = [],
        array $workflowIds = [],
        int $priority = 0,
        bool $enabled = true,
        array $tags = [],
        array $metadata = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->displayName = $displayName;
        $this->description = $description;
        $this->version = $version;
        $this->moduleId = $moduleId;
        $this->category = $category;
        $this->scope = $scope;
        $this->severity = $severity;
        $this->rules = $rules;
        $this->supportedEntityTypes = $supportedEntityTypes;
        $this->businessFunctionIds = $businessFunctionIds;
        $this->workflowIds = $workflowIds;
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
            'version' => $this->version,
            'moduleId' => $this->moduleId,
            'category' => $this->category,
            'scope' => $this->scope,
            'severity' => $this->severity,
            'rules' => array_map(fn($rule) => $rule->toArray(), $this->rules),
            'supportedEntityTypes' => $this->supportedEntityTypes,
            'businessFunctionIds' => $this->businessFunctionIds,
            'workflowIds' => $this->workflowIds,
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
     * Check if validation has a specific tag
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
     * Check if validation supports a specific entity type
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
     * Get count of rules in validation
     *

     * @return int The count of rules
     */
    public function ruleCount(): int
    {
        return count($this->rules);
    }

    /**
     * Check if validation has rules
     *

     * @return bool True if validation has rules
     */
    public function hasRules(): bool
    {
        return count($this->rules) > 0;
    }

    /**
     * Get rule by ID
     *

     * @param string $ruleId The rule ID
     *

     * @return ValidationRuleDefinition|null The rule or null
     */
    public function getRule(string $ruleId): ?ValidationRuleDefinition
    {
        foreach ($this->rules as $rule) {
            if ($rule->id === $ruleId) {
                return $rule;
            }
        }

        return null;
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
     * Check if validation is marked as experimental
     *

     * @return bool True if experimental
     */
    public function isExperimental(): bool
    {
        return (bool) $this->getMetadataValue('experimental', false);
    }

    /**
     * Check if validation is marked as deprecated
     *

     * @return bool True if deprecated
     */
    public function isDeprecated(): bool
    {
        return (bool) $this->getMetadataValue('deprecated', false);
    }

    /**
     * Check if validation severity is error
     *

     * @return bool True if error severity
     */
    public function isError(): bool
    {
        return $this->severity === 'error';
    }

    /**
     * Check if validation severity is warning
     *

     * @return bool True if warning severity
     */
    public function isWarning(): bool
    {
        return $this->severity === 'warning';
    }

    /**
     * Check if validation severity is info
     *

     * @return bool True if info severity
     */
    public function isInfo(): bool
    {
        return $this->severity === 'info';
    }
}
