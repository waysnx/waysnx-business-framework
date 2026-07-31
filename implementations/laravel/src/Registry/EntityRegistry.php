<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Registry;

use WaysNX\BusinessFramework\Exceptions\DuplicateEntityException;
use WaysNX\BusinessFramework\Exceptions\EntityNotFoundException;
use WaysNX\BusinessFramework\Exceptions\RegistryException;

/**
 * EntityRegistry
 *
 * Central registry for managing entity type definitions.
 *
 * Purpose:
 * Serve as the single source of truth for all registered entity types within WBF.
 * Maintains a catalog of entity definitions used by Workflow Engine, Business Functions,
 * DQP, WaysNX Studio, CLI, and Code Generation.
 *
 * Responsibilities:
 * - Register entity definitions
 * - Unregister entities
 * - Lookup entities by ID, class, name, or alias
 * - Return all registered entities
 * - Check entity existence
 * - Manage entity metadata
 * - Support entity aliases
 * - Provide extension points for customization
 *
 * Usage:
 * ```php
 * $registry = new EntityRegistry();
 *
 * $registry->register(new EntityDefinition(
 *     id: 'project',
 *     name: 'Project',
 *     displayName: 'Business Project',
 *     className: 'App\\Models\\Project',
 *     namespace: 'App\\Models'
 * ));
 *
 * $definition = $registry->findById('project');
 * $definition = $registry->findByClass('App\\Models\\Project');
 * $definition = $registry->findByName('Project');
 *
 * $registry->alias('project', 'proj');
 * $definition = $registry->findByAlias('proj');
 *
 * $all = $registry->all();
 * $exists = $registry->exists('project');
 * ```
 *
 * Extension Points:
 * Child classes can override protected methods to add custom behavior:
 * - beforeRegister(): Called before entity registration
 * - afterRegister(): Called after entity registration
 * - beforeUnregister(): Called before entity removal
 * - afterUnregister(): Called after entity removal
 *
 * Key Features:
 * - Type-safe entity lookup by multiple criteria
 * - Support for entity aliasing
 * - Comprehensive validation
 * - Extension points for customization
 * - Lightweight, framework-independent implementation
 * - No business logic or workflows
 *
 * @package WaysNX\BusinessFramework\Registry
 */
class EntityRegistry
{
    /**
     * Registered entity definitions indexed by ID
     *
     * @var array<string, EntityDefinition>
     */
    protected array $entities = [];

    /**
     * Entity aliases mapping aliases to IDs
     *
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * Class to ID mapping for reverse lookup
     *
     * @var array<string, string>
     */
    protected array $classMap = [];

    /**
     * Name to ID mapping for reverse lookup
     *
     * @var array<string, string>
     */
    protected array $nameMap = [];

    /**
     * Register an entity definition
     *
     * Validates uniqueness of ID, class name, and entity name before registration.
     * Calls extension hooks before and after registration.
     *
     * @param EntityDefinition $definition The entity definition to register
     *
     * @return self Returns self for chaining
     *
     * @throws DuplicateEntityException When entity ID, class, or name already registered
     * @throws RegistryException When registration fails
     */
    public function register(EntityDefinition $definition): self
    {
        // Check for duplicate ID
        if (isset($this->entities[$definition->id])) {
            throw new DuplicateEntityException(
                "Entity with ID '{$definition->id}' is already registered"
            );
        }

        // Check for duplicate class
        if (isset($this->classMap[$definition->className])) {
            throw new DuplicateEntityException(
                "Entity class '{$definition->className}' is already registered"
            );
        }

        // Check for duplicate name
        if (isset($this->nameMap[$definition->name])) {
            throw new DuplicateEntityException(
                "Entity name '{$definition->name}' is already registered"
            );
        }

        // Call extension hook
        $this->beforeRegister($definition);

        // Register entity
        $this->entities[$definition->id] = $definition;
        $this->classMap[$definition->className] = $definition->id;
        $this->nameMap[$definition->name] = $definition->id;

        // Call extension hook
        $this->afterRegister($definition);

        return $this;
    }

