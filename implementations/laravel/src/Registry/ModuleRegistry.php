<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Registry;

use WaysNX\BusinessFramework\Exceptions\DuplicateModuleException;
use WaysNX\BusinessFramework\Exceptions\ModuleNotFoundException;
use WaysNX\BusinessFramework\Exceptions\ModuleRegistryException;

/**
 * ModuleRegistry
 *
 * Central registry for managing module definitions.
 *
 * Purpose:
 * Serve as the authoritative catalog of all registered modules within WBF.
 * Maintains module definitions, dependencies, and metadata used by the framework
 * and downstream systems including Workflow Engine, Business Functions, DQP, and Studio.
 *
 * Responsibilities:
 * - Register module definitions
 * - Unregister modules
 * - Lookup modules by ID or name
 * - Return all registered modules
 * - Check module existence
 * - Manage module metadata
 * - Query module dependencies and dependents
 * - Filter modules by enabled/disabled state
 * - Provide extension points for customization
 *
 * Usage:
 * ```php
 * $registry = new ModuleRegistry();
 *
 * $registry->register(new ModuleDefinition(
 *     id: 'project-management',
 *     name: 'ProjectManagement',
 *     displayName: 'Project Management',
 *     namespace: 'App\\Modules\\ProjectManagement',
 *     dependencies: ['core', 'user']
 * ));
 *
 * $module = $registry->findById('project-management');
 * $module = $registry->findByName('ProjectManagement');
 *
 * $all = $registry->all();
 * $enabled = $registry->enabled();
 * $dependents = $registry->dependents('core');
 * ```
 *
 * Extension Points:
 * Child classes can override protected methods to add custom behavior:
 * - beforeRegister(): Called before module registration
 * - afterRegister(): Called after module registration
 * - beforeUnregister(): Called before module removal
 * - afterUnregister(): Called after module removal
 *
 * Key Features:
 * - Type-safe module lookup by multiple criteria
 * - Dependency management and querying
 * - Enabled/disabled state tracking
 * - Comprehensive validation
 * - Extension points for customization
 * - Lightweight, framework-independent implementation
 * - No business logic or module loading
 *
 * @package WaysNX\BusinessFramework\Registry
 */
class ModuleRegistry
{
    /**
     * Registered module definitions indexed by ID
     *
     * @var array<string, ModuleDefinition>
     */
    protected array $modules = [];

    /**
     * Name to ID mapping for reverse lookup
     *
     * @var array<string, string>
     */
    protected array $nameMap = [];

    /**
     * Namespace to ID mapping for reverse lookup
     *
     * @var array<string, string>
     */
    protected array $namespaceMap = [];

    /**
     * Register a module definition
     *
     * Validates uniqueness of ID, name, and namespace before registration.
     * Calls extension hooks before and after registration.
     *
     * @param ModuleDefinition $definition The module definition to register
     *
     * @return self Returns self for chaining
     *
     * @throws DuplicateModuleException When module ID, name, or namespace already registered
     * @throws ModuleRegistryException When registration fails
     */
    public function register(ModuleDefinition $definition): self
    {
        // Check for duplicate ID
        if (isset($this->modules[$definition->id])) {
            throw new DuplicateModuleException(
                "Module with ID '{$definition->id}' is already registered"
            );
        }

        // Check for duplicate name
        if (isset($this->nameMap[$definition->name])) {
            throw new DuplicateModuleException(
                "Module with name '{$definition->name}' is already registered"
            );
        }

        // Check for duplicate namespace
        if ($definition->namespace !== '' && isset($this->namespaceMap[$definition->namespace])) {
            throw new DuplicateModuleException(
                "Module with namespace '{$definition->namespace}' is already registered"
            );
        }

        // Call extension hook
        $this->beforeRegister($definition);

        // Register module
        $this->modules[$definition->id] = $definition;
        $this->nameMap[$definition->name] = $definition->id;

        if ($definition->namespace !== '') {
            $this->namespaceMap[$definition->namespace] = $definition->id;
        }

        // Call extension hook
        $this->afterRegister($definition);

        return $this;
    }

