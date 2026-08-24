<?php

declare(strict_types=1);

/**
 * WaysNX Business Framework Configuration
 *
 * This configuration file provides settings for the WaysNX Business Framework
 * Laravel implementation.
 *
 * Sections:
 * - 'enabled': Enable/disable the framework
 * - 'registries': Configure registry behavior
 * - 'workflow': Workflow execution settings
 * - 'validation': Validation execution settings
 * - 'lifecycle': Lifecycle event handling
 * - 'definitions': Default definitions (workflows, entities, validations, etc.)
 *
 * Usage:
 * Access settings via config() helper:
 * ```php
 * config('business-framework.enabled')
 * config('business-framework.workflow.engine.timeout')
 * config('business-framework.definitions.workflows')
 * ```
 *
 * Publish to customize:
 * ```bash
 * php artisan vendor:publish --tag=business-framework-config
 * ```
 *
 * @package WaysNX\BusinessFramework\ServiceProvider
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Framework Enable/Disable
    |--------------------------------------------------------------------------
    |
    | Set to true to enable the Business Framework, false to disable.
    | Useful for feature flags or gradual migration.
    |
    | Default: true
    */
    'enabled' => env('BUSINESS_FRAMEWORK_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Registries Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for framework registries (Workflow, Entity, Module, etc.)
    |
    | - 'auto_discovery': Automatically discover definitions from config
    | - 'strict_mode': Throw exceptions on duplicate or invalid definitions
    |
    */
    'registries' => [
        'auto_discovery' => env('BUSINESS_FRAMEWORK_AUTO_DISCOVERY', false),
        'strict_mode' => env('BUSINESS_FRAMEWORK_STRICT_MODE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Workflow Engine Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for workflow execution behavior.
    |
    | - 'timeout': Maximum execution time in seconds (0 = unlimited)
    | - 'max_steps': Maximum steps per workflow (0 = unlimited)
    | - 'async_enabled': Enable asynchronous workflow execution via jobs
    |
    */
    'workflow' => [
        'engine' => [
            'timeout' => env('BUSINESS_FRAMEWORK_WORKFLOW_TIMEOUT', 0),
            'max_steps' => env('BUSINESS_FRAMEWORK_WORKFLOW_MAX_STEPS', 0),
            'async_enabled' => env('BUSINESS_FRAMEWORK_WORKFLOW_ASYNC', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Framework Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for validation execution behavior.
    |
    | - 'timeout': Maximum execution time in seconds (0 = unlimited)
    | - 'stop_on_error': Stop validation on first error
    | - 'max_issues': Maximum issues to report (0 = unlimited)
    |
    */
    'validation' => [
        'framework' => [
            'timeout' => env('BUSINESS_FRAMEWORK_VALIDATION_TIMEOUT', 0),
            'stop_on_error' => env('BUSINESS_FRAMEWORK_VALIDATION_STOP_ON_ERROR', false),
            'max_issues' => env('BUSINESS_FRAMEWORK_VALIDATION_MAX_ISSUES', 0),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Lifecycle Event Handling
    |--------------------------------------------------------------------------
    |
    | Settings for lifecycle event dispatching.
    |
    | - 'enabled': Enable/disable lifecycle event dispatching
    | - 'broadcast_events': Broadcast lifecycle events across application
    | - 'queue_events': Queue lifecycle events for async processing
    |
    */
    'lifecycle' => [
        'enabled' => env('BUSINESS_FRAMEWORK_LIFECYCLE_ENABLED', true),
        'broadcast_events' => env('BUSINESS_FRAMEWORK_BROADCAST_EVENTS', false),
        'queue_events' => env('BUSINESS_FRAMEWORK_QUEUE_EVENTS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Definitions
    |--------------------------------------------------------------------------
    |
    | Default workflow, entity, module, and validation definitions.
    | Applications typically override these via config files or database.
    |
    | Examples:
    | 'workflows' => [
    |     new WorkflowDefinition(id: 'emp-onboarding', ...),
    |     new WorkflowDefinition(id: 'emp-offboarding', ...),
    | ],
    | 'entities' => [
    |     new EntityDefinition(id: 'Employee', className: 'App\\Models\\Employee'),
    | ],
    | 'modules' => [
    |     new ModuleDefinition(id: 'hr', name: 'Human Resources'),
    | ],
    |
    | Empty by default; applications populate during bootstrap.
    */
    'definitions' => [
        'workflows' => [],
        'entities' => [],
        'modules' => [],
        'business_functions' => [],
        'validations' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging and Observability
    |--------------------------------------------------------------------------
    |
    | Settings for logging workflow and validation execution.
    |
    | - 'channel': Laravel log channel (default, stack, etc.)
    | - 'log_executions': Log all workflow/validation executions
    | - 'log_level': Log level (debug, info, warning, error)
    |
    */
    'logging' => [
        'channel' => env('BUSINESS_FRAMEWORK_LOG_CHANNEL', 'default'),
        'log_executions' => env('BUSINESS_FRAMEWORK_LOG_EXECUTIONS', false),
        'log_level' => env('BUSINESS_FRAMEWORK_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Tuning
    |--------------------------------------------------------------------------
    |
    | Performance-related settings.
    |
    | - 'cache_definitions': Cache registry definitions in Laravel cache
    | - 'cache_ttl': Cache TTL in seconds
    |
    */
    'performance' => [
        'cache_definitions' => env('BUSINESS_FRAMEWORK_CACHE_DEFINITIONS', false),
        'cache_ttl' => env('BUSINESS_FRAMEWORK_CACHE_TTL', 3600),
    ],
];
