<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Registry;

use WaysNX\BusinessFramework\Exceptions\BusinessFunctionNotFoundException;
use WaysNX\BusinessFramework\Exceptions\BusinessFunctionRegistryException;
use WaysNX\BusinessFramework\Exceptions\DuplicateBusinessFunctionException;

/**
 * BusinessFunctionRegistry
 *
 * Central registry for managing business function definitions.
 *
 * Purpose:
 * Serve as the authoritative catalog of all registered business functions within WBF.
 * Maintains business function definitions that describe the capabilities available
 * throughout the framework without managing their execution.
 *
 * Responsibilities:
 * - Register business function definitions
 * - Unregister business functions
 * - Lookup functions by ID, name, or alias
 * - Return all registered functions
 * - Check function existence
 * - Manage function metadata
 * - Query functions by module
 * - Filter functions by enabled/disabled state
 * - Provide extension points for customization
 *
 * Usage:
 * ```php
 * $registry = new BusinessFunctionRegistry();
 *
 * $registry->register(new BusinessFunctionDefinition(
 *     id: 'create-customer',
 *     name: 'CreateCustomer',
 *     displayName: 'Create Customer',
 *     moduleId: 'crm',
 *     category: 'customer-management',
 *     inputDefinitions: [['name' => 'firstName', 'type' => 'string']],
 *     supportedEntityTypes: ['Customer'],
 *     requiredPermissions: ['customer.create']
 * ));
 *
 * $function = $registry->findById('create-customer');
 * $function = $registry->findByName('CreateCustomer');
 * $crm = $registry->findByModule('crm');
 *
 * $registry->alias('create-customer', 'create-cust');
 * $function = $registry->findByAlias('create-cust');
 * ```
 *
 * Extension Points:
 * Child classes can override protected methods to add custom behavior:
 * - beforeRegister(): Called before function registration
 * - afterRegister(): Called after function registration
 * - beforeUnregister(): Called before function removal
 * - afterUnregister(): Called after function removal
 *
 * Key Features:
 * - Type-safe function lookup by multiple criteria
 * - Alias support for function names
 * - Module-based organization
 * - Enabled/disabled state tracking
 * - Comprehensive validation
 * - Extension points for customization
 * - Lightweight, framework-independent implementation
 * - No execution or workflow logic
 *
 * Note:
 * Business Functions describe capabilities, they do not execute them.
 * This registry maintains definitions only, not runtime state or execution logic.
 *
 * @package WaysNX\BusinessFramework\Registry
 */
class BusinessFunctionRegistry
{
    /**
     * Registered function definitions indexed by ID
     *
     * @var array<string, BusinessFunctionDefinition>
     */
    protected array $functions = [];

    /**
     * Name to ID mapping for reverse lookup
     *
     * @var array<string, string>
     */
    protected array $nameMap = [];

    /**
     * Alias to ID mapping for reverse lookup
     *
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * Module to function IDs mapping
     *

     * @var array<string, array<string>>
     */
    protected array $moduleMap = [];

    /**
     * Register a business function definition
     *

     * Validates uniqueness of ID and name before registration.
     * Calls extension hooks before and after registration.
     *

     * @param BusinessFunctionDefinition $definition The function definition to register
     *

     * @return self Returns self for chaining
     *

     * @throws DuplicateBusinessFunctionException When function ID or name already registered
     * @throws BusinessFunctionRegistryException When registration fails
     */
    public function register(BusinessFunctionDefinition $definition): self
    {
        // Check for duplicate ID
        if (isset($this->functions[$definition->id])) {
            throw new DuplicateBusinessFunctionException(
                "Function with ID '{$definition->id}' is already registered"
            );
        }

        // Check for duplicate name
        if (isset($this->nameMap[$definition->name])) {
            throw new DuplicateBusinessFunctionException(
                "Function with name '{$definition->name}' is already registered"
            );
        }

        // Call extension hook
        $this->beforeRegister($definition);

        // Register function
        $this->functions[$definition->id] = $definition;
        $this->nameMap[$definition->name] = $definition->id;

        // Add to module map if module specified
        if ($definition->moduleId !== '') {
            if (!isset($this->moduleMap[$definition->moduleId])) {
                $this->moduleMap[$definition->moduleId] = [];
            }
            $this->moduleMap[$definition->moduleId][] = $definition->id;
        }

        // Call extension hook
        $this->afterRegister($definition);

        return $this;
    }

