<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Registry;

use WaysNX\BusinessFramework\Exceptions\DuplicateValidationException;
use WaysNX\BusinessFramework\Exceptions\ValidationNotFoundException;
use WaysNX\BusinessFramework\Exceptions\ValidationRegistryException;

/**
 * ValidationRegistry
 *
 * Central registry for managing validation definitions.
 *
 * Purpose:
 * Serve as the authoritative catalog of validation definitions within WBF.
 * Maintains validation metadata used by Business Functions, Workflow Engine, DQP, and other components.
 *
 * Responsibilities:
 * - Register validation definitions
 * - Unregister validations
 * - Lookup validations by ID, name, or alias
 * - Lookup validations by module, scope, or severity
 * - Return all registered validations
 * - Check validation existence
 * - Manage validation metadata
 * - Filter validations by enabled/disabled state
 * - Provide extension points for customization
 *
 * Usage:
 * ```php
 * $registry = new ValidationRegistry();
 *
 * $registry->register(new ValidationDefinition(
 *     id: 'customer-validation',
 *     name: 'CustomerValidation',
 *     displayName: 'Customer Validation',
 *     moduleId: 'crm',
 *     scope: 'entity',
 *     severity: 'error'
 * ));
 *
 * $validation = $registry->findById('customer-validation');
 * $validations = $registry->findByScope('entity');
 * $validations = $registry->findBySeverity('error');
 * ```
 *
 * Extension Points:
 * Child classes can override protected methods to add custom behavior:
 * - beforeRegister(): Called before registration
 * - afterRegister(): Called after registration
 * - beforeUnregister(): Called before removal
 * - afterUnregister(): Called after removal
 *
 * @package WaysNX\BusinessFramework\Registry
 */
class ValidationRegistry
{
    /**
     * Registered validation definitions indexed by ID
     *

     * @var array<string, ValidationDefinition>
     */
    protected array $validations = [];

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
     * Module to validation IDs mapping
     *

     * @var array<string, array<string>>
     */
    protected array $moduleMap = [];

    /**
     * Scope to validation IDs mapping
     *

     * @var array<string, array<string>>
     */
    protected array $scopeMap = [];

    /**
     * Severity to validation IDs mapping
     *

     * @var array<string, array<string>>
     */
    protected array $severityMap = [];

    /**
     * Register a validation definition
     *

     * @param ValidationDefinition $definition The validation definition to register
     *

     * @return self Returns self for chaining
     *

     * @throws DuplicateValidationException When validation already registered
     * @throws ValidationRegistryException When registration fails
     */
    public function register(ValidationDefinition $definition): self
    {
        // Check for duplicate ID
        if (isset($this->validations[$definition->id])) {
            throw new DuplicateValidationException(
                "Validation with ID '{$definition->id}' is already registered"
            );
        }

        // Check for duplicate name
        if (isset($this->nameMap[$definition->name])) {
            throw new DuplicateValidationException(
                "Validation with name '{$definition->name}' is already registered"
            );
        }

        // Validate rule IDs are unique
        $ruleIds = [];
        foreach ($definition->rules as $rule) {
            if (in_array($rule->id, $ruleIds, true)) {
                throw new DuplicateValidationException(
                    "Rule ID '{$rule->id}' is not unique in validation '{$definition->id}'"
                );
            }
            $ruleIds[] = $rule->id;
        }

        // Call extension hook
        $this->beforeRegister($definition);

        // Register validation
        $this->validations[$definition->id] = $definition;
        $this->nameMap[$definition->name] = $definition->id;

        // Add to module map if module specified
        if ($definition->moduleId !== '') {
            if (!isset($this->moduleMap[$definition->moduleId])) {
                $this->moduleMap[$definition->moduleId] = [];
            }
            $this->moduleMap[$definition->moduleId][] = $definition->id;
        }

        // Add to scope map
        if (!isset($this->scopeMap[$definition->scope])) {
            $this->scopeMap[$definition->scope] = [];
        }
        $this->scopeMap[$definition->scope][] = $definition->id;

        // Add to severity map
        if (!isset($this->severityMap[$definition->severity])) {
            $this->severityMap[$definition->severity] = [];
        }
        $this->severityMap[$definition->severity][] = $definition->id;

        // Call extension hook
        $this->afterRegister($definition);

        return $this;
    }