    /**
     * Unregister a module by ID
     *
     * Calls extension hooks before and after unregistration.
     *
     * @param string $id The module ID to unregister
     *
     * @return self Returns self for chaining
     *
     * @throws ModuleNotFoundException When module not found
     * @throws ModuleRegistryException When unregistration fails
     */
    public function unregister(string $id): self
    {
        $definition = $this->findById($id);

        // Call extension hook
        $this->beforeUnregister($definition);

        // Unregister module
        unset($this->modules[$id]);
        unset($this->nameMap[$definition->name]);

        if ($definition->namespace !== '') {
            unset($this->namespaceMap[$definition->namespace]);
        }

        // Call extension hook
        $this->afterUnregister($definition);

        return $this;
    }

    /**
     * Find module definition by ID
     *
     * @param string $id The module ID
     *
     * @return ModuleDefinition The module definition
     *
     * @throws ModuleNotFoundException When module not found
     */
    public function findById(string $id): ModuleDefinition
    {
        if (!isset($this->modules[$id])) {
            throw new ModuleNotFoundException(
                "Module with ID '{$id}' not found in registry"
            );
        }

        return $this->modules[$id];
    }

    /**
     * Find module definition by name
     *
     * @param string $name The module name
     *
     * @return ModuleDefinition The module definition
     *
     * @throws ModuleNotFoundException When module not found
     */
    public function findByName(string $name): ModuleDefinition
    {
        if (!isset($this->nameMap[$name])) {
            throw new ModuleNotFoundException(
                "Module with name '{$name}' not found in registry"
            );
        }

        $id = $this->nameMap[$name];

        return $this->modules[$id];
    }

    /**
     * Find module definition by namespace
     *
     * @param string $namespace The module namespace
     *
     * @return ModuleDefinition The module definition
     *
     * @throws ModuleNotFoundException When module not found
     */
    public function findByNamespace(string $namespace): ModuleDefinition
    {
        if (!isset($this->namespaceMap[$namespace])) {
            throw new ModuleNotFoundException(
                "Module with namespace '{$namespace}' not found in registry"
            );
        }

        $id = $this->namespaceMap[$namespace];

        return $this->modules[$id];
    }

    /**
     * Get all registered modules
     *
     * @return array<string, ModuleDefinition> All registered module definitions indexed by ID
     */
    public function all(): array
    {
        return $this->modules;
    }

    /**
     * Get count of registered modules
     *
     * @return int The count of registered modules
     */
    public function count(): int
    {
        return count($this->modules);
    }

    /**
     * Check if module exists by ID
     *
     * @param string $id The module ID
     *
     * @return bool True if module exists
     */
    public function exists(string $id): bool
    {
        return isset($this->modules[$id]);
    }

    /**
     * Check if module name is registered
     *
     * @param string $name The module name
     *
     * @return bool True if name is registered
     */
    public function nameExists(string $name): bool
    {
        return isset($this->nameMap[$name]);
    }

    /**
     * Check if namespace is registered
     *
     * @param string $namespace The module namespace
     *

     * @return bool True if namespace is registered
     */
    public function namespaceExists(string $namespace): bool
    {
        return isset($this->namespaceMap[$namespace]);
    }

