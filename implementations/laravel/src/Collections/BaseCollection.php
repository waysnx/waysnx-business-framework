<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Collections;

use ArrayIterator;
use Traversable;
use WaysNX\BusinessFramework\Contracts\CollectionInterface;

/**
 * BaseCollection
 *
 * Generic collection foundation for all WBF collections.
 *
 * BaseCollection provides a consistent abstraction for working with groups
 * of WBF entities. It supports filtering, mapping, grouping, sorting, and
 * other common collection operations while carrying optional metadata.
 *
 * Purpose:
 * Serve as the parent collection for all WBF entity collections including
 * ProjectCollection, RequirementCollection, WorkflowCollection,
 * BusinessFunctionCollection, ModuleCollection, TaskCollection,
 * DocumentCollection, and all future collection types.
 *
 * Responsibilities:
 * - Provide collection operations (filter, map, reduce, groupBy, sort)
 * - Provide collection access (first, last, get, contains)
 * - Provide collection queries (count, isEmpty)
 * - Provide collection transformations (pluck, values, keys)
 * - Support metadata carrying (pagination, filters, sorting, context)
 * - Support serialization (toArray, toJson)
 * - Provide iteration support
 *
 * Features:
 * - Strict typing throughout
 * - Framework-independent design
 * - Lightweight implementation
 * - Chainable methods where appropriate
 * - Extension points via protected methods
 * - Generic, no business logic
 * - Production-ready
 *
 * Usage:
 * ```php
 * $collection = new BaseCollection([
 *     $entity1, $entity2, $entity3
 * ]);
 *
 * $filtered = $collection
 *     ->filter(fn($item) => $item->isActive())
 *     ->sort(fn($a, $b) => $a->getName() <=> $b->getName());
 *
 * $first = $collection->first();
 * $count = $collection->count();
 * $json = $collection->toJson();
 * ```
 *
 * Metadata Usage:
 * ```php
 * $collection->setMetadata([
 *     'pagination' => ['page' => 1, 'per_page' => 15, 'total' => 100],
 *     'filters' => ['status' => 'active'],
 *     'sorting' => ['by' => 'created_at', 'direction' => 'desc']
 * ]);
 * ```
 *
 * Extension Points:
 * - beforeTransform(): Called before transformation operations
 * - afterTransform(): Called after transformation operations
 * - beforeSerialize(): Called before serialization
 * - afterSerialize(): Called after serialization
 *
 * @implements CollectionInterface
 * @package WaysNX\BusinessFramework\Collections
 */
class BaseCollection implements CollectionInterface
{
    /**
     * The items in the collection
     *
     * @var array
     */
    protected array $items = [];

    /**
     * Collection metadata
     *
     * @var array
     */
    protected array $metadata = [];

    /**
     * Initialize a new BaseCollection instance
     *
     * @param array $items Initial items
     * @param array $metadata Initial metadata
     */
    public function __construct(array $items = [], array $metadata = [])
    {
        $this->items = array_values($items);
        $this->metadata = $metadata;
    }