    /**
     * Unregister an entity by ID
     *
     * Calls extension hooks before and after unregistration.
     *
     * @param string $id The entity ID to unregister
     *
     * @return self Returns self for chaining
     *
     * @throws EntityNotFoundException When entity not found
     * @throws RegistryException When unregistration fails
     */
    public function unregister(string $id): self
    {
        $definition = $this->findById($id);

        // Call extension hook
        $this->beforeUnregister($definition);

        // Unregister entity
        unset($this->entities[$id]);
        unset($this->classMap[$definition->className]);
        unset($this->nameMap[$definition->name]);

        // Remove aliases
        foreach ($this->aliases as $alias => $entityId) {
            if ($entityId === $id) {
                unset($this->aliases[$alias]);
            }
        }

        // Call extension hook
        $this->afterUnregister($definition);

        return $this;
    }

    /**
     * Find entity definition by ID
     *
     * @param string $id The entity ID
     *
     * @return EntityDefinition The entity definition
     *
     * @throws EntityNotFoundException When entity not found
     */
    public function findById(string $id): EntityDefinition
    {
        if (!isset($this->entities[$id])) {
            throw new EntityNotFoundException(
                "Entity with ID '{$id}' not found in registry"
            );
        }

        return $this->entities[$id];
    }

    /**
     * Find entity definition by class name
     *
     * @param string $className The fully qualified class name
     *

     * @return EntityDefinition The entity definition
     *

     * @throws EntityNotFoundException When entity not found
     */
    public function findByClass(string $className): EntityDefinition
    {
        if (!isset($this->classMap[$className])) {
            throw new EntityNotFoundException(
                "Entity with class '{$className}' not found in registry"
            );
        }

        $id = $this->classMap[$className];

        return $this->entities[$id];
    }

    /**
     * Find entity definition by name
     *

     * @param string $name The entity name
     *

     * @return EntityDefinition The entity definition
     *

     * @throws EntityNotFoundException When entity not found
     */
    public function findByName(string $name): EntityDefinition
    {
        if (!isset($this->nameMap[$name])) {
            throw new EntityNotFoundException(
                "Entity with name '{$name}' not found in registry"
            );
        }

        $id = $this->nameMap[$name];

        return $this->entities[$id];
    }

    /**
     * Find entity definition by alias
     *

     * @param string $alias The entity alias
     *

     * @return EntityDefinition The entity definition
     *

     * @throws EntityNotFoundException When alias or entity not found
     */
    public function findByAlias(string $alias): EntityDefinition
    {
        if (!isset($this->aliases[$alias])) {
            throw new EntityNotFoundException(
                "Entity alias '{$alias}' not found in registry"
            );
        }

        $id = $this->aliases[$alias];

        return $this->entities[$id];
    }

    /**
     * Get all registered entities
     *

     * @return array<string, EntityDefinition> All registered entity definitions indexed by ID
     */
    public function all(): array
    {
        return $this->entities;
    }

    /**
     * Get count of registered entities
     *

     * @return int The count of registered entities
     */
    public function count(): int
    {
        return count($this->entities);
    }

    /**
     * Check if entity exists by ID
     *

     * @param string $id The entity ID
     *

     * @return bool True if entity exists
     */
    public function exists(string $id): bool
    {
        return isset($this->entities[$id]);
    }

    /**
     * Check if class is registered
     *

     * @param string $className The fully qualified class name
     *

     * @return bool True if class is registered
     */
    public function classExists(string $className): bool
    {
        return isset($this->classMap[$className]);
    }

    /**
     * Check if entity name is registered
     *

     * @param string $name The entity name
     *

     * @return bool True if name is registered
     */
    public function nameExists(string $name): bool
    {
        return isset($this->nameMap[$name]);
    }

