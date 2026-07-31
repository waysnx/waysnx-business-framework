<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Traits;

/**
 * HasLifecycleHooks Trait
 *
 * Provides lifecycle hook extension points for entity operations.
 *
 * Lifecycle hooks allow child classes to execute custom logic at specific points
 * in an entity's lifecycle (creation, update, deletion) without modifying BaseModel.
 *
 * Responsibilities:
 * - Provide before/after hooks for create operations
 * - Provide before/after hooks for update operations
 * - Provide before/after hooks for delete operations
 * - Provide before/after hooks for validation
 * - Allow child classes to override hooks
 *
 * Hook Execution Order:
 * 1. beforeValidate()
 * 2. Validation occurs
 * 3. afterValidate()
 * 4. beforeCreate() / beforeUpdate() / beforeDelete()
 * 5. Operation occurs
 * 6. afterCreate() / afterUpdate() / afterDelete()
 *
 * Usage:
 * ```php
 * class MyEntity extends BaseModel {
 *     protected function beforeCreate(): void {
 *         // Custom logic before creation
 *     }
 *
 *     protected function afterCreate(): void {
 *         // Custom logic after creation
 *     }
 * }
 * ```
 *
 * @package WaysNX\BusinessFramework\Traits
 */
trait HasLifecycleHooks
{
    /**
     * Hook called before entity validation
     *
     * Override in child classes to customize validation preparation.
     *
     * @return void
     */
    protected function beforeValidate(): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity validation
     *
     * Override in child classes to customize post-validation logic.
     *
     * @return void
     */
    protected function afterValidate(): void
    {
        // Override in child class
    }

    /**
     * Hook called before entity creation
     *
     * Override in child classes to customize pre-creation logic.
     * Called after validation but before the entity is persisted.
     *
     * @return void
     */
    protected function beforeCreate(): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity creation
     *
     * Override in child classes to customize post-creation logic.
     * Called after the entity is persisted.
     *
     * @return void
     */
    protected function afterCreate(): void
    {
        // Override in child class
    }

    /**
     * Hook called before entity update
     *
     * Override in child classes to customize pre-update logic.
     * Called after validation but before the entity is updated.
     *
     * @return void
     */
    protected function beforeUpdate(): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity update
     *
     * Override in child classes to customize post-update logic.
     * Called after the entity is updated.
     *
     * @return void
     */
    protected function afterUpdate(): void
    {
        // Override in child class
    }

    /**
     * Hook called before entity deletion
     *
     * Override in child classes to customize pre-deletion logic.
     * Called before the entity is deleted (hard or soft delete).
     *
     * @return void
     */
    protected function beforeDelete(): void
    {
        // Override in child class
    }

    /**
     * Hook called after entity deletion
     *
     * Override in child classes to customize post-deletion logic.
     * Called after the entity is deleted.
     *
     * @return void
     */
    protected function afterDelete(): void
    {
        // Override in child class
    }

    /**
     * Execute validation hooks and validation
     *
     * Called internally to execute before/after validation hooks around
     * the validate() method. Child classes should override validate() to
     * implement actual validation rules.
     *
     * @return void
     */
    protected function executeValidation(): void
    {
        $this->beforeValidate();
        $this->validate();
        $this->afterValidate();
    }

    /**
     * Validation hook
     *
     * Override in child classes to implement validation rules.
     * Should throw an exception or return early if validation fails.
     *
     * @return void
     */
    protected function validate(): void
    {
        // Override in child class to implement validation
    }
}