    /**
     * Get all enabled modules
     *

     * @return array<string, ModuleDefinition> Enabled modules indexed by ID
     */
    public function enabled(): array
    {
        $result = [];
        foreach ($this->modules as $id => $definition) {
            if ($definition->enabled) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get all disabled modules
     *

     * @return array<string, ModuleDefinition> Disabled modules indexed by ID
     */
    public function disabled(): array
    {
        $result = [];
        foreach ($this->modules as $id => $definition) {
            if (!$definition->enabled) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get modules by category
     *

     * @param string $category The category to filter by
     *

     * @return array<string, ModuleDefinition> Modules in category indexed by ID
     */
    public function findByCategory(string $category): array
    {
        $result = [];
        foreach ($this->modules as $id => $definition) {
            if ($definition->category === $category) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get modules with a specific tag
     *

     * @param string $tag The tag to search for
     *

     * @return array<string, ModuleDefinition> Modules with tag indexed by ID
     */
    public function findByTag(string $tag): array
    {
        $result = [];
        foreach ($this->modules as $id => $definition) {
            if ($definition->hasTag($tag)) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get all modules that depend on a specific module
     *

     * @param string $moduleId The module ID to find dependents for
     *

     * @return array<string, ModuleDefinition> Modules that depend on the module indexed by ID
     *

     * @throws ModuleNotFoundException When module not found
     */
    public function dependents(string $moduleId): array
    {
        // Verify module exists
        $this->findById($moduleId);

        $result = [];
        foreach ($this->modules as $id => $definition) {
            if ($definition->hasDependency($moduleId)) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get all dependencies for a module
     *

     * @param string $moduleId The module ID to get dependencies for
     *

     * @return array<string> Array of dependency module IDs
     *

     * @throws ModuleNotFoundException When module not found
     */
    public function dependencies(string $moduleId): array
    {
        $module = $this->findById($moduleId);

        return $module->dependencies;
    }

    /**
     * Check if module has dependencies
     *

     * @param string $moduleId The module ID to check
     *

     * @return bool True if module has dependencies
     *

     * @throws ModuleNotFoundException When module not found
     */
    public function hasDependencies(string $moduleId): bool
    {
        $module = $this->findById($moduleId);

        return $module->hasDependencies();
    }

    /**
     * Get all experimental modules
     *

     * @return array<string, ModuleDefinition> Experimental modules indexed by ID
     */
    public function experimental(): array
    {
        $result = [];
        foreach ($this->modules as $id => $definition) {
            if ($definition->isExperimental()) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get all deprecated modules
     *

     * @return array<string, ModuleDefinition> Deprecated modules indexed by ID
     */
    public function deprecated(): array
    {
        $result = [];
        foreach ($this->modules as $id => $definition) {
            if ($definition->isDeprecated()) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get modules sorted by priority (highest first)
     *

     * @return array<string, ModuleDefinition> Modules sorted by priority
     */
    public function sortedByPriority(): array
    {
        $sorted = $this->modules;
        usort($sorted, fn($a, $b) => $b->priority <=> $a->priority);

        return $sorted;
    }

    /**
     * Clear all registered modules
     *

     * @return self Returns self for chaining
     */
    public function clear(): self
    {
        $this->modules = [];
        $this->nameMap = [];
        $this->namespaceMap = [];

        return $this;
    }

    /**
     * Before register extension point
     *

     * Called before a module is registered.
     * Override in child classes to add custom behavior.
     *

     * @param ModuleDefinition $definition The module definition being registered
     *

     * @return void
     */
    protected function beforeRegister(ModuleDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * After register extension point
     *

     * Called after a module is registered.
     * Override in child classes to add custom behavior.
     *

     * @param ModuleDefinition $definition The module definition that was registered
     *

     * @return void
     */
    protected function afterRegister(ModuleDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * Before unregister extension point
     *

     * Called before a module is unregistered.
     * Override in child classes to add custom behavior.
     *

     * @param ModuleDefinition $definition The module definition being unregistered
     *

     * @return void
     */
    protected function beforeUnregister(ModuleDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * After unregister extension point
     *

     * Called after a module is unregistered.
     * Override in child classes to add custom behavior.
     *

     * @param ModuleDefinition $definition The module definition that was unregistered
     *

     * @return void
     */
    protected function afterUnregister(ModuleDefinition $definition): void
    {
        // Default implementation does nothing
    }
}