    /**
     * Check if alias exists
     *

     * @param string $alias The entity alias
     *

     * @return bool True if alias exists
     */
    public function aliasExists(string $alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    /**
     * Add an alias for an entity
     *

     * @param string $id The entity ID
     * @param string $alias The alias to add
     *

     * @return self Returns self for chaining
     *

     * @throws EntityNotFoundException When entity not found
     * @throws DuplicateEntityException When alias already exists
     */
    public function alias(string $id, string $alias): self
    {
        // Verify entity exists
        $this->findById($id);

        // Check for duplicate alias
        if (isset($this->aliases[$alias])) {
            throw new DuplicateEntityException(
                "Alias '{$alias}' is already registered"
            );
        }

        $this->aliases[$alias] = $id;

        return $this;
    }

    /**
     * Remove an alias
     *

     * @param string $alias The alias to remove
     *

     * @return self Returns self for chaining
     *

     * @throws EntityNotFoundException When alias not found
     */
    public function removeAlias(string $alias): self
    {
        if (!isset($this->aliases[$alias])) {
            throw new EntityNotFoundException(
                "Alias '{$alias}' not found in registry"
            );
        }

        unset($this->aliases[$alias]);

        return $this;
    }

    /**
     * Get all aliases for an entity
     *

     * @param string $id The entity ID
     *

     * @return array<string> All aliases for the entity
     *

     * @throws EntityNotFoundException When entity not found
     */
    public function getAliasesFor(string $id): array
    {
        // Verify entity exists
        $this->findById($id);

        $result = [];
        foreach ($this->aliases as $alias => $entityId) {
            if ($entityId === $id) {
                $result[] = $alias;
            }
        }

        return $result;
    }

    /**
     * Get all entities with a specific tag
     *

     * @param string $tag The tag to search for
     *

     * @return array<string, EntityDefinition> Entities with the tag indexed by ID
     */
    public function findByTag(string $tag): array
    {
        $result = [];
        foreach ($this->entities as $id => $definition) {
            if ($definition->hasTag($tag)) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get all experimental entities
     *

     * @return array<string, EntityDefinition> Experimental entities indexed by ID
     */
    public function experimental(): array
    {
        return $this->findByTag('experimental');
    }

    /**
     * Get all deprecated entities
     *

     * @return array<string, EntityDefinition> Deprecated entities indexed by ID
     */
    public function deprecated(): array
    {
        $result = [];
        foreach ($this->entities as $id => $definition) {
            if ($definition->isDeprecated()) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Clear all registered entities
     *

     * @return self Returns self for chaining
     */
    public function clear(): self
    {
        $this->entities = [];
        $this->aliases = [];
        $this->classMap = [];
        $this->nameMap = [];

        return $this;
    }

    /**
     * Before register extension point
     *

     * Called before an entity is registered.
     * Override in child classes to add custom behavior.
     *

     * @param EntityDefinition $definition The entity definition being registered
     *

     * @return void
     */
    protected function beforeRegister(EntityDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * After register extension point
     *

     * Called after an entity is registered.
     * Override in child classes to add custom behavior.
     *

     * @param EntityDefinition $definition The entity definition that was registered
     *

     * @return void
     */
    protected function afterRegister(EntityDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * Before unregister extension point
     *

     * Called before an entity is unregistered.
     * Override in child classes to add custom behavior.
     *

     * @param EntityDefinition $definition The entity definition being unregistered
     *

     * @return void
     */
    protected function beforeUnregister(EntityDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * After unregister extension point
     *

     * Called after an entity is unregistered.
     * Override in child classes to add custom behavior.
     *

     * @param EntityDefinition $definition The entity definition that was unregistered
     *

     * @return void
     */
    protected function afterUnregister(EntityDefinition $definition): void
    {
        // Default implementation does nothing
    }
}