    /**
     * Unregister a business function by ID
     *

     * Calls extension hooks before and after unregistration.
     *

     * @param string $id The function ID to unregister
     *

     * @return self Returns self for chaining
     *

     * @throws BusinessFunctionNotFoundException When function not found
     * @throws BusinessFunctionRegistryException When unregistration fails
     */
    public function unregister(string $id): self
    {
        $definition = $this->findById($id);

        // Call extension hook
        $this->beforeUnregister($definition);

        // Unregister function
        unset($this->functions[$id]);
        unset($this->nameMap[$definition->name]);

        // Remove from module map
        if ($definition->moduleId !== '' && isset($this->moduleMap[$definition->moduleId])) {
            $key = array_search($id, $this->moduleMap[$definition->moduleId], true);
            if ($key !== false) {
                unset($this->moduleMap[$definition->moduleId][$key]);
            }
            // Clean up empty module entries
            if (count($this->moduleMap[$definition->moduleId]) === 0) {
                unset($this->moduleMap[$definition->moduleId]);
            }
        }

        // Remove aliases
        foreach ($this->aliases as $alias => $functionId) {
            if ($functionId === $id) {
                unset($this->aliases[$alias]);
            }
        }

        // Call extension hook
        $this->afterUnregister($definition);

        return $this;
    }

    /**
     * Find function definition by ID
     *

     * @param string $id The function ID
     *

     * @return BusinessFunctionDefinition The function definition
     *

     * @throws BusinessFunctionNotFoundException When function not found
     */
    public function findById(string $id): BusinessFunctionDefinition
    {
        if (!isset($this->functions[$id])) {
            throw new BusinessFunctionNotFoundException(
                "Function with ID '{$id}' not found in registry"
            );
        }

        return $this->functions[$id];
    }

    /**
     * Find function definition by name
     *

     * @param string $name The function name
     *

     * @return BusinessFunctionDefinition The function definition
     *

     * @throws BusinessFunctionNotFoundException When function not found
     */
    public function findByName(string $name): BusinessFunctionDefinition
    {
        if (!isset($this->nameMap[$name])) {
            throw new BusinessFunctionNotFoundException(
                "Function with name '{$name}' not found in registry"
            );
        }

        $id = $this->nameMap[$name];

        return $this->functions[$id];
    }

    /**
     * Find function definition by alias
     *

     * @param string $alias The function alias
     *

     * @return BusinessFunctionDefinition The function definition
     *

     * @throws BusinessFunctionNotFoundException When alias or function not found
     */
    public function findByAlias(string $alias): BusinessFunctionDefinition
    {
        if (!isset($this->aliases[$alias])) {
            throw new BusinessFunctionNotFoundException(
                "Function alias '{$alias}' not found in registry"
            );
        }

        $id = $this->aliases[$alias];

        return $this->functions[$id];
    }

    /**
     * Find all functions in a module
     *

     * @param string $moduleId The module ID
     *

     * @return array<string, BusinessFunctionDefinition> Functions in module indexed by ID
     */
    public function findByModule(string $moduleId): array
    {
        $result = [];

        if (!isset($this->moduleMap[$moduleId])) {
            return $result;
        }

        foreach ($this->moduleMap[$moduleId] as $functionId) {
            $result[$functionId] = $this->functions[$functionId];
        }

        return $result;
    }

