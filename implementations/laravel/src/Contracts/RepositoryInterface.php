<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Contracts;

use Countable;
use IteratorAggregate;

/**
 * RepositoryInterface
 *
 * Generic repository contract for all WBF repositories.
 *
 * Defines the common interface that all repositories must implement to provide
 * CRUD operations, querying, and pagination functionality.
 *
 * Responsibilities:
 * - Define contract for entity retrieval (find, all, paginate)
 * - Define contract for entity persistence (create, update, delete)
 * - Define contract for entity counting and existence checking
 * - Define contract for query building
 * - Support soft deletes and restoration
 *
 * Usage:
 * All concrete repositories (ProjectRepository, RequirementRepository, etc.)
 * should implement this interface and extend BaseRepository.
 *
 * @package WaysNX\BusinessFramework\Contracts
 */
interface RepositoryInterface extends Countable, IteratorAggregate
{
    /**
     * Find an entity by ID
     *
     * @param string|int $id The entity ID
     *
     * @return mixed|null The entity or null if not found
     */
    public function find(string|int $id): mixed;

    /**
     * Find an entity by ID or throw an exception
     *
     * @param string|int $id The entity ID
     *
     * @return mixed The entity
     *
     * @throws \WaysNX\BusinessFramework\Exceptions\EntityNotFoundException
     */
    public function findOrFail(string|int $id): mixed;

    /**
     * Retrieve all entities
     *
     * @return array All entities
     */
    public function all(): array;

    /**
     * Create a new entity
     *
     * @param array $data The entity data
     *
     * @return mixed The created entity
     */
    public function create(array $data): mixed;

    /**
     * Update an entity
     *
     * @param string|int $id The entity ID
     * @param array $data The data to update
     *
     * @return bool True if update successful, false otherwise
     */
    public function update(string|int $id, array $data): bool;

    /**
     * Delete an entity (soft delete if supported)
     *
     * @param string|int $id The entity ID
     *
     * @return bool True if deletion successful, false otherwise
     */
    public function delete(string|int $id): bool;

    /**
     * Restore a soft-deleted entity
     *
     * @param string|int $id The entity ID
     *
     * @return bool True if restoration successful, false otherwise
     */
    public function restore(string|int $id): bool;

    /**
     * Paginate entities
     *
     * @param int $perPage Number of items per page
     * @param int $page Page number (defaults to 1)
     *
     * @return array Paginated results with metadata
     */
    public function paginate(int $perPage = 15, int $page = 1): array;

    /**
     * Check if an entity exists
     *
     * @param string|int $id The entity ID
     *
     * @return bool True if entity exists, false otherwise
     */
    public function exists(string|int $id): bool;

    /**
     * Count entities
     *
     * @return int The number of entities
     */
    public function count(): int;

    /**
     * Get the underlying query builder
     *
     * This allows direct query access for advanced operations.
     *
     * @return mixed The query builder instance
     */
    public function query(): mixed;

    /**
     * Create a new query instance
     *
     * @return mixed A fresh query instance
     */
    public function newQuery(): mixed;

    /**
     * Get the model instance
     *
     * @return mixed The model class being used
     */
    public function getModel(): mixed;

    /**
     * Set the model instance
     *
     * @param mixed $model The model to use
     *
     * @return self For method chaining
     */
    public function setModel(mixed $model): self;
}
