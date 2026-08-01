<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Lifecycle;

use WaysNX\BusinessFramework\Exceptions\DuplicateHandlerException;
use WaysNX\BusinessFramework\Exceptions\HandlerNotFoundException;
use WaysNX\BusinessFramework\Exceptions\LifecycleException;

/**
 * LifecycleManager
 *
 * Coordinates lifecycle events across WBF.
 *
 * Purpose:
 * Provide a common mechanism for framework components to participate in lifecycle events.
 * Manages handler registration, event dispatching, and execution ordering.
 *
 * Responsibilities:
 * - Register lifecycle handlers
 * - Unregister lifecycle handlers
 * - Check handler existence
 * - Retrieve registered handlers
 * - Return supported events
 * - Dispatch lifecycle events
 * - Execute handlers in priority order
 * - Manage handler priority
 * - Enable/disable handlers
 * - Clear all handlers
 * - Propagate lifecycle context
 *
 * Usage:
 * ```php
 * $manager = new LifecycleManager();
 *
 * // Register a handler
 * $handler = new LifecycleHandler(
 *     id: 'log-handler',
 *     callable: fn(LifecycleEvent $e, LifecycleContext $c) => logger()->info($e->name),
 *     supportedEvents: ['beforeCreate', 'afterCreate'],
 *     priority: 10
 * );
 * $manager->register($handler);
 *
 * // Dispatch an event
 * $event = new LifecycleEvent('beforeCreate');
 * $context = new LifecycleContext(entityId: '123', entityType: 'User');
 * $manager->dispatch($event, $context);
 *
 * // Check handler existence
 * if ($manager->exists('log-handler')) {
 *     $handlers = $manager->handlers('beforeCreate');
 * }
 * ```
 *
 * Extension Points:
 * - Subclass to add custom handler filtering
 * - Override dispatch() to add instrumentation
 * - Override executeHandler() to add error handling strategies
 *
 * @package WaysNX\BusinessFramework\Lifecycle
 */
class LifecycleManager
{
    /**
     * Registered handlers indexed by ID
     *
     * @var array<string, LifecycleHandler>
     */
    private array $handlers = [];

    /**
     * Handlers indexed by event name for fast lookup
     *
     * @var array<string, array<string>>
     */
    private array $eventIndex = [];

    /**
     * Register a lifecycle handler
     *
     * Adds a handler to the manager for the events it supports.
     * Handlers are registered by ID and indexed by supported events.
     *
     * @param LifecycleHandler $handler The handler to register
     *
     * @return self Returns self for method chaining
     *
     * @throws DuplicateHandlerException If handler ID already registered
     */
    public function register(LifecycleHandler $handler): self
    {
        if (isset($this->handlers[$handler->id])) {
            throw new DuplicateHandlerException(
                "Handler with ID '{$handler->id}' is already registered."
            );
        }

        $this->handlers[$handler->id] = $handler;

        // Index by supported events
        foreach ($handler->supportedEvents as $eventName) {
            if (!isset($this->eventIndex[$eventName])) {
                $this->eventIndex[$eventName] = [];
            }
            $this->eventIndex[$eventName][] = $handler->id;
        }

        return $this;
    }

    /**
     * Unregister a lifecycle handler
     *
     * Removes a handler from the manager and all event indices.
     *
     * @param string $handlerId The handler ID to remove
     *
     * @return self Returns self for method chaining
     *
     * @throws HandlerNotFoundException If handler ID not found
     */
    public function unregister(string $handlerId): self
    {
        if (!isset($this->handlers[$handlerId])) {
            throw new HandlerNotFoundException(
                "Handler with ID '{$handlerId}' not found."
            );
        }

        $handler = $this->handlers[$handlerId];
        unset($this->handlers[$handlerId]);

        // Remove from event indices
        foreach ($handler->supportedEvents as $eventName) {
            if (isset($this->eventIndex[$eventName])) {
                $this->eventIndex[$eventName] = array_filter(
                    $this->eventIndex[$eventName],
                    fn(string $id) => $id !== $handlerId
                );

                // Clean up empty indices
                if (empty($this->eventIndex[$eventName])) {
                    unset($this->eventIndex[$eventName]);
                }
            }
        }

        return $this;
    }

    /**
     * Check if a handler is registered
     *
     * @param string $handlerId The handler ID
     *
     * @return bool True if handler exists
     */
    public function exists(string $handlerId): bool
    {
        return isset($this->handlers[$handlerId]);
    }

