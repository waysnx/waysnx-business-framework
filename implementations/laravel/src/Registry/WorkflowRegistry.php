<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Registry;

use WaysNX\BusinessFramework\Exceptions\DuplicateWorkflowException;
use WaysNX\BusinessFramework\Exceptions\WorkflowNotFoundException;
use WaysNX\BusinessFramework\Exceptions\WorkflowRegistryException;

/**
 * WorkflowRegistry
 *
 * Central registry for managing workflow definitions.
 *
 * Purpose:
 * Serve as the authoritative catalog of all registered workflows within WBF.
 * Maintains workflow definitions that describe reusable business processes
 * without managing their execution.
 *
 * Responsibilities:
 * - Register workflow definitions
 * - Unregister workflows
 * - Lookup workflows by ID, name, or alias
 * - Lookup workflows by module or trigger
 * - Return all registered workflows
 * - Check workflow existence
 * - Manage workflow metadata
 * - Filter workflows by enabled/disabled state
 * - Provide extension points for customization
 *
 * Usage:
 * ```php
 * $registry = new WorkflowRegistry();
 *
 * $registry->register(new WorkflowDefinition(
 *     id: 'employee-onboarding',
 *     name: 'EmployeeOnboarding',
 *     displayName: 'Employee Onboarding',
 *     moduleId: 'hr',
 *     triggerType: 'event',
 *     triggerEvent: 'employee.created',
 *     steps: [new WorkflowStep(...)]
 * ));
 *
 * $workflow = $registry->findById('employee-onboarding');
 * $workflow = $registry->findByName('EmployeeOnboarding');
 * $hrWorkflows = $registry->findByModule('hr');
 * $eventWorkflows = $registry->findByTrigger('event');
 *
 * $registry->alias('employee-onboarding', 'onboarding');
 * ```
 *
 * Extension Points:
 * Child classes can override protected methods to add custom behavior:
 * - beforeRegister(): Called before workflow registration
 * - afterRegister(): Called after workflow registration
 * - beforeUnregister(): Called before workflow removal
 * - afterUnregister(): Called after workflow removal
 *
 * Key Features:
 * - Type-safe workflow lookup by multiple criteria
 * - Alias support for workflow names
 * - Module-based organization
 * - Trigger-based filtering
 * - Enabled/disabled state tracking
 * - Comprehensive validation
 * - Extension points for customization
 * - Lightweight, framework-independent implementation
 * - No execution or workflow engine logic
 *
 * Note:
 * Workflows describe business processes, they do not execute them.
 * This registry maintains definitions only, not runtime state or execution logic.
 *
 * @package WaysNX\BusinessFramework\Registry
 */
class WorkflowRegistry
{
    /**
     * Registered workflow definitions indexed by ID
     *

     * @var array<string, WorkflowDefinition>
     */
    protected array $workflows = [];

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
     * Module to workflow IDs mapping
     *

     * @var array<string, array<string>>
     */
    protected array $moduleMap = [];

    /**
     * Trigger type to workflow IDs mapping
     *

     * @var array<string, array<string>>
     */
    protected array $triggerMap = [];

