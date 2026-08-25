<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Console;

use Illuminate\Console\Application as Artisan;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\ServiceProvider\LaravelServiceProvider;

/**
 * ConsoleTestCase
 *
 * Base class for CLI command tests.
 *
 * Provides:
 * - Real Laravel container
 * - Artisan console instance
 * - WBF service provider registration
 * - Command execution helpers
 *
 * @package WaysNX\BusinessFramework\Tests\Console
 */
class ConsoleTestCase extends TestCase
{
    /**
     * Laravel container
     *
     * @var Container
     */
    protected Container $container;

    /**
     * Artisan console application
     *
     * @var Artisan
     */
    protected Artisan $artisan;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create real Laravel container (extends it with test support)
        $this->container = new class extends Container {
            public function runningUnitTests() {
                return true;
            }
        };

        // Set the container as the global app() container
        Container::setInstance($this->container);

        // Bootstrap config BEFORE registering provider (so provider's mergeConfigFrom finds it)
        $this->container->singleton('config', function() {
            return new class {
                private $data = [
                    'app' => [
                        'name' => 'WBF',
                        'version' => '2.0.0',
                        'providers' => [
                            'WaysNX\\BusinessFramework\\ServiceProvider\\LaravelServiceProvider',
                        ],
                        'debug' => true,
                    ],
                    'business-framework' => [
                        'enabled' => true,
                        'registries' => [
                            'auto_discovery' => false,
                            'strict_mode' => true,
                        ],
                    ],
                ];

                public function get($key, $default = null) {
                    $keys = explode('.', $key);
                    $value = $this->data;
                    foreach ($keys as $k) {
                        if (is_array($value) && isset($value[$k])) {
                            $value = $value[$k];
                        } else {
                            return $default;
                        }
                    }
                    return $value;
                }

                public function has($key) {
                    return $this->get($key) !== null;
                }

                public function all() {
                    return $this->data;
                }

                public function set($key, $value) {
                    $keys = explode('.', $key);
                    $current = &$this->data;
                    foreach ($keys as $k) {
                        if (!isset($current[$k])) {
                            $current[$k] = [];
                        }
                        $current = &$current[$k];
                    }
                    $current = $value;
                }

                public function merge($key, $data) {
                    // Support config merge from provider
                    if (isset($this->data[$key])) {
                        $this->data[$key] = array_merge($this->data[$key], $data);
                    } else {
                        $this->data[$key] = $data;
                    }
                }
            };
        });

        // Register event dispatcher
        $this->container->singleton('events', function ($app) {
            return new Dispatcher($app);
        });

        // Create a minimal service provider that doesn't try to merge config file
        // Register all services directly
        $this->registerWBFServices();

        // Create Artisan console with real container
        $this->artisan = new Artisan($this->container, $this->container['events'], 'test');
        
