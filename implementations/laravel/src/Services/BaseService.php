<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Services;

use WaysNX\BusinessFramework\Contracts\RepositoryInterface;
use WaysNX\BusinessFramework\Contracts\ServiceInterface;
use WaysNX\BusinessFramework\Exceptions\EntityNotFoundException;

/**
 * BaseService
 *
 * Generic service foundation for all WBF services.
 *
 * BaseService provides a business orchestration layer that coordinates
 * repository operations with validation and lifecycle management. It serves
 * as the parent class for all entity-specific services, enabling consistent
 * handling of business logic across the application.
 *
 * Purpose:
 * Serve as the parent service for all WBF entity services including ProjectService,
 * RequirementService, ModuleService, WorkflowService, BusinessFunctionService,
 * DocumentService, TaskService, and all future service types.
 *
 * Responsibilities:
 * - Coordinate repository operations
 * - Manage business orchestration
 * - Provide validation hooks
 * - Provide lifecycle hooks for customization
 * - Handle service-level exceptions
 * - Support transaction management
 *
 * Features:
 * - Strict typing throughout
 * - Constructor dependency injection
 * - Protected lifecycle hooks for extension
 * - Validation extension points
 * - Repository abstraction (no concrete dependencies)
 * - Generic implementation without business logic
 * - Production-ready
 *
 * Architecture Pattern:
 * Controllers → Services → Repositories → Models
 *
 * Services never:
 * - Access database directly
 * - Implement persistence logic
 * - Duplicate repository methods
 * - Create concrete repositories
 *
 * Services always:
 * - Coordinate repository calls
 * - Validate input data
 * - Execute business logic
 * - Manage transactions
 * - Provide extension points
 *
 * Usage:
 * ```php
 * class ProjectService extends BaseService {
 *     protected function validateCreate(array &$data): void {
 *         // Validate project-specific data
 *     }
 * }
 *
 * $service = new ProjectService($repository);
 * $project = $service->create(['name' => 'Q1 Roadmap']);
 * ```
 *
 * Extension Points:
 * Lifecycle Hooks:
 * - beforeCreate(): Called before create operation
 * - afterCreate(): Called after create operation
 * - beforeUpdate(): Called before update operation
 * - afterUpdate(): Called after update operation
 * - beforeDelete(): Called before delete operation
 * - afterDelete(): Called after delete operation
 * - beforeRestore(): Called before restore operation
 * - afterRestore(): Called after restore operation
 *
 * Validation Hooks:
 * - validateCreate(): Validate data before creation
 * - validateUpdate(): Validate data before update
 *
 * @implements ServiceInterface
 * @package WaysNX\BusinessFramework\Services
 */
class BaseService implements ServiceInterface
{
    /**
     * The repository instance used by this service
     *
     * @var RepositoryInterface
     */
    protected RepositoryInterface $repository;

    /**
     * Initialize a new BaseService instance
     *
     * @param RepositoryInterface $repository The repository to use for data access
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new entity
     *
     * Orchestrates the creation process by validating input data,
     * executing lifecycle hooks, and delegating to the repository.
     *
     * @param array $data The entity data
     *
     * @return mixed The created entity
     */
    public function create(array $data): mixed
    {
        // Validate input data
        $this->validateCreate($data);

        // Pre-creation hook
        $this->beforeCreate($data);

        // Delegate to repository
        $entity = $this->repository->create($data);

        // Post-creation hook
        $this->afterCreate($entity);

        return $entity;
    }

    /**
     * Update an entity
     *
     * Orchestrates the update process by validating input data,
     * executing lifecycle hooks, and delegating to the repository.
     *
     * @param string|int $id The entity ID
     * @param array $data The data to update
     *
     * @return mixed The updated entity
     *
     * @throws EntityNotFoundException
     */
    public function update(string|int $id, array $data): mixed
    {
        // Validate input data
        $this->validateUpdate($data);

        // Pre-update hook
        $this->beforeUpdate($id, $data);

        // Delegate to repository
        $this->repository->update($id, $data);

        // Retrieve updated entity
        $entity = $this->repository->findOrFail($id);

        // Post-update hook
        $this->afterUpdate($entity);

        return $entity;
    }

    /**
     * Delete an entity
     *
     * Orchestrates the deletion process by executing lifecycle hooks
     * and delegating to the repository.
     *
     * @param string|int $id The entity ID
     *
     * @return bool True if deletion successful
     *
     * @throws EntityNotFoundException
     */
    public function delete(string|int $id): bool
    {
        // Pre-delete hook
        $this->beforeDelete($id);

        // Delegate to repository
        $result = $this->repository->delete($id);

        // Post-delete hook
        $this->afterDelete($id);

        return $result;
    }

    /**
     * Restore a soft-deleted entity
     *
     * Orchestrates the restoration process by executing lifecycle hooks
     * and delegating to the repository.
     *
     * @param string|int $id The entity ID
     *
     * @return bool True if restoration successful
     *
     * @throws EntityNotFoundException
     */
    public function restore(string|int $id): bool
    {
        // Pre-restore hook
        $this->beforeRestore($id);

        // Delegate to repository
        $result = $this->repository->restore($id);

        // Post-restore hook
        $this->afterRestore($id);

        return $result;
    }