    /**
     * Check if a handler is enabled
     *
     * @param string $handlerId The handler ID
     *
     * @return bool True if handler is enabled
     *
     * @throws HandlerNotFoundException If handler ID not found
     */
    public function isEnabled(string $handlerId): bool
    {
        if (!isset($this->handlers[$handlerId])) {
            throw new HandlerNotFoundException(
                "Handler with ID '{$handlerId}' not found."
            );
        }

        return $this->handlers[$handlerId]->enabled;
    }

    /**
     * Check if a handler is disabled
     *
     * @param string $handlerId The handler ID
     *
     * @return bool True if handler is disabled
     *
     * @throws HandlerNotFoundException If handler ID not found
     */
    public function isDisabled(string $handlerId): bool
    {
        return !$this->isEnabled($handlerId);
    }

    /**
     * Get all registered handlers for an event
     *
     * Returns all handlers supporting the given event, sorted by priority (descending).
     * If no event is specified, returns all handlers.
     *
     * @param string $eventName The event name (empty = all handlers)
     *
     * @return array<LifecycleHandler> Handlers sorted by priority
     */
    public function handlers(string $eventName = ''): array
    {
        if (empty($eventName)) {
            return array_values($this->handlers);
        }

        if (!isset($this->eventIndex[$eventName])) {
            return [];
        }

        $handlerIds = $this->eventIndex[$eventName];
        $handlers = [];

        foreach ($handlerIds as $handlerId) {
            $handlers[] = $this->handlers[$handlerId];
        }

        // Sort by priority (highest first)
        usort(
            $handlers,
            fn(LifecycleHandler $a, LifecycleHandler $b) => $b->priority <=> $a->priority
        );

        return $handlers;
    }

    /**
     * Get all supported event names
     *
     * @return array<string> Array of event names
     */
    public function supportedEvents(): array
    {
        return array_keys($this->eventIndex);
    }

    /**
     * Dispatch a lifecycle event
     *
     * Executes all registered handlers that support the event.
     * Handlers are executed in priority order (highest first).
     * Only enabled handlers are executed.
     *
     * @param LifecycleEvent $event The event to dispatch
     * @param LifecycleContext $context The execution context
     *
     * @return void
     *
     * @throws LifecycleException If handler execution fails
     */
    public function dispatch(LifecycleEvent $event, LifecycleContext $context): void
    {
        $handlers = $this->handlers($event->name);

        foreach ($handlers as $handler) {
            if (!$handler->enabled) {
                continue;
            }

            $this->executeHandler($handler, $event, $context);
        }
    }

    /**
     * Execute a single handler
     *
     * Invokes the handler callable with the event and context.
     * Wraps the invocation to catch and wrap any exceptions.
     *
     * @param LifecycleHandler $handler The handler to execute
     * @param LifecycleEvent $event The lifecycle event
     * @param LifecycleContext $context The execution context
     *
     * @return void
     *
     * @throws LifecycleException If handler execution fails
     */
    protected function executeHandler(
        LifecycleHandler $handler,
        LifecycleEvent $event,
        LifecycleContext $context
    ): void {
        try {
            ($handler->callable)($event, $context);
        } catch (\Throwable $e) {
            throw new LifecycleException(
                "Handler '{$handler->id}' failed while processing '{$event->name}': {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Clear all registered handlers
     *
     * Removes all handlers and indices.
     *
     * @return self Returns self for method chaining
     */
    public function clear(): self
    {
        $this->handlers = [];
        $this->eventIndex = [];

        return $this;
    }

    /**
     * Get total handler count
     *
     * @return int Total number of registered handlers
     */
    public function count(): int
    {
        return count($this->handlers);
    }

    /**
     * Check if any handlers are registered
     *
     * @return bool True if handlers exist
     */
    public function hasHandlers(): bool
    {
        return !empty($this->handlers);
    }

    /**
     * Check if handlers exist for an event
     *
     * @param string $eventName The event name
     *
     * @return bool True if handlers exist for event
     */
    public function hasHandlersFor(string $eventName): bool
    {
        return isset($this->eventIndex[$eventName]) && !empty($this->eventIndex[$eventName]);
    }

    /**
     * Get handler count for an event
     *
     * @param string $eventName The event name
     *
     * @return int Count of handlers supporting the event
     */
    public function handlerCountFor(string $eventName): int
    {
        return count($this->handlers($eventName));
    }
}
