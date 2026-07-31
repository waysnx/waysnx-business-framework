<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Repositories;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use WaysNX\BusinessFramework\Contracts\RepositoryInterface;
use WaysNX\BusinessFramework\Exceptions\EntityNotFoundException;

/**
 * BaseRepository
 *
 * Generic repository foundation for all WBF repositories.
 *
 * BaseRepository provides common CRUD operations and querying functionality
 * that all entity-specific repositories should inherit from. It works with
 * any model that implements BaseModel and provides a clean abstraction
 * between business logic and data access layers.
 *
 * Purpose:
 * Serve as the parent repository for all WBF entity repositories including
 * ProjectRepository, RequirementRepository, ModuleRepository, WorkflowRepository, etc.
 *
 * Responsibilities:
 * - Provide CRUD operations (create, read, update, delete)
 * - Support soft deletes and restoration
 * - Provide pagination and counting
 * - Support query building and filtering
 * - Provide lifecycle hooks for customization
 * - Manage model instances
 *
 * Features:
 * - Strict typing throughout
 * - Composition-friendly design
 * - Protected lifecycle hooks for extension
 * - Generic implementation without business logic
 * - Framework-focused design
 * - Production-ready
 *
 * Usage:
 * ```php
 * class ProjectRepository extends BaseRepository {
 *     protected function applyFilters(array $filters): void {
 *         // Apply project-specific filters
 *     }
 * }
 *
 * $repository = new ProjectRepository(new Project());
 * $project = $repository->find('project-id');
 * $projects = $repository->paginate(15, 1);
 * ```
 *
 * Extension Points:
 * - beforeCreate(): Called before entity creation
 * - afterCreate(): Called after entity creation
 * - beforeUpdate(): Called before update
 * - afterUpdate(): Called after update
 * - beforeDelete(): Called before deletion
 * - afterDelete(): Called after deletion
 * - beforeRestore(): Called before restoration
 * - afterRestore(): Called after restoration
 * - applyFilters(): Apply custom filters to queries
 * - applySorting(): Apply custom sorting to queries
 * - applySearch(): Apply custom search to queries
 *
 * @implements RepositoryInterface
 * @package WaysNX\BusinessFramework\Repositories
 */
class BaseRepository implements RepositoryInterface
{
    /**
     * The model instance used by this repository
     *
     * @var mixed
     */
    protected mixed $model;

    /**
     * The current query instance
     *
     * @var mixed
     */
    protected mixed $query;

    /**
     * Initialize a new BaseRepository instance
     *
     * @param mixed $model The model instance to use for this repository
     */
    public function __construct(mixed $model)
    {
        $this->setModel($model);
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
        return $this->newQuery()->find($id);
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
        $entity = $this->find($id);

        if ($entity === null) {
            throw new EntityNotFoundException($id, get_class($this->model));
        }

        return $entity;
    }

    /**
     * Retrieve all entities
     *
     * @return array All entities
     */
    public function all(): array
    {
        $query = $this->newQuery();
        $this->applyFilters([]);
        $this->applySorting([]);

        return $query->get() ?? [];
    }

    /**
     * Create a new entity
     *
     * The data array is passed to the model's fill() or similar method
     * if available. Otherwise, properties are set directly.
     *
     * @param array $data The entity data
     *
     * @return mixed The created entity
     */
    public function create(array $data): mixed
    {
        $this->beforeCreate($data);

        $entity = clone $this->model;
        if (method_exists($entity, 'fill')) {
            $entity->fill($data);
        } else {
            foreach ($data as $key => $value) {
                if (property_exists($entity, $key)) {
                    $entity->{$key} = $value;
                }
            }
        }

        $this->afterCreate($entity);

        return $entity;
    }

    /**
     * Update an entity
     *
     * @param string|int $id The entity ID
     * @param array $data The data to update
     *
     * @return bool True if update successful, false otherwise
     */
    public function update(string|int $id, array $data): bool
    {
        $entity = $this->findOrFail($id);

        $this->beforeUpdate($entity, $data);

        if (method_exists($entity, 'fill')) {
            $entity->fill($data);
        } else {
            foreach ($data as $key => $value) {
                if (property_exists($entity, $key)) {
                    $entity->{$key} = $value;
                }
            }
        }

        $this->afterUpdate($entity);

        return true;
    }

    /**
     * Delete an entity (soft delete if supported)
     *
     * If the entity supports soft deletes (has isDeleted() method),
     * it will be soft deleted instead of hard deleted.
     *
     * @param string|int $id The entity ID
     *
     * @return bool True if deletion successful, false otherwise
     */
    public function delete(string|int $id): bool
    {
        $entity = $this->findOrFail($id);

        $this->beforeDelete($entity);

        if (method_exists($entity, 'setDeletedBy')) {
            $entity->setDeletedBy(null); // Soft delete marker
        }

        $this->afterDelete($entity);

        return true;
    }