    /**
     * Register a workflow definition
     *

     * Validates uniqueness of ID and name before registration.
     * Validates step ID uniqueness.
     * Calls extension hooks before and after registration.
     *

     * @param WorkflowDefinition $definition The workflow definition to register
     *

     * @return self Returns self for chaining
     *

     * @throws DuplicateWorkflowException When workflow ID or name already registered
     * @throws WorkflowRegistryException When registration fails
     */
    public function register(WorkflowDefinition $definition): self
    {
        // Check for duplicate ID
        if (isset($this->workflows[$definition->id])) {
            throw new DuplicateWorkflowException(
                "Workflow with ID '{$definition->id}' is already registered"
            );
        }

        // Check for duplicate name
        if (isset($this->nameMap[$definition->name])) {
            throw new DuplicateWorkflowException(
                "Workflow with name '{$definition->name}' is already registered"
            );
        }

        // Validate step IDs are unique
        $stepIds = [];
        foreach ($definition->steps as $step) {
            if (in_array($step->id, $stepIds, true)) {
                throw new DuplicateWorkflowException(
                    "Step ID '{$step->id}' is not unique in workflow '{$definition->id}'"
                );
            }
            $stepIds[] = $step->id;
        }

        // Call extension hook
        $this->beforeRegister($definition);

        // Register workflow
        $this->workflows[$definition->id] = $definition;
        $this->nameMap[$definition->name] = $definition->id;

        // Add to module map if module specified
        if ($definition->moduleId !== '') {
            if (!isset($this->moduleMap[$definition->moduleId])) {
                $this->moduleMap[$definition->moduleId] = [];
            }
            $this->moduleMap[$definition->moduleId][] = $definition->id;
        }

        // Add to trigger map
        if (!isset($this->triggerMap[$definition->triggerType])) {
            $this->triggerMap[$definition->triggerType] = [];
        }
        $this->triggerMap[$definition->triggerType][] = $definition->id;

        // Call extension hook
        $this->afterRegister($definition);

        return $this;
    }

    /**
     * Unregister a workflow by ID
     *

     * Calls extension hooks before and after unregistration.
     *

     * @param string $id The workflow ID to unregister
     *

     * @return self Returns self for chaining
     *

     * @throws WorkflowNotFoundException When workflow not found
     * @throws WorkflowRegistryException When unregistration fails
     */
    public function unregister(string $id): self
    {
        $definition = $this->findById($id);

        // Call extension hook
        $this->beforeUnregister($definition);

        // Unregister workflow
        unset($this->workflows[$id]);
        unset($this->nameMap[$definition->name]);

        // Remove from module map
        if ($definition->moduleId !== '' && isset($this->moduleMap[$definition->moduleId])) {
            $key = array_search($id, $this->moduleMap[$definition->moduleId], true);
            if ($key !== false) {
                unset($this->moduleMap[$definition->moduleId][$key]);
            }
            if (count($this->moduleMap[$definition->moduleId]) === 0) {
                unset($this->moduleMap[$definition->moduleId]);
            }
        }

        // Remove from trigger map
        if (isset($this->triggerMap[$definition->triggerType])) {
            $key = array_search($id, $this->triggerMap[$definition->triggerType], true);
            if ($key !== false) {
                unset($this->triggerMap[$definition->triggerType][$key]);
            }
            if (count($this->triggerMap[$definition->triggerType]) === 0) {
                unset($this->triggerMap[$definition->triggerType]);
            }
        }

        // Remove aliases
        foreach ($this->aliases as $alias => $workflowId) {
            if ($workflowId === $id) {
                unset($this->aliases[$alias]);
            }
        }

        // Call extension hook
        $this->afterUnregister($definition);

        return $this;
    }

    /**
     * Find workflow definition by ID
     *

     * @param string $id The workflow ID
     *

     * @return WorkflowDefinition The workflow definition
     *

     * @throws WorkflowNotFoundException When workflow not found
     */
    public function findById(string $id): WorkflowDefinition
    {
        if (!isset($this->workflows[$id])) {
            throw new WorkflowNotFoundException(
                "Workflow with ID '{$id}' not found in registry"
            );
        }

        return $this->workflows[$id];
    }

    /**
     * Find workflow definition by name
     *

     * @param string $name The workflow name
     *

     * @return WorkflowDefinition The workflow definition
     *

     * @throws WorkflowNotFoundException When workflow not found
     */
    public function findByName(string $name): WorkflowDefinition
    {
        if (!isset($this->nameMap[$name])) {
            throw new WorkflowNotFoundException(
                "Workflow with name '{$name}' not found in registry"
            );
        }

        $id = $this->nameMap[$name];

        return $this->workflows[$id];
    }

