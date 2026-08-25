<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\ServiceProvider;

use Illuminate\Support\ServiceProvider;

use WaysNX\BusinessFramework\Registry\WorkflowRegistry;
use WaysNX\BusinessFramework\Registry\EntityRegistry;
use WaysNX\BusinessFramework\Registry\ModuleRegistry;
use WaysNX\BusinessFramework\Registry\ValidationRegistry;
use WaysNX\BusinessFramework\Registry\BusinessFunctionRegistry;

use WaysNX\BusinessFramework\Lifecycle\LifecycleManager;

use WaysNX\BusinessFramework\Workflow\WorkflowEngine;
use WaysNX\BusinessFramework\Validation\ValidationFramework;

use WaysNX\BusinessFramework\Collections\BaseCollection;
use WaysNX\BusinessFramework\Repositories\BaseRepository;
use WaysNX\BusinessFramework\Services\BaseService;

use WaysNX\BusinessFramework\Contracts\CollectionInterface;
use WaysNX\BusinessFramework\Contracts\RepositoryInterface;
use WaysNX\BusinessFramework\Contracts\ServiceInterface;

/**
 * LaravelServiceProvider
 *
 * Laravel integration service provider for the WaysNX Business Framework.
 *
 * This provider registers all WBF components with Laravel's IoC container,
 * merges configuration, and sets up package publishing.
 *
 * Responsibilities:
 * - Register all WBF services with Laravel's container
 * - Merge configuration file
 * - Publish configuration for customization
 * - Manage service provider lifecycle (register → boot)
 *
 * Registration Strategy:
 * - SINGLETON: All Registries, LifecycleManager, Engines (stateful, shared)
 * - TRANSIENT: Interface bindings to base classes (instantiated per use)
 *
 * The base BusinessFrameworkServiceProvider documents the bindings conceptually.
 * This provider implements the actual Laravel integration.
 *
 * Usage:
 * This provider is automatically discovered by Laravel via composer.json.
 * No manual registration needed in Laravel 11+.
 *
 * Access resolved services:
 * ```php
 * // In controllers, commands, etc.
 * $workflow = app(WorkflowRegistry::class);
 * $engine = app(WorkflowEngine::class);
 * $collection = app(CollectionInterface::class);
 * ```
 *
 * @package WaysNX\BusinessFramework\ServiceProvider
 */
class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Base service provider for documentation
     *
     * @var BusinessFrameworkServiceProvider
     */
    protected BusinessFrameworkServiceProvider $baseProvider;

    /**
     * Bootstrap services
     *
     * Called after all services have been registered.
     *
     * @return void
     */
    public function boot(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            $this->getConfigPath(),
            'business-framework'
        );

        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->getConfigPath() => config_path('business-framework.php'),
            ], 'business-framework-config');

            // Register CLI commands
            $this->registerCommands();
        }
    }

    /**
     * Register services
     *
     * Called when the service provider is registered.
     * Register all bindings with the container here.
     *
     * @return void
     */
    public function register(): void
    {
        // Initialize base provider
        $this->baseProvider = new BusinessFrameworkServiceProvider($this->app);

        // Register all repositories (singletons)
        $this->registerRegistries();

        // Register lifecycle manager (singleton)
        $this->registerLifecycleManager();

        // Register execution engines (singletons, depend on registries)
        $this->registerEngines();

        // Register interface bindings (transient)
        $this->registerInterfaces();
    }

    /**
     * Get the services provided by the provider
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return $this->baseProvider->provides();
    }

    /**
     * Register all registry services
     *
     * All registries are singletons (stateful, shared across app).
     * Each registry maintains its own in-memory definitions store.
     * No external dependencies.
     *
     * @return void
     */
    protected function registerRegistries(): void
    {
        // WorkflowRegistry: singleton, no dependencies
        $this->app->singleton(WorkflowRegistry::class, function () {
            return new WorkflowRegistry();
        });

        // EntityRegistry: singleton, no dependencies
        $this->app->singleton(EntityRegistry::class, function () {
            return new EntityRegistry();
        });

        // ModuleRegistry: singleton, no dependencies
        $this->app->singleton(ModuleRegistry::class, function () {
            return new ModuleRegistry();
        });

        // ValidationRegistry: singleton, no dependencies
        $this->app->singleton(ValidationRegistry::class, function () {
            return new ValidationRegistry();
        });

        // BusinessFunctionRegistry: singleton, no dependencies
        $this->app->singleton(BusinessFunctionRegistry::class, function () {
            return new BusinessFunctionRegistry();
        });
    }

    /**
     * Register lifecycle manager
     *
     * Singleton. No dependencies. Manages event handlers across framework.
     *
     * @return void
     */
    protected function registerLifecycleManager(): void
    {
        $this->app->singleton(LifecycleManager::class, function () {
            return new LifecycleManager();
        });
    }

    /**
     * Register execution engines
     *
     * Both engines are singletons (expensive to instantiate, expensive to recreate).
     * WorkflowEngine and ValidationFramework depend on:
     * - Required registry (WorkflowRegistry, ValidationRegistry)
     * - Optional LifecycleManager (injected if bound)
     *
     * @return void
     */
    protected function registerEngines(): void
    {
        // WorkflowEngine: singleton, depends on WorkflowRegistry and LifecycleManager
        $this->app->singleton(WorkflowEngine::class, function ($app) {
            $registry = $app->make(WorkflowRegistry::class);
            $lifecycleManager = $app->make(LifecycleManager::class);

            return new WorkflowEngine($registry, $lifecycleManager);
        });

        // ValidationFramework: singleton, depends on ValidationRegistry and LifecycleManager
        $this->app->singleton(ValidationFramework::class, function ($app) {
            $registry = $app->make(ValidationRegistry::class);
            $lifecycleManager = $app->make(LifecycleManager::class);

            return new ValidationFramework($registry, $lifecycleManager);
        });
    }

    /**
     * Register interface bindings
     *
     * All interface bindings are transient (new instance per resolution).
     * - CollectionInterface → BaseCollection
     * - RepositoryInterface → BaseRepository
     * - ServiceInterface → BaseService (depends on RepositoryInterface)
     *
     * Note: BaseRepository and BaseService are not bound directly as singletons
     * because they require constructor parameters that vary per use case.
     * Applications subclass these or bind implementations for specific models.
     *
     * @return void
     */
    protected function registerInterfaces(): void
    {
        // CollectionInterface → BaseCollection (transient, no dependencies)
        $this->app->bind(CollectionInterface::class, BaseCollection::class);

        // RepositoryInterface → BaseRepository (transient, no dependencies)
        $this->app->bind(RepositoryInterface::class, BaseRepository::class);

        // ServiceInterface → BaseService (transient, depends on RepositoryInterface)
        $this->app->bind(ServiceInterface::class, function ($app) {
            $repository = $app->make(RepositoryInterface::class);
            return new BaseService($repository);
        });
    }

    /**
     * Get the path to the configuration file
     *
     * @return string
     */
    protected function getConfigPath(): string
    {
        return dirname(__DIR__, 2) . '/config/business-framework.php';
    }

    /**
     * Register CLI commands
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        $this->commands([
            \WaysNX\BusinessFramework\Console\Commands\MakeCommand::class,
            \WaysNX\BusinessFramework\Console\Commands\ListCommand::class,
            \WaysNX\BusinessFramework\Console\Commands\ShowCommand::class,
            \WaysNX\BusinessFramework\Console\Commands\RegisterCommand::class,
            \WaysNX\BusinessFramework\Console\Commands\DoctorCommand::class,
        ]);
    }
}