    /**
     * Unregister a validation by ID
     *

     * @param string $id The validation ID to unregister
     *

     * @return self Returns self for chaining
     *

     * @throws ValidationNotFoundException When validation not found
     */
    public function unregister(string $id): self
    {
        $definition = $this->findById($id);

        // Call extension hook
        $this->beforeUnregister($definition);

        // Unregister validation
        unset($this->validations[$id]);
        unset($this->nameMap[$definition->name]);

        // Remove from module map
        if ($definition->moduleId !== '' && isset($this->moduleMap[$definition->moduleId])) {
            $key = array_search($id, $this->moduleMap[$definition->moduleId], true);
            if ($key !== false) {
                unset($this->moduleMap[$definition->moduleId][$key]);
            }
        }

        // Remove from scope map
        if (isset($this->scopeMap[$definition->scope])) {
            $key = array_search($id, $this->scopeMap[$definition->scope], true);
            if ($key !== false) {
                unset($this->scopeMap[$definition->scope][$key]);
            }
        }

        // Remove from severity map
        if (isset($this->severityMap[$definition->severity])) {
            $key = array_search($id, $this->severityMap[$definition->severity], true);
            if ($key !== false) {
                unset($this->severityMap[$definition->severity][$key]);
            }
        }

        // Remove aliases
        foreach ($this->aliases as $alias => $validationId) {
            if ($validationId === $id) {
                unset($this->aliases[$alias]);
            }
        }

        // Call extension hook
        $this->afterUnregister($definition);

        return $this;
    }

    /**
     * Find validation definition by ID
     *

     * @param string $id The validation ID
     *

     * @return ValidationDefinition The validation definition
     *

     * @throws ValidationNotFoundException When validation not found
     */
    public function findById(string $id): ValidationDefinition
    {
        if (!isset($this->validations[$id])) {
            throw new ValidationNotFoundException(
                "Validation with ID '{$id}' not found in registry"
            );
        }

        return $this->validations[$id];
    }

    /**
     * Find validation definition by name
     *

     * @param string $name The validation name
     *

     * @return ValidationDefinition The validation definition
     *

     * @throws ValidationNotFoundException When validation not found
     */
    public function findByName(string $name): ValidationDefinition
    {
        if (!isset($this->nameMap[$name])) {
            throw new ValidationNotFoundException(
                "Validation with name '{$name}' not found in registry"
            );
        }

        $id = $this->nameMap[$name];

        return $this->validations[$id];
    }

    /**
     * Find validation definition by alias
     *

     * @param string $alias The validation alias
     *

     * @return ValidationDefinition The validation definition
     *

     * @throws ValidationNotFoundException When alias not found
     */
    public function findByAlias(string $alias): ValidationDefinition
    {
        if (!isset($this->aliases[$alias])) {
            throw new ValidationNotFoundException(
                "Validation alias '{$alias}' not found in registry"
            );
        }

        $id = $this->aliases[$alias];

        return $this->validations[$id];
    }

    /**
     * Find all validations in a module
     *

     * @param string $moduleId The module ID
     *

     * @return array<string, ValidationDefinition> Validations in module indexed by ID
     */
    public function findByModule(string $moduleId): array
    {
        $result = [];

        if (!isset($this->moduleMap[$moduleId])) {
            return $result;
        }

        foreach ($this->moduleMap[$moduleId] as $validationId) {
            $result[$validationId] = $this->validations[$validationId];
        }

        return $result;
    }

    /**
     * Find all validations by scope
     *

     * @param string $scope The scope to filter by
     *

     * @return array<string, ValidationDefinition> Validations with scope indexed by ID
     */
    public function findByScope(string $scope): array
    {
        $result = [];

        if (!isset($this->scopeMap[$scope])) {
            return $result;
        }

        foreach ($this->scopeMap[$scope] as $validationId) {
            $result[$validationId] = $this->validations[$validationId];
        }

        return $result;
    }

    /**
     * Find all validations by severity
     *

     * @param string $severity The severity to filter by
     *

     * @return array<string, ValidationDefinition> Validations with severity indexed by ID
     */
    public function findBySeverity(string $severity): array
    {
        $result = [];

        if (!isset($this->severityMap[$severity])) {
            return $result;
        }

        foreach ($this->severityMap[$severity] as $validationId) {
            $result[$validationId] = $this->validations[$validationId];
        }

        return $result;
    }

