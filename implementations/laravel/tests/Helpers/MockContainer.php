<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Helpers;

/**
 * MockContainer
 *
 * Minimal mock of Illuminate\Container\Container for testing WBF service provider
 * without requiring a full Laravel installation.
 *
 * Supports:
 * - singleton() registration
 * - bind() registration
 * - make() resolution with closure support
 * - Dependency injection through constructor
 *
 * This mock is sufficient for testing service provider bindings.
 */
class MockContainer
{
    /**
     * Registered singletons
     *
     * @var array
     */
    private array $singletons = [];

    /**
     * Singleton instances
     *
     * @var array
     */
    private array $instances = [];

    /**
     * Registered bindings
     *
     * @var array
     */
    private array $bindings = [];

    /**
     * Determine if a binding exists
     *
     * @param string $abstract
     * @return bool
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]);
    }

    /**
     * Determine if a binding is registered as singleton
     *
     * @param string $abstract
     * @return bool
     */
    public function isSingleton(string $abstract): bool
    {
        return isset($this->singletons[$abstract]);
    }

    /**
     * Register a singleton binding
     *
     * @param string $abstract
     * @param mixed $concrete
     * @return void
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->singletons[$abstract] = true;
        $this->bind($abstract, $concrete ?? $abstract);
    }

    /**
     * Register a binding
     *
     * @param string $abstract
     * @param mixed $concrete
     * @return void
     */
    public function bind(string $abstract, $concrete = null): void
    {
        $this->bindings[$abstract] = $concrete ?? $abstract;
    }

    /**
     * Resolve a binding from the container
     *
     * @param string $abstract
     * @return mixed
     * @throws \Exception
     */
    public function make(string $abstract)
    {
        // Check if already instantiated (singleton)
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Get the concrete binding
        if (!isset($this->bindings[$abstract])) {
            // Try to instantiate directly if it's a class
            if (class_exists($abstract)) {
                $concrete = $abstract;
            } else {
                throw new \Exception("No binding found for '{$abstract}'");
            }
        } else {
            $concrete = $this->bindings[$abstract];
        }

        // Resolve the concrete value
        if ($concrete instanceof \Closure) {
            $instance = $concrete($this);
        } elseif (is_string($concrete)) {
            // Try to instantiate the class
            $instance = $this->resolveClass($concrete);
        } else {
            $instance = $concrete;
        }

        // Store as singleton if registered
        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Resolve a class with constructor dependency injection
     *
     * @param string $className
     * @return object
     * @throws \Exception
     */
    private function resolveClass(string $className): object
    {
        if (!class_exists($className)) {
            throw new \Exception("Class '{$className}' does not exist");
        }

        $reflection = new \ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        // If no constructor, simply instantiate
        if ($constructor === null) {
            return new $className();
        }

        // Resolve constructor parameters
        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $paramType = $parameter->getType();

            if ($paramType === null) {
                // No type hint, use default if available
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception(
                        "Cannot resolve parameter '{$parameter->getName()}' in '{$className}': no type hint"
                    );
                }
            } else {
                $typeName = $paramType->getName();

                // Handle built-in types (array, string, int, etc.)
                if ($paramType->isBuiltin()) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        throw new \Exception(
                            "Cannot resolve builtin parameter '{$parameter->getName()}' of type '{$typeName}' in '{$className}': no default value"
                        );
                    }
                } else {
                    $isNullable = $paramType->allowsNull();

                    // Try to resolve from container
                    if ($isNullable && !isset($this->bindings[$typeName])) {
                        // Optional dependency not bound - use null
                        $dependencies[] = null;
                    } elseif ($isNullable && isset($this->bindings[$typeName])) {
                        // Optional dependency is bound - resolve it
                        $dependencies[] = $this->make($typeName);
                    } else {
                        // Required dependency - resolve it
                        $dependencies[] = $this->make($typeName);
                    }
                }
            }
        }

        return new $className(...$dependencies);
    }
}
