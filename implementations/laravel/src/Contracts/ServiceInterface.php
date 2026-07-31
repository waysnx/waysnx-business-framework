<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Contracts;

/**
 * ServiceInterface
 *
 * Generic service contract for all WBF services.
 *
 * Defines the common interface that all services must implement to provide
 * business orchestration and coordination of repository operations.
 *
 * Responsibilities:
 * - Define contract for entity manipulation (create, update, delete)
 * - Define contract for entity querying (find, all, paginate)
 * - Define contract for soft delete operations (delete, restore)
 * - Support lifecycle hooks for business logic
 * - Validate data before persistence
 *
 * Usage:
 * All concrete services (ProjectService, RequirementService, etc.)
 * should implement this interface and extend BaseService.
 *
 * @package WaysNX\BusinessFramework\Contracts
 */
interface ServiceInterface
{
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
     * @return mixed The updated entity
     */
    public function update(string|int $id, array $data): mixed;

    /**
     * Delete an entity
     *
     * @param string|int $id The entity ID
     *
     * @return bool True if deletion successful
     */
    public function delete(string|int $id): bool;

    /**
     * Restore a soft-deleted entity
     *
     * @param string|int $id The entity ID
     *
     * @return bool True if restoration successful
     */
    public function restore(string|int $id): bool;

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
     * Paginate entities
     *
     * @param int $perPage Number of items per page
     * @param int $page Page number
     *
     * @return array Paginated results
     */
    public function paginate(int $perPage = 15, int $page = 1): array;

    /**
     * Check if an entity exists
     *
     * @param string|int $id The entity ID
     *
     * @return bool True if entity exists
     */
    public function exists(string|int $id): bool;

    /**
     * Count all entities
     *
     * @return int The number of entities
     */
    public function count(): int;

    /**
     * Get the underlying repository
     *
     * @return RepositoryInterface The repository instance
     */
    public function getRepository(): RepositoryInterface;

    /**
     * Set the repository instance
     *
     * @param RepositoryInterface $repository The repository to use
     *
     * @return self For method chaining
     */
    public function setRepository(RepositoryInterface $repository): self;
}