    /**
     * Find validations by category
     *

     * @param string $category The category to filter by
     *

     * @return array<string, ValidationDefinition> Validations in category indexed by ID
     */
    public function findByCategory(string $category): array
    {
        $result = [];
        foreach ($this->validations as $id => $definition) {
            if ($definition->category === $category) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Find validations by tag
     *

     * @param string $tag The tag to search for
     *

     * @return array<string, ValidationDefinition> Validations with tag indexed by ID
     */
    public function findByTag(string $tag): array
    {
        $result = [];
        foreach ($this->validations as $id => $definition) {
            if ($definition->hasTag($tag)) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get all registered validations
     *

     * @return array<string, ValidationDefinition> All validations indexed by ID
     */
    public function all(): array
    {
        return $this->validations;
    }

    /**
     * Get count of registered validations
     *

     * @return int The count of registered validations
     */
    public function count(): int
    {
        return count($this->validations);
    }

    /**
     * Check if validation exists by ID
     *

     * @param string $id The validation ID
     *

     * @return bool True if validation exists
     */
    public function exists(string $id): bool
    {
        return isset($this->validations[$id]);
    }

    /**
     * Check if validation name is registered
     *

     * @param string $name The validation name
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

     * @param string $alias The validation alias
     *

     * @return bool True if alias exists
     */
    public function aliasExists(string $alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    /**
     * Get all enabled validations
     *

     * @return array<string, ValidationDefinition> Enabled validations indexed by ID
     */
    public function enabled(): array
    {
        $result = [];
        foreach ($this->validations as $id => $definition) {
            if ($definition->enabled) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get all disabled validations
     *

     * @return array<string, ValidationDefinition> Disabled validations indexed by ID
     */
    public function disabled(): array
    {
        $result = [];
        foreach ($this->validations as $id => $definition) {
            if (!$definition->enabled) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Add an alias for a validation
     *

     * @param string $id The validation ID
     * @param string $alias The alias to add
     *

     * @return self Returns self for chaining
     *

     * @throws ValidationNotFoundException When validation not found
     * @throws DuplicateValidationException When alias already exists
     */
    public function alias(string $id, string $alias): self
    {
        // Verify validation exists
        $this->findById($id);

        // Check for duplicate alias
        if (isset($this->aliases[$alias])) {
            throw new DuplicateValidationException(
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

     * @throws ValidationNotFoundException When alias not found
     */
    public function removeAlias(string $alias): self
    {
        if (!isset($this->aliases[$alias])) {
            throw new ValidationNotFoundException(
                "Alias '{$alias}' not found in registry"
            );
        }

        unset($this->aliases[$alias]);

        return $this;
    }

    /**
     * Get all aliases for a validation
     *

     * @param string $id The validation ID
     *

     * @return array<string> All aliases for the validation
     *

     * @throws ValidationNotFoundException When validation not found
     */
    public function getAliasesFor(string $id): array
    {
        // Verify validation exists
        $this->findById($id);

        $result = [];
        foreach ($this->aliases as $alias => $validationId) {
            if ($validationId === $id) {
                $result[] = $alias;
            }
        }

        return $result;
    }

    /**
     * Get all experimental validations
     *

     * @return array<string, ValidationDefinition> Experimental validations indexed by ID
     */
    public function experimental(): array
    {
        $result = [];
        foreach ($this->validations as $id => $definition) {
            if ($definition->isExperimental()) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get all deprecated validations
     *

     * @return array<string, ValidationDefinition> Deprecated validations indexed by ID
     */
    public function deprecated(): array
    {
        $result = [];
        foreach ($this->validations as $id => $definition) {
            if ($definition->isDeprecated()) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Find validations supporting a specific entity type
     *

     * @param string $entityType The entity type to search for
     *

     * @return array<string, ValidationDefinition> Validations supporting entity indexed by ID
     */
    public function supportingEntity(string $entityType): array
    {
        $result = [];
        foreach ($this->validations as $id => $definition) {
            if ($definition->supportsEntityType($entityType)) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get validations sorted by priority (highest first)
     *

     * @return array<string, ValidationDefinition> Validations sorted by priority
     */
    public function sortedByPriority(): array
    {
        $sorted = $this->validations;
        uasort($sorted, fn($a, $b) => $b->priority <=> $a->priority);

        return $sorted;
    }

    /**
     * Clear all registered validations
     *

     * @return self Returns self for chaining
     */
    public function clear(): self
    {
        $this->validations = [];
        $this->nameMap = [];
        $this->aliases = [];
        $this->moduleMap = [];
        $this->scopeMap = [];
        $this->severityMap = [];

        return $this;
    }

    /**
     * Before register extension point
     *

     * @param ValidationDefinition $definition The validation definition being registered
     *

     * @return void
     */
    protected function beforeRegister(ValidationDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * After register extension point
     *

     * @param ValidationDefinition $definition The validation definition that was registered
     *

     * @return void
     */
    protected function afterRegister(ValidationDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * Before unregister extension point
     *

     * @param ValidationDefinition $definition The validation definition being unregistered
     *

     * @return void
     */
    protected function beforeUnregister(ValidationDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * After unregister extension point
     *

     * @param ValidationDefinition $definition The validation definition that was unregistered
     *

     * @return void
     */
    protected function afterUnregister(ValidationDefinition $definition): void
    {
        // Default implementation does nothing
    }
}
