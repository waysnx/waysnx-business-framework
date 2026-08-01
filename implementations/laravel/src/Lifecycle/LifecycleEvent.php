<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Lifecycle;

/**
 * LifecycleEvent
 *
 * Immutable value object representing a lifecycle event.
 *
 * Purpose:
 * Represent a specific lifecycle event that framework components can listen to.
 *
 * Responsibilities:
 * - Store event name
 * - Store event metadata
 * - Provide type-safe event information
 *
 * Usage:
 * ```php
 * $event = new LifecycleEvent(
 *     name: 'beforeCreate',
 *     metadata: ['description' => 'Before entity creation']
 * );
 *
 * echo $event->name;
 * ```
 *
 * @package WaysNX\BusinessFramework\Lifecycle
 */
class LifecycleEvent
{
    /**
     * The event name
     *
     * @var string
     */
    public readonly string $name;

    /**
     * Event metadata
     *
     * @var array
     */
    public readonly array $metadata;

    /**
     * Initialize a new LifecycleEvent
     *

     * @param string $name The event name
     * @param array $metadata Event metadata
     */
    public function __construct(
        string $name,
        array $metadata = []
    ) {
        $this->name = $name;
        $this->metadata = $metadata;
    }

    /**
     * Convert event to array
     *

     * @return array The event as array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'metadata' => $this->metadata,
        ];
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
}