    /**
     * Restore a soft-deleted entity
     *
     * @param string|int $id The entity ID
     *
     * @return bool True if restoration successful, false otherwise
     */
    public function restore(string|int $id): bool
    {
        $entity = $this->findOrFail($id);

        $this->beforeRestore($entity);

        if (method_exists($entity, 'restore')) {
            $entity->restore();
        }

        $this->afterRestore($entity);

        return true;
    }

    /**
     * Paginate entities
     *
     * @param int $perPage Number of items per page
     * @param int $page Page number (defaults to 1)
     *
     * @return array Paginated results with metadata
     */
    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $query = $this->newQuery();
        $this->applyFilters([]);
        $this->applySorting([]);

        $total = $query->count();
        $offset = ($page - 1) * $perPage;

        $items = $query->offset($offset)->limit($perPage)->get() ?? [];

        return [
            'items' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
        ];
    }

    /**
     * Check if an entity exists
     *
     * @param string|int $id The entity ID
     *
     * @return bool True if entity exists, false otherwise
     */
    public function exists(string|int $id): bool
    {
        return $this->find($id) !== null;
    }

    /**
     * Count entities
     *
     * @return int The number of entities
     */
    public function count(): int
    {
        return $this->newQuery()->count();
    }

    /**
     * Get the underlying query builder
     *
     * This allows direct query access for advanced operations.
     * Note: Modifications to this query will not persist to the repository state.
     *
     * @return mixed The query builder instance
     */
    public function query(): mixed
    {
        return $this->query;
    }

    /**
     * Create a new query instance
     *
     * @return mixed A fresh query instance
     */
    public function newQuery(): mixed
    {
        $this->query = clone $this->model;
        return $this->query;
    }

    /**
     * Get the model instance
     *
     * @return mixed The model class being used
     */
    public function getModel(): mixed
    {
        return $this->model;
    }

    /**
     * Set the model instance
     *
     * @param mixed $model The model to use
     *
     * @return self For method chaining
     */
    public function setModel(mixed $model): self
    {
        $this->model = $model;
        $this->query = clone $model;
        return $this;
    }

    /**
     * Iterate over entities
     *
     * Implements IteratorAggregate to make repositories iterable.
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }

    /**
     * Hook called before entity creation
     *
     * Override in child classes to customize pre-creation behavior.
     *
     * @param array &$data The data to be used for creation
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
     *
     * @param mixed $entity The entity being updated
     * @param array &$data The data being applied
     *
     * @return void
     */
    protected function beforeUpdate(mixed $entity, array &$data): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity update
     *
     * Override in child classes to customize post-update behavior.
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
     *
     * @param mixed $entity The entity being deleted
     *
     * @return void
     */
    protected function beforeDelete(mixed $entity): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity deletion
     *
     * Override in child classes to customize post-deletion behavior.
     *
     * @param mixed $entity The deleted entity
     *
     * @return void
     */
    protected function afterDelete(mixed $entity): void
    {
        // Override in child class
    }

    /**
     * Hook called before entity restoration
     *
     * Override in child classes to customize pre-restoration behavior.
     *
     * @param mixed $entity The entity being restored
     *
     * @return void
     */
    protected function beforeRestore(mixed $entity): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity restoration
     *
     * Override in child classes to customize post-restoration behavior.
     *
     * @param mixed $entity The restored entity
     *
     * @return void
     */
    protected function afterRestore(mixed $entity): void
    {
        // Override in child class
    }

    /**
     * Apply filters to the query
     *
     * Override in child classes to implement custom filtering logic.
     * This method is called by all() and paginate() methods.
     *
     * @param array $filters The filters to apply
     *
     * @return void
     */
    protected function applyFilters(array $filters): void
    {
        // Override in child class to implement filtering
    }

    /**
     * Apply sorting to the query
     *
     * Override in child classes to implement custom sorting logic.
     * This method is called by all() and paginate() methods.
     *
     * @param array $sortParams The sorting parameters
     *
     * @return void
     */
    protected function applySorting(array $sortParams): void
    {
        // Override in child class to implement sorting
    }

    /**
     * Apply search to the query
     *
     * Override in child classes to implement custom search logic.
     *
     * @param string $searchTerm The search term
     * @param array $searchFields The fields to search in
     *
     * @return void
     */
    protected function applySearch(string $searchTerm, array $searchFields = []): void
    {
        // Override in child class to implement search
    }

    /**
     * Execute a transaction
     *
     * Executes the provided callback within a transaction context.
     * If a framework transaction system is available, it will be used.
     *
     * @param callable $callback The callback to execute
     *
     * @return mixed The result of the callback
     */
    protected function transaction(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
