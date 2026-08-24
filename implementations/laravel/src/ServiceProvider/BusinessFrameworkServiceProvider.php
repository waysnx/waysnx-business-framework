<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\ServiceProvider;

/**
 * BusinessFrameworkServiceProvider
 *
 * Base service provider for the WaysNX Business Framework.
 *
 * This is a framework-agnostic base class that documents the bindings
 * needed for Laravel integration. Concrete implementations should extend
 * Illuminate\Support\ServiceProvider in their Laravel packages.
 *
 * Responsibilities:
 * - Register all framework dependencies with application's IoC container
 * - Bind singleton instances for stateful components (registries, managers, engines)
 * - Bind transient instances for stateless/per-use components
 * - Bind interfaces to implementations for dependency injection
 * - Boot registries and handlers during application bootstrap
 * - Publish configuration for customization
 *
 * Binding Strategy:
 * - SINGLETON: Registries, Managers, Engines (shared state across application)
 * - TRANSIENT: Base classes (instantiated per use)
 * - INTERFACE BINDING: For loose coupling and testability
 *
 * Usage (in Laravel 11):
 * ```php
 * namespace App\Providers;
 *
 * use Illuminate\Support\ServiceProvider;
 * use WaysNX\BusinessFramework\ServiceProvider\BusinessFrameworkServiceProvider;
 *
 * class AppServiceProvider extends ServiceProvider
 * {
 *     public function register(): void
 *     {
 *         // Delegate to framework provider
 *         $this->bindBusinessFramework();
 *     }
 *
 *     protected function bindBusinessFramework(): void
 *     {
 *         // See BINDINGS section below for what to register
 *     }
 * }
 * ```
 *
 * Or via dedicated provider:
 * ```php
 * class BusinessFrameworkServiceProvider extends \Illuminate\Support\ServiceProvider
 * {
 *     public function register(): void
 *     {
 *         $this->registerAllBindings();
 *     }
 * }
 * ```
 *
 * @package WaysNX\BusinessFramework\ServiceProvider
 */
class BusinessFrameworkServiceProvider
{
    /**
     * Get array of all services provided by this provider
     *
     * Applications use this to know what bindings are expected.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            // Registries
            'WaysNX\BusinessFramework\Registry\WorkflowRegistry',
            'WaysNX\BusinessFramework\Registry\EntityRegistry',
            'WaysNX\BusinessFramework\Registry\ModuleRegistry',
            'WaysNX\BusinessFramework\Registry\ValidationRegistry',
            'WaysNX\BusinessFramework\Registry\BusinessFunctionRegistry',

            // Managers
            'WaysNX\BusinessFramework\Lifecycle\LifecycleManager',

            // Execution Engines
            'WaysNX\BusinessFramework\Workflow\WorkflowEngine',
            'WaysNX\BusinessFramework\Validation\ValidationFramework',

            // Interface Bindings
            'WaysNX\BusinessFramework\Contracts\CollectionInterface',
            'WaysNX\BusinessFramework\Contracts\RepositoryInterface',
            'WaysNX\BusinessFramework\Contracts\ServiceInterface',
        ];
    }

    /**
     * Get service provider documentation
     *
     * @return string Human-readable documentation of bindings
     */
    public static function getBindingDocumentation(): string
    {
        return <<<'EOD'
WaysNX Business Framework Service Provider Bindings
====================================================

REGISTRIES (All Singleton):
1. WorkflowRegistry - Register/retrieve workflow definitions
2. EntityRegistry - Register/retrieve entity type definitions
3. ModuleRegistry - Register/retrieve module definitions
4. ValidationRegistry - Register/retrieve validation definitions
5. BusinessFunctionRegistry - Register/retrieve business function definitions

MANAGERS (All Singleton):
1. LifecycleManager - Manage and dispatch lifecycle event handlers

EXECUTION ENGINES (All Singleton):
1. WorkflowEngine - Orchestrate workflow execution
   - Depends on: WorkflowRegistry (required), LifecycleManager (optional)
2. ValidationFramework - Orchestrate validation execution
   - Depends on: ValidationRegistry (required), LifecycleManager (optional)

INTERFACE BINDINGS:
1. CollectionInterface → BaseCollection
2. RepositoryInterface → BaseRepository
3. ServiceInterface → BaseService

BINDING PATTERN (Laravel):
$this->app->singleton(WorkflowRegistry::class, function ($app) {
    return new WorkflowRegistry();
});

$this->app->singleton(LifecycleManager::class, function ($app) {
    return new LifecycleManager();
});

$this->app->singleton(WorkflowEngine::class, function ($app) {
    return new WorkflowEngine(
        $app->make(WorkflowRegistry::class),
        $app->make(LifecycleManager::class)
    );
});

CONFIGURATION:
- Merge config from: implementations/laravel/config/business-framework.php
- Key name: 'business-framework'
- Support environment overrides via env() helper

EXTENSION POINT:
Override bootRegistries() to populate registries during bootstrap with
workflow definitions, entity types, etc. from configuration or database.
EOD;
    }
}
