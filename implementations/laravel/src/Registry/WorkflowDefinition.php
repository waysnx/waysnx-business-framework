<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Registry;

/**
 * WorkflowDefinition
 *
 * Immutable value object representing a workflow definition.
 *
 * Encapsulates all metadata and information about a registered workflow.
 * Defines a reusable business process that sequences business functions.
 *
 * Purpose:
 * Serve as a data container for workflow metadata with immutability guarantees.
 *
 * Responsibilities:
 * - Store workflow identification (id, name, displayName)
 * - Store workflow classification (module, category)
 * - Store workflow metadata (version, description, owner)
 * - Manage workflow steps (sequences of business functions)
 * - Store entry and exit points
 * - Store trigger definitions
 * - Manage supported entity types
 * - Store workflow state (enabled flag)
 * - Provide type-safe access to all properties
 *
 * Usage:
 * ```php
 * $definition = new WorkflowDefinition(
 *     id: 'employee-onboarding',
 *     name: 'EmployeeOnboarding',
 *     displayName: 'Employee Onboarding',
 *     moduleId: 'hr',
 *     triggerType: 'event',
 *     triggerEvent: 'employee.created',
 *     entryFunction: 'create-employee-record',
 *     exitFunction: 'send-welcome-email',
 *     steps: [new WorkflowStep(...)],
 *     supportedEntityTypes: ['Employee']
 * );
 *
 * echo $definition->id;
 * $steps = $definition->steps;
 * ```
 *
 * Immutability:
 * Once created, WorkflowDefinition cannot be modified.
 * Workflows describe processes only, no runtime state.
 *
 * @package WaysNX\BusinessFramework\Registry
 */
class WorkflowDefinition
{
    /**
     * The workflow identifier
     *
     * @var string
     */
    public readonly string $id;

    /**
     * The workflow name
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
     * The workflow description
     *

     * @var string
     */
    public readonly string $description;

    /**
     * The workflow version
     *

     * @var string
     */
    public readonly string $version;

    /**
     * The module ID this workflow belongs to
     *

     * @var string
     */
    public readonly string $moduleId;

    /**
     * The workflow category
     *

     * @var string
     */
    public readonly string $category;

    /**
     * The trigger type (event, manual, scheduled, etc.)
     *

     * @var string
     */
    public readonly string $triggerType;

    /**
     * The trigger event that starts the workflow
     *

     * @var string
     */
    public readonly string $triggerEvent;

    /**
     * The entry business function ID
     *

     * @var string
     */
    public readonly string $entryFunction;

    /**
     * The exit business function ID
     *

     * @var string
     */
    public readonly string $exitFunction;

    /**
     * Workflow steps
     *

     * @var array<WorkflowStep>
     */
    public readonly array $steps;

    /**
     * Supported entity types
     *

     * @var array<string>
     */
    public readonly array $supportedEntityTypes;

    /**
     * Workflow priority
     *

     * @var int
     */
    public readonly int $priority;

    /**
     * Whether workflow is enabled
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
     * Initialize a new WorkflowDefinition
     *

     * @param string $id The workflow identifier
     * @param string $name The workflow name
     * @param string $displayName The display name for UI
     * @param string $description The workflow description
     * @param string $version The workflow version
     * @param string $moduleId The module ID
     * @param string $category The workflow category
     * @param string $triggerType The trigger type
     * @param string $triggerEvent The trigger event
     * @param string $entryFunction The entry function ID
     * @param string $exitFunction The exit function ID
     * @param array<WorkflowStep> $steps The workflow steps
     * @param array<string> $supportedEntityTypes Supported entity types
     * @param int $priority The workflow priority
     * @param bool $enabled Whether workflow is enabled
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
        string $triggerType = 'manual',
        string $triggerEvent = '',
        string $entryFunction = '',
        string $exitFunction = '',
        array $steps = [],
        array $supportedEntityTypes = [],
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
        $this->triggerType = $triggerType;
        $this->triggerEvent = $triggerEvent;
        $this->entryFunction = $entryFunction;
        $this->exitFunction = $exitFunction;
        $this->steps = $steps;
        $this->supportedEntityTypes = $supportedEntityTypes;
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
            'triggerType' => $this->triggerType,
            'triggerEvent' => $this->triggerEvent,
            'entryFunction' => $this->entryFunction,
            'exitFunction' => $this->exitFunction,
            'steps' => array_map(fn($step) => $step->toArray(), $this->steps),
            'supportedEntityTypes' => $this->supportedEntityTypes,
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
     * Check if workflow has a specific tag
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
     * Check if workflow supports a specific entity type
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
     * Get count of steps in workflow
     *

     * @return int The count of steps
     */
    public function stepCount(): int
    {
        return count($this->steps);
    }

    /**
     * Check if workflow has steps
     *

     * @return bool True if workflow has steps
     */
    public function hasSteps(): bool
    {
        return count($this->steps) > 0;
    }

    /**
     * Get step by ID
     *

     * @param string $stepId The step ID
     *

     * @return WorkflowStep|null The step or null
     */
    public function getStep(string $stepId): ?WorkflowStep
    {
        foreach ($this->steps as $step) {
            if ($step->id === $stepId) {
                return $step;
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
     * Check if workflow is marked as experimental
     *

     * @return bool True if experimental
     */
    public function isExperimental(): bool
    {
        return (bool) $this->getMetadataValue('experimental', false);
    }

    /**
     * Check if workflow is marked as deprecated
     *

     * @return bool True if deprecated
     */
    public function isDeprecated(): bool
    {
        return (bool) $this->getMetadataValue('deprecated', false);
    }

    /**
     * Check if workflow is event-triggered
     *

     * @return bool True if triggered by event
     */
    public function isEventTriggered(): bool
    {
        return $this->triggerType === 'event';
    }

    /**
     * Check if workflow is manually triggered
     *

     * @return bool True if manually triggered
     */
    public function isManuallyTriggered(): bool
    {
        return $this->triggerType === 'manual';
    }

    /**
     * Check if workflow is scheduled
     *

     * @return bool True if scheduled
     */
    public function isScheduled(): bool
    {
        return $this->triggerType === 'scheduled';
    }
}
