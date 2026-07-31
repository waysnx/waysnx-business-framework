<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Contracts;

use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * CollectionInterface
 *
 * Generic collection contract for all WBF collections.
 *
 * Defines the common interface that all collections must implement to provide
 * consistent collection operations across WBF entities.
 *
 * Responsibilities:
 * - Define contract for collection manipulation (add, remove, filter)
 * - Define contract for collection queries (count, contains, isEmpty)
 * - Define contract for collection transformation (map, groupBy, sort)
 * - Define contract for collection access (first, last, get, all)
 * - Support metadata carrying
 * - Support serialization
 *
 * Usage:
 * All concrete collections (ProjectCollection, RequirementCollection, etc.)
 * should implement this interface and extend BaseCollection.
 *
 * @package WaysNX\BusinessFramework\Contracts
 */
interface CollectionInterface extends Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Add an item to the collection
     *
     * @param mixed $item The item to add
     *
     * @return self For method chaining
     */
    public function add(mixed $item): self;

    /**
     * Remove an item from the collection
     *
     * @param mixed $item The item to remove
     *
     * @return self For method chaining
     */
    public function remove(mixed $item): self;

    /**
     * Filter the collection
     *
     * @param callable $callback The filter callback
     *
     * @return CollectionInterface A new filtered collection
     */
    public function filter(callable $callback): CollectionInterface;

    /**
     * Map over the collection
     *
     * @param callable $callback The mapping callback
     *
     * @return CollectionInterface A new mapped collection
     */
    public function map(callable $callback): CollectionInterface;

    /**
     * Reduce the collection to a single value
     *

     * @param callable $callback The reduction callback
     * @param mixed $initial The initial value
     *

     * @return mixed The reduced value
     */
    public function reduce(callable $callback, mixed $initial = null): mixed;

    /**
     * Group collection items by a key or callback
     *

     * @param string|callable $key The grouping key or callback
     *

     * @return array Grouped items as array
     */
    public function groupBy(string|callable $key): array;

    /**
     * Sort the collection
     *

     * @param callable|null $callback The sorting callback
     *

     * @return CollectionInterface A new sorted collection
     */
    public function sort(?callable $callback = null): CollectionInterface;

    /**
     * Get the first item
     *

     * @param callable|null $callback Optional filter callback
     *

     * @return mixed|null The first item or null
     */
    public function first(?callable $callback = null): mixed;

    /**
     * Get the last item
     *

     * @param callable|null $callback Optional filter callback
     *

     * @return mixed|null The last item or null
     */
    public function last(?callable $callback = null): mixed;

    /**
     * Get item at index
     *

     * @param int $index The index
     *

     * @return mixed|null The item or null
     */
    public function get(int $index): mixed;

    /**
     * Check if collection contains an item
     *

     * @param mixed $item The item to check
     *

     * @return bool True if item exists
     */
    public function contains(mixed $item): bool;

    /**
     * Check if collection is empty
     *

     * @return bool True if empty
     */
    public function isEmpty(): bool;

    /**
     * Get all items as array
     *

     * @return array The items array
     */
    public function all(): array;

    /**
     * Get collection values
     *

     * @return array The values
     */
    public function values(): array;

    /**
     * Get collection keys
     *

     * @return array The keys
     */
    public function keys(): array;

    /**
     * Pluck values from items
     *

     * @param string $key The key to pluck
     *

     * @return array Plucked values
     */
    public function pluck(string $key): array;

    /**
     * Iterate over items
     *

     * @param callable $callback The iteration callback
     *

     * @return self For method chaining
     */
    public function each(callable $callback): self;

    /**
     * Merge another collection
     *

     * @param CollectionInterface $collection The collection to merge
     *

     * @return CollectionInterface A new merged collection
     */
    public function merge(CollectionInterface $collection): CollectionInterface;

    /**
     * Convert collection to array
     *

     * @return array The collection as array
     */
    public function toArray(): array;

    /**
     * Convert collection to JSON
     *

     * @return string The collection as JSON
     */
    public function toJson(): string;

    /**
     * Get collection count
     *

     * @return int The number of items
     */
    public function count(): int;

    /**
     * Get collection metadata
     *

     * @return array The metadata
     */
    public function getMetadata(): array;

    /**
     * Set collection metadata
     *

     * @param array $metadata The metadata
     *

     * @return self For method chaining
     */
    public function setMetadata(array $metadata): self;

    /**
     * Get a metadata value
     *

     * @param string $key The metadata key
     * @param mixed $default Default value
     *

     * @return mixed The metadata value
     */
    public function getMetadataValue(string $key, mixed $default = null): mixed;

    /**
     * Set a metadata value
     *

     * @param string $key The metadata key
     * @param mixed $value The value
     *

     * @return self For method chaining
     */
    public function setMetadataValue(string $key, mixed $value): self;
}