        // Add commands
        $this->artisan->add($this->container->make(\WaysNX\BusinessFramework\Console\Commands\MakeCommand::class));
        $this->artisan->add($this->container->make(\WaysNX\BusinessFramework\Console\Commands\ListCommand::class));
        $this->artisan->add($this->container->make(\WaysNX\BusinessFramework\Console\Commands\ShowCommand::class));
        $this->artisan->add($this->container->make(\WaysNX\BusinessFramework\Console\Commands\RegisterCommand::class));
        $this->artisan->add($this->container->make(\WaysNX\BusinessFramework\Console\Commands\DoctorCommand::class));
    }

    /**
     * Register WBF services without loading config file
     *
     * @return void
     */
    private function registerWBFServices(): void
    {
        // Register all registries (singletons)
        $this->container->singleton(\WaysNX\BusinessFramework\Registry\WorkflowRegistry::class, function () {
            return new \WaysNX\BusinessFramework\Registry\WorkflowRegistry();
        });

        $this->container->singleton(\WaysNX\BusinessFramework\Registry\EntityRegistry::class, function () {
            return new \WaysNX\BusinessFramework\Registry\EntityRegistry();
        });

        $this->container->singleton(\WaysNX\BusinessFramework\Registry\ModuleRegistry::class, function () {
            return new \WaysNX\BusinessFramework\Registry\ModuleRegistry();
        });

        $this->container->singleton(\WaysNX\BusinessFramework\Registry\ValidationRegistry::class, function () {
            return new \WaysNX\BusinessFramework\Registry\ValidationRegistry();
        });

        $this->container->singleton(\WaysNX\BusinessFramework\Registry\BusinessFunctionRegistry::class, function () {
            return new \WaysNX\BusinessFramework\Registry\BusinessFunctionRegistry();
        });

        // Register lifecycle manager
        $this->container->singleton(\WaysNX\BusinessFramework\Lifecycle\LifecycleManager::class, function () {
            return new \WaysNX\BusinessFramework\Lifecycle\LifecycleManager();
        });

        // Register engines
        $this->container->singleton(\WaysNX\BusinessFramework\Workflow\WorkflowEngine::class, function ($app) {
            $registry = $app->make(\WaysNX\BusinessFramework\Registry\WorkflowRegistry::class);
            $lifecycleManager = $app->make(\WaysNX\BusinessFramework\Lifecycle\LifecycleManager::class);
            return new \WaysNX\BusinessFramework\Workflow\WorkflowEngine($registry, $lifecycleManager);
        });

        $this->container->singleton(\WaysNX\BusinessFramework\Validation\ValidationFramework::class, function ($app) {
            $registry = $app->make(\WaysNX\BusinessFramework\Registry\ValidationRegistry::class);
            $lifecycleManager = $app->make(\WaysNX\BusinessFramework\Lifecycle\LifecycleManager::class);
            return new \WaysNX\BusinessFramework\Validation\ValidationFramework($registry, $lifecycleManager);
        });

        // Register interface bindings
        $this->container->bind(\WaysNX\BusinessFramework\Contracts\CollectionInterface::class, \WaysNX\BusinessFramework\Collections\BaseCollection::class);
        $this->container->bind(\WaysNX\BusinessFramework\Contracts\RepositoryInterface::class, \WaysNX\BusinessFramework\Repositories\BaseRepository::class);
        $this->container->bind(\WaysNX\BusinessFramework\Contracts\ServiceInterface::class, function ($app) {
            $repository = $app->make(\WaysNX\BusinessFramework\Contracts\RepositoryInterface::class);
            return new \WaysNX\BusinessFramework\Services\BaseService($repository);
        });
    }

    /**
     * Call an Artisan command
     *
     * @param string $command Command name
     * @param array $parameters Command parameters
     * @param array $options Command options
     * @return int Exit code
     */
    protected function call(string $command, array $parameters = [], array $options = []): int
    {
        // Build full command with parameters and options
        $input = $command;
        foreach ($parameters as $param) {
            $input .= " " . escapeshellarg($param);
        }
        foreach ($options as $key => $value) {
            if ($value === true) {
                $input .= " --{$key}";
            } elseif ($value !== false) {
                $input .= " --{$key}=" . escapeshellarg($value);
            }
        }

        $tester = $this->artisan->find($command);
        $input = new \Symfony\Component\Console\Input\StringInput($input);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();

        return $tester->run($input, $output);
    }

    /**
     * Tear down test environment
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Clear the global container instance
        Container::setInstance(null);
        parent::tearDown();
    }

    /**
     * Get the last command output
     *
     * @return string
     */
    protected function getOutput(): string
    {
        // Note: This would require capturing output from Artisan
        // For now, return empty string
        return '';
    }

    /**
     * Assert command succeeded
     *
     * @param int $exitCode
     * @return void
     */
    protected function assertCommandSucceeded(int $exitCode): void
    {
        $this->assertEquals(0, $exitCode, 'Command should exit with code 0');
    }

    /**
     * Assert command failed
     *
     * @param int $exitCode
     * @param int $expectedCode Optional expected code
     * @return void
     */
    protected function assertCommandFailed(int $exitCode, int $expectedCode = null): void
    {
        if ($expectedCode !== null) {
            $this->assertEquals($expectedCode, $exitCode, "Command should exit with code {$expectedCode}");
        } else {
            $this->assertNotEquals(0, $exitCode, 'Command should not exit with code 0');
        }
    }
}
