<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Workflow;

/**
 * WorkflowTransition
 *
 * Immutable value object representing a workflow transition.
 *
 * Purpose:
 * Represent a transition between workflow steps or endpoints.
 *
 * Responsibilities:
 * - Store transition source and target
 * - Store transition type (sequential, conditional, parallel, retry)
 * - Store transition metadata
 * - Provide type-safe access to transition properties
 *
 * Usage:
 * ```php
 * $transition = new WorkflowTransition(
 *     source: 'step-1',
 *     target: 'step-2',
 *     type: 'sequential',
 *     metadata: ['delay' => 0, 'condition' => 'success']
 * );
 *
 * echo $transition->target;
 * ```
 *
 * @package WaysNX\BusinessFramework\Workflow
 */
class WorkflowTransition
{
    /**
     * The source step ID
     *

     * @var string
     */
    public readonly string $source;

    /**
     * The target step ID or endpoint
     *

     * @var string
     */
    public readonly string $target;

    /**
     * The transition type (sequential, conditional, parallel, retry)
     *

     * @var string
     */
    public readonly string $type;

    /**
     * Transition metadata
     *

     * @var array
     */
    public readonly array $metadata;

    /**
     * Initialize a new WorkflowTransition
     *

     * @param string $source The source step ID
     * @param string $target The target step ID
     * @param string $type The transition type
     * @param array $metadata Transition metadata
     */
    public function __construct(
        string $source,
        string $target,
        string $type = 'sequential',
        array $metadata = []
    ) {
        $this->source = $source;
        $this->target = $target;
        $this->type = $type;
        $this->metadata = $metadata;
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
     * Convert transition to array
     *

     * @return array The transition as array
     */
    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'target' => $this->target,
            'type' => $this->type,
            'metadata' => $this->metadata,
        ];
    }
}
