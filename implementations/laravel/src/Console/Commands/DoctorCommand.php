<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Console\Commands;

use WaysNX\BusinessFramework\Console\BaseWbfCommand;
use WaysNX\BusinessFramework\Registry\WorkflowRegistry;
use WaysNX\BusinessFramework\Registry\EntityRegistry;
use WaysNX\BusinessFramework\Registry\ModuleRegistry;
use WaysNX\BusinessFramework\Registry\ValidationRegistry;
use WaysNX\BusinessFramework\Registry\BusinessFunctionRegistry;
use WaysNX\BusinessFramework\Workflow\WorkflowEngine;
use WaysNX\BusinessFramework\Validation\ValidationFramework;
use WaysNX\BusinessFramework\Lifecycle\LifecycleManager;
use Symfony\Component\Console\Input\InputOption;

/**
 * DoctorCommand
 *
 * Diagnose WBF system health.
 *
 * Checks:
 * - Laravel service provider registration
 * - WBF configuration
 * - PHP version
 * - Laravel version
 * - All registries availability
 * - WorkflowEngine availability
 * - ValidationFramework availability
 * - LifecycleManager availability
 *
 * Usage:
 *   php artisan wbf:doctor
 *   php artisan wbf:doctor --json
 *   php artisan wbf:doctor --detail
 *   php artisan wbf:doctor --component WorkflowEngine
 *
 * @package WaysNX\BusinessFramework\Console\Commands
 */