    /**
     * Find workflow definition by alias
     *

     * @param string $alias The workflow alias
     *

     * @return WorkflowDefinition The workflow definition
     *

     * @throws WorkflowNotFoundException When alias or workflow not found
     */
    public function findByAlias(string $alias): WorkflowDefinition
    {
        if (!isset($this->aliases[$alias])) {
            throw new WorkflowNotFoundException(
                "Workflow alias '{$alias}' not found in registry"
            );
        }

        $id = $this->aliases[$alias];

        return $this->workflows[$id];
    }

    /**
     * Find all workflows in a module
     *

     * @param string $moduleId The module ID
     *

     * @return array<string, WorkflowDefinition> Workflows in module indexed by ID
     */
    public function findByModule(string $moduleId): array
    {
        $result = [];

        if (!isset($this->moduleMap[$moduleId])) {
            return $result;
        }

        foreach ($this->moduleMap[$moduleId] as $workflowId) {
            $result[$workflowId] = $this->workflows[$workflowId];
        }

        return $result;
    }

    /**
     * Find all workflows by trigger type
     *

     * @param string $triggerType The trigger type to filter by
     *

     * @return array<string, WorkflowDefinition> Workflows with trigger type indexed by ID
     */
    public function findByTrigger(string $triggerType): array
    {
        $result = [];

        if (!isset($this->triggerMap[$triggerType])) {
            return $result;
        }

        foreach ($this->triggerMap[$triggerType] as $workflowId) {
            $result[$workflowId] = $this->workflows[$workflowId];
        }

        return $result;
    }