    /**
     * Find an entity by ID
     *
     * @param string|int $id The entity ID
     *
     * @return mixed|null The entity or null if not found
     */
    public function find(string|int $id): mixed
    {
        return $this->repository->find($id);
    }

    /**
     * Find an entity by ID or throw an exception
     *
     * @param string|int $id The entity ID
     *
     * @return mixed The entity
     *
     * @throws EntityNotFoundException
     */
    public function findOrFail(string|int $id): mixed
    {
        return $this->repository->findOrFail($id);
    }

    /**
     * Retrieve all entities
     *
     * @return array All entities
     */
    public function all(): array
    {
        return $this->repository->all();
    }

    /**
     * Paginate entities
     *

     * @param int $perPage Number of items per page
     * @param int $page Page number
     *
     * @return array Paginated results with metadata
     */
    public function paginate(int $perPage = 15, int $page = 1): array
    {
        return $this->repository->paginate($perPage, $page);
    }

    /**
     * Check if an entity exists
     *
     * @param string|int $id The entity ID
     *
     * @return bool True if entity exists
     */
    public function exists(string|int $id): bool
    {
        return $this->repository->exists($id);
    }

    /**
     * Count all entities
     *
     * @return int The number of entities
     */
    public function count(): int
    {
        return $this->repository->count();
    }

    /**
     * Get the underlying repository
     *
     * @return RepositoryInterface The repository instance
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    /**
     * Set the repository instance
     *

     * @param RepositoryInterface $repository The repository to use
     *
     * @return self For method chaining
     */
    public function setRepository(RepositoryInterface $repository): self
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * Hook called before entity creation
     *
     * Override in child classes to customize pre-creation behavior.
     * This hook is called after validation but before persistence.
     *
     * @param array &$data The data to be created (pass by reference for modification)
     *
     * @return void
     */
    protected function beforeCreate(array &$data): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity creation
     *
     * Override in child classes to customize post-creation behavior.
     * This hook is called after the entity is persisted.
     *
     * @param mixed $entity The created entity
     *
     * @return void
     */
    protected function afterCreate(mixed $entity): void
    {
        // Override in child class
    }

    /**
     * Hook called before entity update
     *
     * Override in child classes to customize pre-update behavior.
     * This hook is called after validation but before persistence.
     *
     * @param string|int $id The entity ID
     * @param array &$data The data being applied (pass by reference for modification)
     *
     * @return void
     */
    protected function beforeUpdate(string|int $id, array &$data): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity update
     *
     * Override in child classes to customize post-update behavior.
     * This hook is called after the entity is updated.
     *
     * @param mixed $entity The updated entity
     *
     * @return void
     */
    protected function afterUpdate(mixed $entity): void
    {
        // Override in child class
    }

    /**
     * Hook called before entity deletion
     *
     * Override in child classes to customize pre-deletion behavior.
     * This hook is called before the entity is deleted.
     *
     * @param string|int $id The entity ID
     *
     * @return void
     */
    protected function beforeDelete(string|int $id): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity deletion
     *
     * Override in child classes to customize post-deletion behavior.
     * This hook is called after the entity is deleted.
     *
     * @param string|int $id The entity ID
     *
     * @return void
     */
    protected function afterDelete(string|int $id): void
    {
        // Override in child class
    }

    /**
     * Hook called before entity restoration
     *
     * Override in child classes to customize pre-restoration behavior.
     * This hook is called before the entity is restored.
     *
     * @param string|int $id The entity ID
     *
     * @return void
     */
    protected function beforeRestore(string|int $id): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity restoration
     *
     * Override in child classes to customize post-restoration behavior.
     * This hook is called after the entity is restored.
     *
     * @param string|int $id The entity ID
     *
     * @return void
     */
    protected function afterRestore(string|int $id): void
    {
        // Override in child class
    }

    /**
     * Validation hook for create operation
     *
     * Override in child classes to implement creation-specific validation.
     * Should throw an exception if validation fails.
     *
     * @param array &$data The data to validate (pass by reference for modification)
     *
     * @return void
     */
    protected function validateCreate(array &$data): void
    {
        // Override in child class to implement validation
    }

    /**
     * Validation hook for update operation
     *
     * Override in child classes to implement update-specific validation.
     * Should throw an exception if validation fails.
     *
     * @param array &$data The data to validate (pass by reference for modification)
     *
     * @return void
     */
    protected function validateUpdate(array &$data): void
    {
        // Override in child class to implement validation
    }

    /**
     * Execute a transaction
     *
     * Wraps the provided callback in a transaction.
     * Uses the repository's transaction support if available.
     *
     * @param callable $callback The callback to execute
     *
     * @return mixed The result of the callback
     */
    protected function transaction(callable $callback): mixed
    {
        if (method_exists($this->repository, 'transaction')) {
            return $this->repository->transaction($callback);
        }

        try {
            return $callback();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
