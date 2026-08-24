<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Helpers;

/**
 * MockServiceProvider
 *
 * Minimal mock of Illuminate\Support\ServiceProvider for testing WBF service provider
 * without requiring a full Laravel installation.
 *
 * Provides the minimal interface needed for LaravelServiceProvider to function:
 * - $this->app (container)
 * - boot() and register() lifecycle methods
 * - mergeConfigFrom() for config merging
 * - publishes() for config publishing
 *
 * This mock is sufficient for testing service provider bindings.
 */
abstract class MockServiceProvider
{
    /**
     * The application container
     *
     * @var mixed
     */
    protected $app;

    /**
     * Published config paths
     *
     * @var array
     */
    protected array $publishedConfig = [];

    /**
     * Create a new service provider instance
     *
     * @param mixed $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Register any application services
     *
     * @return void
     */
    public function register(): void
    {
        // Override in subclass
    }

    /**
     * Bootstrap any application services
     *
     * @return void
     */
    public function boot(): void
    {
        // Override in subclass
    }

    /**
     * Merge configuration into the application config
     *
     * @param string $path
     * @param string $key
     * @return void
     */
    protected function mergeConfigFrom(string $path, string $key): void
    {
        // Mock implementation: just load the file
        if (file_exists($path)) {
            // In a real scenario, this would merge with the app config
        }
    }

    /**
     * Register config files for publishing
     *
     * @param array $paths
     * @param string|null $group
     * @return void
     */
    protected function publishes(array $paths, ?string $group = null): void
    {
        // Mock implementation: just store the paths
        $this->publishedConfig[$group ?? 'default'] = $paths;
    }

    /**
     * Get the services provided by the provider
     *
     * @return array
     */
    public function provides(): array
    {
        return [];
    }
}