class DoctorCommand extends BaseWbfCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wbf:doctor
        {--detail : Show detailed diagnostics}
        {--component= : Check specific component only}
        {--json : Output as JSON format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose WBF system health';

    /**
     * Verbose mode flag
     *
     * @var bool
     */
    private bool $verbose = false;

    /**
     * Health check results
     *
     * @var array
     */
    private array $checks = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->verbose = $this->option('detail') === true;
        $component = $this->option('component');

        try {
            // Run health checks
            $this->runChecks($component);

            // Output results
            return $this->reportResults();
        } catch (\Throwable $e) {
            return $this->error(
                "Doctor failed: {$e->getMessage()}",
                self::EXIT_SYSTEM_FAILURE,
                'system_error'
            );
        }
    }

    /**
     * Run health checks
     *
     * @param string|null $component Optional component to check
     * @return void
     */
    private function runChecks(?string $component = null): void
    {
        if (!$component || $component === 'php') {
            $this->checkPhpVersion();
        }

        if (!$component || $component === 'laravel') {
            $this->checkLaravelVersion();
        }

        if (!$component || $component === 'config') {
            $this->checkConfiguration();
        }

        if (!$component || $component === 'provider') {
            $this->checkServiceProvider();
        }

        if (!$component || $component === 'WorkflowRegistry') {
            $this->checkRegistry('workflow', WorkflowRegistry::class);
        }

        if (!$component || $component === 'EntityRegistry') {
            $this->checkRegistry('entity', EntityRegistry::class);
        }

        if (!$component || $component === 'ModuleRegistry') {
            $this->checkRegistry('module', ModuleRegistry::class);
        }

        if (!$component || $component === 'ValidationRegistry') {
            $this->checkRegistry('validation', ValidationRegistry::class);
        }

        if (!$component || $component === 'BusinessFunctionRegistry') {
            $this->checkRegistry('business-function', BusinessFunctionRegistry::class);
        }

        if (!$component || $component === 'WorkflowEngine') {
            $this->checkEngine(WorkflowEngine::class, 'WorkflowEngine');
        }

        if (!$component || $component === 'ValidationFramework') {
            $this->checkEngine(ValidationFramework::class, 'ValidationFramework');
        }

        if (!$component || $component === 'LifecycleManager') {
            $this->checkEngine(LifecycleManager::class, 'LifecycleManager');
        }
    }

    /**
     * Check PHP version
     *
     * @return void
     */
    private function checkPhpVersion(): void
    {
        $version = PHP_VERSION;
        $required = '8.3';
        $ok = version_compare($version, $required, '>=');

        $this->checks['php_version'] = [
            'name' => 'PHP Version',
            'ok' => $ok,
            'value' => $version,
            'required' => $required,
            'message' => $ok ? "OK" : "PHP {$required}+ required, found {$version}",
        ];
    }

    /**
     * Check Laravel version
     *
     * @return void
     */
    private function checkLaravelVersion(): void
    {
        try {
            $version = app()->version();
            $this->checks['laravel_version'] = [
                'name' => 'Laravel Version',
                'ok' => true,
                'value' => $version,
                'message' => "OK",
            ];
        } catch (\Throwable $e) {
            $this->checks['laravel_version'] = [
                'name' => 'Laravel Version',
                'ok' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check WBF configuration
     *
     * @return void
     */
    private function checkConfiguration(): void
    {
        try {
            $enabled = config('business-framework.enabled', true);
            $this->checks['configuration'] = [
                'name' => 'WBF Configuration',
                'ok' => true,
                'value' => 'Loaded',
                'message' => "OK" . ($enabled ? " (enabled)" : " (disabled)"),
            ];
        } catch (\Throwable $e) {
            $this->checks['configuration'] = [
                'name' => 'WBF Configuration',
                'ok' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check service provider registration
     *
     * @return void
     */
    private function checkServiceProvider(): void
    {
        try {
            $providerClass = 'WaysNX\\BusinessFramework\\ServiceProvider\\LaravelServiceProvider';
            $container = app();
            
            // Check if we're in a real Laravel application or lightweight test context
            $isLightweightTestContext = method_exists($container, 'runningUnitTests') && $container->runningUnitTests();
            
            if ($isLightweightTestContext) {
                // In lightweight test context: check config only, report as "lightweight context"
                $providers = config('app.providers', []);
                $found = in_array($providerClass, $providers, true);
                
                $this->checks['service_provider'] = [
                    'name' => 'Service Provider',
                    'ok' => $found,
                    'value' => $found ? 'Config (lightweight)' : 'Not Found',
                    'message' => $found 
                        ? 'OK (in config; registration not verifiable in lightweight test context)' 
                        : "FAILED: Provider not in config. Add to app.providers or use package auto-discovery",
                ];
            } else {
                // In real Laravel application: check actual provider registration
                $providers = config('app.providers', []);
                $inConfig = in_array($providerClass, $providers, true);
                
                // Check if provider was actually loaded by Laravel
                $loadedProviders = [];
                if (method_exists($container, 'getLoadedProviders')) {
                    $loadedProviders = $container->getLoadedProviders();
                    $isLoaded = isset($loadedProviders[$providerClass]);
                } else {
                    // Fallback: just check if provider is in config
                    $isLoaded = $inConfig;
                }
                
                $this->checks['service_provider'] = [
                    'name' => 'Service Provider',
                    'ok' => $isLoaded,
                    'value' => $isLoaded ? 'Registered' : ($inConfig ? 'Config' : 'Not Found'),
                    'message' => $isLoaded
                        ? 'OK (registered and loaded by Laravel)'
                        : ($inConfig
                            ? 'In config but not loaded. Check for errors during provider boot.'
                            : "FAILED: Provider not registered. Ensure WaysNX\\BusinessFramework\\ServiceProvider\\LaravelServiceProvider is in config/app.php providers or use package auto-discovery"),
                ];
            }
        } catch (\Throwable $e) {
            $this->checks['service_provider'] = [
                'name' => 'Service Provider',
                'ok' => false,
                'message' => "Failed to check provider: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Check registry availability
     *
     * @param string $name
     * @param string $class
     * @return void
     */
    private function checkRegistry(string $name, string $class): void
    {
        try {
            $registry = app($class);
            $this->checks["registry_{$name}"] = [
                'name' => ucfirst($name) . ' Registry',
                'ok' => true,
                'value' => 'Available',
                'message' => 'OK',
            ];
        } catch (\Throwable $e) {
            $this->checks["registry_{$name}"] = [
                'name' => ucfirst($name) . ' Registry',
                'ok' => false,
                'message' => "Failed to resolve: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Check engine availability
     *
     * @param string $class
     * @param string $name
     * @return void
     */
    private function checkEngine(string $class, string $name): void
    {
        try {
            $engine = app($class);
            $this->checks[strtolower($name)] = [
                'name' => $name,
                'ok' => true,
                'value' => 'Available',
                'message' => 'OK',
            ];
        } catch (\Throwable $e) {
            $this->checks[strtolower($name)] = [
                'name' => $name,
                'ok' => false,
                'message' => "Failed to resolve: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Report results
     *
     * @return int
     */
    private function reportResults(): int
    {
        $allOk = true;
        $results = [];

        foreach ($this->checks as $check) {
            if (!$check['ok']) {
                $allOk = false;
            }

            $results[] = [
                'name' => $check['name'],
                'status' => $check['ok'] ? 'OK' : 'FAILED',
                'message' => $check['message'],
                'value' => $check['value'] ?? '',
            ];
        }

        if ($this->jsonOutput) {
            return $this->outputJson('success', [
                'healthy' => $allOk,
                'checks' => $results,
            ]);
        }

        // Human-readable output
        $this->outputTable($results, ['name', 'status', 'message', 'value']);

        if ($allOk) {
            $this->info("\n✓ System is healthy");
            return self::EXIT_SUCCESS;
        } else {
            $this->errorOutput("\n✗ System has issues", self::EXIT_SYSTEM_FAILURE);
            return self::EXIT_SYSTEM_FAILURE;
        }
    }


}