    /**
     * Add an item to the collection
     *
     * @param mixed $item The item to add
     *
     * @return self For method chaining
     */
    public function add(mixed $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Remove an item from the collection
     *
     * @param mixed $item The item to remove
     *

     * @return self For method chaining
     */
    public function remove(mixed $item): self
    {
        $this->items = array_filter($this->items, fn($value) => $value !== $item);
        return $this;
    }

    /**
     * Filter the collection
     *

     * @param callable $callback The filter callback
     *

     * @return CollectionInterface A new filtered collection
     */
    public function filter(callable $callback): CollectionInterface
    {
        $this->beforeTransform();

        $filtered = array_filter($this->items, $callback);
        $collection = new self(array_values($filtered), $this->metadata);

        $this->afterTransform();

        return $collection;
    }

    /**
     * Map over the collection
     *

     * @param callable $callback The mapping callback
     *

     * @return CollectionInterface A new mapped collection
     */
    public function map(callable $callback): CollectionInterface
    {
        $this->beforeTransform();

        $mapped = array_map($callback, $this->items);
        $collection = new self($mapped, $this->metadata);

        $this->afterTransform();

        return $collection;
    }

    /**
     * Reduce the collection to a single value
     *

     * @param callable $callback The reduction callback
     * @param mixed $initial The initial value
     *

     * @return mixed The reduced value
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Group collection items by a key or callback
     *

     * @param string|callable $key The grouping key or callback
     *

     * @return array Grouped items as array
     */
    public function groupBy(string|callable $key): array
    {
        $grouped = [];

        foreach ($this->items as $item) {
            if (is_callable($key)) {
                $groupKey = $key($item);
            } else {
                $groupKey = $item->{$key} ?? null;
            }

            $grouped[$groupKey][] = $item;
        }

        return $grouped;
    }

    /**
     * Sort the collection
     *

     * @param callable|null $callback The sorting callback
     *

     * @return CollectionInterface A new sorted collection
     */
    public function sort(?callable $callback = null): CollectionInterface
    {
        $this->beforeTransform();

        $sorted = $this->items;

        if ($callback === null) {
            sort($sorted);
        } else {
            usort($sorted, $callback);
        }

        $collection = new self($sorted, $this->metadata);

        $this->afterTransform();

        return $collection;
    }

    /**
     * Get the first item
     *

     * @param callable|null $callback Optional filter callback
     *

     * @return mixed|null The first item or null
     */
    public function first(?callable $callback = null): mixed
    {
        if ($callback === null) {
            return $this->items[0] ?? null;
        }

        foreach ($this->items as $item) {
            if ($callback($item)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Get the last item
     *

     * @param callable|null $callback Optional filter callback
     *

     * @return mixed|null The last item or null
     */
    public function last(?callable $callback = null): mixed
    {
        if ($callback === null) {
            return end($this->items) ?: null;
        }

        $last = null;
        foreach ($this->items as $item) {
            if ($callback($item)) {
                $last = $item;
            }
        }

        return $last;
    }

    /**
     * Get item at index
     *

     * @param int $index The index
     *

     * @return mixed|null The item or null
     */
    public function get(int $index): mixed
    {
        return $this->items[$index] ?? null;
    }

    /**
     * Check if collection contains an item
     *

     * @param mixed $item The item to check
     *

     * @return bool True if item exists
     */
    public function contains(mixed $item): bool
    {
        return in_array($item, $this->items, true);
    }

    /**
     * Check if collection is empty
     *

     * @return bool True if empty
     */
    public function isEmpty(): bool
    {
        return count($this->items) === 0;
    }

    /**
     * Get all items as array
     *

     * @return array The items array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Get collection values (alias for all)
     *

     * @return array The values
     */
    public function values(): array
    {
        return array_values($this->items);
    }

    /**
     * Get collection keys
     *

     * @return array The keys
     */
    public function keys(): array
    {
        return array_keys($this->items);
    }

    /**
     * Pluck values from items
     *

     * @param string $key The key to pluck
     *

     * @return array Plucked values
     */
    public function pluck(string $key): array
    {
        $plucked = [];
        foreach ($this->items as $item) {
            if (is_array($item) && isset($item[$key])) {
                $plucked[] = $item[$key];
            } elseif (is_object($item) && isset($item->{$key})) {
                $plucked[] = $item->{$key};
            }
        }
        return $plucked;
    }

    /**
     * Iterate over items
     *

     * @param callable $callback The iteration callback
     *

     * @return self For method chaining
     */
    public function each(callable $callback): self
    {
        foreach ($this->items as $item) {
            $callback($item);
        }
        return $this;
    }

    /**
     * Merge another collection
     *

     * @param CollectionInterface $collection The collection to merge
     *

     * @return CollectionInterface A new merged collection
     */
    public function merge(CollectionInterface $collection): CollectionInterface
    {
        $merged = array_merge($this->items, $collection->all());
        return new self($merged, $this->metadata);
    }

    /**
     * Convert collection to array
     *

     * @return array The collection as array
     */
    public function toArray(): array
    {
        $this->beforeSerialize();

        $array = array_map(function ($item) {
            if (method_exists($item, 'toArray')) {
                return $item->toArray();
            } elseif (is_array($item)) {
                return $item;
            }
            return (array) $item;
        }, $this->items);

        $this->afterSerialize();

        return $array;
    }

    /**
     * Convert collection to JSON
     *

     * @return string The collection as JSON
     */
    public function toJson(): string
    {
        return (string) json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * Get collection count
     *

     * @return int The number of items
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Get collection metadata
     *

     * @return array The metadata
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Set collection metadata
     *

     * @param array $metadata The metadata
     *

     * @return self For method chaining
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Get a metadata value
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
     * Set a metadata value
     *

     * @param string $key The metadata key
     * @param mixed $value The value
     *

     * @return self For method chaining
     */
    public function setMetadataValue(string $key, mixed $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    /**
     * Get iterator for the collection
     *

     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Specify data which should be serialized to JSON
     *

     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Hook called before transformation operations
     *

     * Override in child classes to customize pre-transformation behavior.
     *

     * @return void
     */
    protected function beforeTransform(): void
    {
        // Override in child class
    }

    /**
     * Hook called after transformation operations
     *

     * Override in child classes to customize post-transformation behavior.
     *

     * @return void
     */
    protected function afterTransform(): void
    {
        // Override in child class
    }

    /**
     * Hook called before serialization
     *

     * Override in child classes to customize pre-serialization behavior.
     *

     * @return void
     */
    protected function beforeSerialize(): void
    {
        // Override in child class
    }

    /**
     * Hook called after serialization
     *

     * Override in child classes to customize post-serialization behavior.
     *

     * @return void
     */
    protected function afterSerialize(): void
    {
        // Override in child class
    }
}
