<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Lifecycle;

/**
 * LifecycleHandler
 *
 * Immutable value object representing a lifecycle handler.
 *
 * Purpose:
 * Represent a handler registration for a lifecycle event.
 *
 * Responsibilities:
 * - Store handler identification
 * - Store supported events
 * - Store handler priority
 * - Store enabled flag
 * - Store handler metadata
 * - Reference the handler callable/interface
 *
 * Usage:
 * ```php
 * $handler = new LifecycleHandler(
 *     id: 'log-handler',
 *     callable: $loggingFunction,
 *     supportedEvents: ['beforeCreate', 'afterCreate'],
 *     priority: 10,
 *     enabled: true
 * );
 *
 * echo $handler->id;
 * $handler->callable(...);
 * ```
 *
 * @package WaysNX\BusinessFramework\Lifecycle
 */
class LifecycleHandler
{
    /**
     * The handler ID
     *

     * @var string
     */
    public readonly string $id;

    /**
     * The handler callable
     *

     * @var callable
     */
    public readonly mixed $callable;

    /**
     * Supported events
     *

     * @var array<string>
     */
    public readonly array $supportedEvents;

    /**
     * Handler priority (higher = earlier execution)
     *

     * @var int
     */
    public readonly int $priority;

    /**
     * Whether handler is enabled
     *

     * @var bool
     */
    public readonly bool $enabled;

    /**
     * Handler metadata
     *

     * @var array
     */
    public readonly array $metadata;

    /**
     * Initialize a new LifecycleHandler
     *

     * @param string $id The handler ID
     * @param callable $callable The handler callable
     * @param array<string> $supportedEvents Supported event names
     * @param int $priority Handler priority
     * @param bool $enabled Whether handler is enabled
     * @param array $metadata Handler metadata
     */
    public function __construct(
        string $id,
        callable $callable,
        array $supportedEvents = [],
        int $priority = 0,
        bool $enabled = true,
        array $metadata = []
    ) {
        $this->id = $id;
        $this->callable = $callable;
        $this->supportedEvents = $supportedEvents;
        $this->priority = $priority;
        $this->enabled = $enabled;
        $this->metadata = $metadata;
    }

    /**
     * Check if handler supports an event
     *

     * @param string $eventName The event name
     *

     * @return bool True if event is supported
     */
    public function supportsEvent(string $eventName): bool
    {
        return in_array($eventName, $this->supportedEvents, true);
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
