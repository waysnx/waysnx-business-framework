<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Console\Commands;

use WaysNX\BusinessFramework\Console\BaseWbfCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * RegisterCommand
 *
 * Verify that a WBF resource is registered in its registry.
 *
 * This is a validation/verification command, not a loader.
 * Resources are registered during application bootstrap via auto_discovery or explicit bootstrap code.
 *
 * Usage:
 *   php artisan wbf:register workflow employee-onboarding
 *   php artisan wbf:register business-function apply-leave
 *   php artisan wbf:register workflow employee-onboarding --json
 *   php artisan wbf:register workflow employee-onboarding --fail-if-not-found
 *
 * @package WaysNX\BusinessFramework\Console\Commands
 */
class RegisterCommand extends BaseWbfCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wbf:register 
        {type : Resource type (workflow, business-function, validation, module, entity)}
        {id : Resource ID}
        {--fail-if-not-found : Exit with error if not found}
        {--json : Output as JSON format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify a WBF resource is registered in its registry';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $type = $this->argument('type');
        $id = $this->argument('id');

        try {
            // Validate type
            if (!$this->isValidType($type)) {
                return $this->errorOutput(
                    "Invalid type: {$type}. Supported: workflow, business-function, validation, module, entity",
                    self::EXIT_INVALID_ARGUMENT,
                    'invalid_type'
                );
            }

            // Check registration
            return $this->checkRegistration($type, $id);
        } catch (\Throwable $e) {
            return $this->errorOutput(
                "Failed to check registration: {$e->getMessage()}",
                self::EXIT_SYSTEM_FAILURE,
                'system_error'
            );
        }
    }

    /**
     * Check if type is valid
     *
     * @param string $type
     * @return bool
     */
    private function isValidType(string $type): bool
    {
        return in_array($type, [
            'workflow',
            'business-function',
            'validation',
            'module',
            'entity',
        ], true);
    }

    /**
     * Check if a resource is registered
     *
     * @param string $type
     * @param string $id
     * @return int
     */
    private function checkRegistration(string $type, string $id): int
    {
        try {
            $found = $this->isResourceRegistered($type, $id);
            $data = [
                'type' => $type,
                'id' => $id,
                'found' => $found,
            ];

            if ($found) {
                // Resource found - return success
                if ($this->jsonOutput) {
                    return $this->outputJson('success', $data);
                }

                $this->info("✓ Resource registered: {$type}/{$id}");
                return self::EXIT_SUCCESS;
            } else {
                // Resource NOT found
                $failIfNotFound = $this->option('fail-if-not-found') === true;

                if ($failIfNotFound) {
                    // With --fail-if-not-found: return error
                    $exitCode = self::EXIT_NOT_FOUND;
                    $message = "Resource not found: {$type}/{$id}";
                    
                    if ($this->jsonOutput) {
                        return $this->outputJsonError(
                            $message,
                            $exitCode,
                            'not_found',
                            $data
                        );
                    }

                    return $this->errorOutput($message, $exitCode, 'not_found');
                } else {
                    // Without --fail-if-not-found: return success with warning
                    if ($this->jsonOutput) {
                        return $this->outputJson('success', array_merge($data, [
                            'warning' => 'Resource not found but --fail-if-not-found not specified',
                        ]));
                    }

                    $this->warnOutput("✗ Resource not found: {$type}/{$id}");
                    return self::EXIT_SUCCESS;
                }
            }
        } catch (\Throwable $e) {
            return $this->errorOutput(
                "Failed to check registration: {$e->getMessage()}",
                self::EXIT_SYSTEM_FAILURE,
                'system_error'
            );
        }
    }

    /**
     * Check if a resource is registered in its registry
     *
     * @param string $type
     * @param string $id
     * @return bool
     */
    private function isResourceRegistered(string $type, string $id): bool
    {
        try {
            return match ($type) {
                'workflow' => $this->checkWorkflowRegistration($id),
                'business-function' => $this->checkBusinessFunctionRegistration($id),
                'validation' => $this->checkValidationRegistration($id),
                'module' => $this->checkModuleRegistration($id),
                'entity' => $this->checkEntityRegistration($id),
                default => false,
            };
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if workflow is registered
     *
     * @param string $id
     * @return bool
     */
    private function checkWorkflowRegistration(string $id): bool
    {
        try {
            $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\WorkflowRegistry::class);
            return $registry->exists($id);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if business function is registered
     *
     * @param string $id
     * @return bool
     */
    private function checkBusinessFunctionRegistration(string $id): bool
    {
        try {
            $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\BusinessFunctionRegistry::class);
            return $registry->exists($id);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if validation is registered
     *
     * @param string $id
     * @return bool
     */
    private function checkValidationRegistration(string $id): bool
    {
        try {
            $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\ValidationRegistry::class);
            return $registry->exists($id);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if module is registered
     *
     * @param string $id
     * @return bool
     */
    private function checkModuleRegistration(string $id): bool
    {
        try {
            $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\ModuleRegistry::class);
            return $registry->exists($id);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if entity is registered
     *
     * @param string $id
     * @return bool
     */
    private function checkEntityRegistration(string $id): bool
    {
        try {
            $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\EntityRegistry::class);
            return $registry->exists($id);
        } catch (\Throwable $e) {
            return false;
        }
    }


}