    /**
     * Find functions by category
     *

     * @param string $category The category to filter by
     *

     * @return array<string, BusinessFunctionDefinition> Functions in category indexed by ID
     */
    public function findByCategory(string $category): array
    {
        $result = [];
        foreach ($this->functions as $id => $definition) {
            if ($definition->category === $category) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Find functions by tag
     *

     * @param string $tag The tag to search for
     *

     * @return array<string, BusinessFunctionDefinition> Functions with tag indexed by ID
     */
    public function findByTag(string $tag): array
    {
        $result = [];
        foreach ($this->functions as $id => $definition) {
            if ($definition->hasTag($tag)) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get all registered functions
     *

     * @return array<string, BusinessFunctionDefinition> All registered function definitions indexed by ID
     */
    public function all(): array
    {
        return $this->functions;
    }

    /**
     * Get count of registered functions
     *

     * @return int The count of registered functions
     */
    public function count(): int
    {
        return count($this->functions);
    }

    /**
     * Check if function exists by ID
     *

     * @param string $id The function ID
     *

     * @return bool True if function exists
     */
    public function exists(string $id): bool
    {
        return isset($this->functions[$id]);
    }

    /**
     * Check if function name is registered
     *

     * @param string $name The function name
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

     * @param string $alias The function alias
     *

     * @return bool True if alias exists
     */
    public function aliasExists(string $alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    /**
     * Get all enabled functions
     *

     * @return array<string, BusinessFunctionDefinition> Enabled functions indexed by ID
     */
    public function enabled(): array
    {
        $result = [];
        foreach ($this->functions as $id => $definition) {
            if ($definition->enabled) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get all disabled functions
     *

     * @return array<string, BusinessFunctionDefinition> Disabled functions indexed by ID
     */
    public function disabled(): array
    {
        $result = [];
        foreach ($this->functions as $id => $definition) {
            if (!$definition->enabled) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Add an alias for a function
     *

     * @param string $id The function ID
     * @param string $alias The alias to add
     *

     * @return self Returns self for chaining
     *

     * @throws BusinessFunctionNotFoundException When function not found
     * @throws DuplicateBusinessFunctionException When alias already exists
     */
    public function alias(string $id, string $alias): self
    {
        // Verify function exists
        $this->findById($id);

        // Check for duplicate alias
        if (isset($this->aliases[$alias])) {
            throw new DuplicateBusinessFunctionException(
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

     * @throws BusinessFunctionNotFoundException When alias not found
     */
    public function removeAlias(string $alias): self
    {
        if (!isset($this->aliases[$alias])) {
            throw new BusinessFunctionNotFoundException(
                "Alias '{$alias}' not found in registry"
            );
        }

        unset($this->aliases[$alias]);

        return $this;
    }

    /**
     * Get all aliases for a function
     *

     * @param string $id The function ID
     *

     * @return array<string> All aliases for the function
     *

     * @throws BusinessFunctionNotFoundException When function not found
     */
    public function getAliasesFor(string $id): array
    {
        // Verify function exists
        $this->findById($id);

        $result = [];
        foreach ($this->aliases as $alias => $functionId) {
            if ($functionId === $id) {
                $result[] = $alias;
            }
        }

        return $result;
    }

    /**
     * Get all experimental functions
     *

     * @return array<string, BusinessFunctionDefinition> Experimental functions indexed by ID
     */
    public function experimental(): array
    {
        $result = [];
        foreach ($this->functions as $id => $definition) {
            if ($definition->isExperimental()) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get all deprecated functions
     *

     * @return array<string, BusinessFunctionDefinition> Deprecated functions indexed by ID
     */
    public function deprecated(): array
    {
        $result = [];
        foreach ($this->functions as $id => $definition) {
            if ($definition->isDeprecated()) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Find functions that support a specific entity type
     *

     * @param string $entityType The entity type to search for
     *

     * @return array<string, BusinessFunctionDefinition> Functions supporting entity indexed by ID
     */
    public function supportingEntity(string $entityType): array
    {
        $result = [];
        foreach ($this->functions as $id => $definition) {
            if ($definition->supportsEntityType($entityType)) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Find functions that require a specific permission
     *

     * @param string $permission The permission to search for
     *

     * @return array<string, BusinessFunctionDefinition> Functions requiring permission indexed by ID
     */
    public function requiringPermission(string $permission): array
    {
        $result = [];
        foreach ($this->functions as $id => $definition) {
            if ($definition->requiresPermission($permission)) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Clear all registered functions
     *

     * @return self Returns self for chaining
     */
    public function clear(): self
    {
        $this->functions = [];
        $this->nameMap = [];
        $this->aliases = [];
        $this->moduleMap = [];

        return $this;
    }

    /**
     * Before register extension point
     *

     * Called before a function is registered.
     * Override in child classes to add custom behavior.
     *

     * @param BusinessFunctionDefinition $definition The function definition being registered
     *

     * @return void
     */
    protected function beforeRegister(BusinessFunctionDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * After register extension point
     *

     * Called after a function is registered.
     * Override in child classes to add custom behavior.
     *

     * @param BusinessFunctionDefinition $definition The function definition that was registered
     *

     * @return void
     */
    protected function afterRegister(BusinessFunctionDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * Before unregister extension point
     *

     * Called before a function is unregistered.
     * Override in child classes to add custom behavior.
     *

     * @param BusinessFunctionDefinition $definition The function definition being unregistered
     *

     * @return void
     */
    protected function beforeUnregister(BusinessFunctionDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * After unregister extension point
     *

     * Called after a function is unregistered.
     * Override in child classes to add custom behavior.
     *

     * @param BusinessFunctionDefinition $definition The function definition that was unregistered
     *

     * @return void
     */
    protected function afterUnregister(BusinessFunctionDefinition $definition): void
    {
        // Default implementation does nothing
    }
}