    /**
     * Find workflows by trigger event
     *

     * @param string $triggerEvent The trigger event to search for
     *

     * @return array<string, WorkflowDefinition> Workflows with event indexed by ID
     */
    public function findByTriggerEvent(string $triggerEvent): array
    {
        $result = [];
        foreach ($this->workflows as $id => $definition) {
            if ($definition->triggerEvent === $triggerEvent) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Find workflows by category
     *

     * @param string $category The category to filter by
     *

     * @return array<string, WorkflowDefinition> Workflows in category indexed by ID
     */
    public function findByCategory(string $category): array
    {
        $result = [];
        foreach ($this->workflows as $id => $definition) {
            if ($definition->category === $category) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Find workflows by tag
     *

     * @param string $tag The tag to search for
     *

     * @return array<string, WorkflowDefinition> Workflows with tag indexed by ID
     */
    public function findByTag(string $tag): array
    {
        $result = [];
        foreach ($this->workflows as $id => $definition) {
            if ($definition->hasTag($tag)) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get all registered workflows
     *

     * @return array<string, WorkflowDefinition> All registered workflow definitions indexed by ID
     */
    public function all(): array
    {
        return $this->workflows;
    }

    /**
     * Get count of registered workflows
     *

     * @return int The count of registered workflows
     */
    public function count(): int
    {
        return count($this->workflows);
    }

    /**
     * Check if workflow exists by ID
     *

     * @param string $id The workflow ID
     *

     * @return bool True if workflow exists
     */
    public function exists(string $id): bool
    {
        return isset($this->workflows[$id]);
    }

    /**
     * Check if workflow name is registered
     *

     * @param string $name The workflow name
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

     * @param string $alias The workflow alias
     *

     * @return bool True if alias exists
     */
    public function aliasExists(string $alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    /**
     * Get all enabled workflows
     *

     * @return array<string, WorkflowDefinition> Enabled workflows indexed by ID
     */
    public function enabled(): array
    {
        $result = [];
        foreach ($this->workflows as $id => $definition) {
            if ($definition->enabled) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get all disabled workflows
     *

     * @return array<string, WorkflowDefinition> Disabled workflows indexed by ID
     */
    public function disabled(): array
    {
        $result = [];
        foreach ($this->workflows as $id => $definition) {
            if (!$definition->enabled) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Add an alias for a workflow
     *

     * @param string $id The workflow ID
     * @param string $alias The alias to add
     *

     * @return self Returns self for chaining
     *

     * @throws WorkflowNotFoundException When workflow not found
     * @throws DuplicateWorkflowException When alias already exists
     */
    public function alias(string $id, string $alias): self
    {
        // Verify workflow exists
        $this->findById($id);

        // Check for duplicate alias
        if (isset($this->aliases[$alias])) {
            throw new DuplicateWorkflowException(
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

     * @throws WorkflowNotFoundException When alias not found
     */
    public function removeAlias(string $alias): self
    {
        if (!isset($this->aliases[$alias])) {
            throw new WorkflowNotFoundException(
                "Alias '{$alias}' not found in registry"
            );
        }

        unset($this->aliases[$alias]);

        return $this;
    }

    /**
     * Get all aliases for a workflow
     *

     * @param string $id The workflow ID
     *

     * @return array<string> All aliases for the workflow
     *

     * @throws WorkflowNotFoundException When workflow not found
     */
    public function getAliasesFor(string $id): array
    {
        // Verify workflow exists
        $this->findById($id);

        $result = [];
        foreach ($this->aliases as $alias => $workflowId) {
            if ($workflowId === $id) {
                $result[] = $alias;
            }
        }

        return $result;
    }

    /**
     * Get all experimental workflows
     *

     * @return array<string, WorkflowDefinition> Experimental workflows indexed by ID
     */
    public function experimental(): array
    {
        $result = [];
        foreach ($this->workflows as $id => $definition) {
            if ($definition->isExperimental()) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get all deprecated workflows
     *

     * @return array<string, WorkflowDefinition> Deprecated workflows indexed by ID
     */
    public function deprecated(): array
    {
        $result = [];
        foreach ($this->workflows as $id => $definition) {
            if ($definition->isDeprecated()) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Find workflows supporting a specific entity type
     *

     * @param string $entityType The entity type to search for
     *

     * @return array<string, WorkflowDefinition> Workflows supporting entity indexed by ID
     */
    public function supportingEntity(string $entityType): array
    {
        $result = [];
        foreach ($this->workflows as $id => $definition) {
            if ($definition->supportsEntityType($entityType)) {
                $result[$id] = $definition;
            }
        }

        return $result;
    }

    /**
     * Get workflows sorted by priority (highest first)
     *

     * @return array<string, WorkflowDefinition> Workflows sorted by priority
     */
    public function sortedByPriority(): array
    {
        $sorted = $this->workflows;
        usort($sorted, fn($a, $b) => $b->priority <=> $a->priority);

        return $sorted;
    }

    /**
     * Clear all registered workflows
     *

     * @return self Returns self for chaining
     */
    public function clear(): self
    {
        $this->workflows = [];
        $this->nameMap = [];
        $this->aliases = [];
        $this->moduleMap = [];
        $this->triggerMap = [];

        return $this;
    }

    /**
     * Before register extension point
     *

     * Called before a workflow is registered.
     * Override in child classes to add custom behavior.
     *

     * @param WorkflowDefinition $definition The workflow definition being registered
     *

     * @return void
     */
    protected function beforeRegister(WorkflowDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * After register extension point
     *

     * Called after a workflow is registered.
     * Override in child classes to add custom behavior.
     *

     * @param WorkflowDefinition $definition The workflow definition that was registered
     *

     * @return void
     */
    protected function afterRegister(WorkflowDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * Before unregister extension point
     *

     * Called before a workflow is unregistered.
     * Override in child classes to add custom behavior.
     *

     * @param WorkflowDefinition $definition The workflow definition being unregistered
     *

     * @return void
     */
    protected function beforeUnregister(WorkflowDefinition $definition): void
    {
        // Default implementation does nothing
    }

    /**
     * After unregister extension point
     *

     * Called after a workflow is unregistered.
     * Override in child classes to add custom behavior.
     *

     * @param WorkflowDefinition $definition The workflow definition that was unregistered
     *

     * @return void
     */
    protected function afterUnregister(WorkflowDefinition $definition): void
    {
        // Default implementation does nothing
    }
}
