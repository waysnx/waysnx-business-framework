<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Console\Commands;

use WaysNX\BusinessFramework\Console\BaseWbfCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * ShowCommand
 *
 * Show details of a registered WBF resource.
 *
 * Usage:
 *   php artisan wbf:show workflow employee-onboarding
 *   php artisan wbf:show business-function apply-leave
 *   php artisan wbf:show workflow employee-onboarding --json
 *
 * @package WaysNX\BusinessFramework\Console\Commands
 */
class ShowCommand extends BaseWbfCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wbf:show 
        {type : Resource type (workflow, business-function, validation, module, entity)}
        {id : Resource ID}
        {--json : Output as JSON format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show details of a WBF resource';

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

            // Show the resource
            return $this->showResource($type, $id);
        } catch (\Throwable $e) {
            return $this->errorOutput(
                "Failed to show resource: {$e->getMessage()}",
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
     * Show a resource
     *
     * @param string $type
     * @param string $id
     * @return int
     */
    private function showResource(string $type, string $id): int
    {
        try {
            $data = $this->getResourceData($type, $id);

            if ($data === null) {
                return $this->errorOutput(
                    "Resource not found: {$type}/{$id}",
                    self::EXIT_NOT_FOUND,
                    'not_found'
                );
            }

            if ($this->jsonOutput) {
                return $this->outputJson('success', $data);
            }

            // Human-readable output
            $this->info("Resource: {$type}/{$id}");
            $this->line('');

            foreach ($data as $key => $value) {
                $formattedKey = str_replace('_', ' ', ucfirst($key));
                if (is_array($value)) {
                    $this->line("<info>{$formattedKey}:</info>");
                    foreach ($value as $v) {
                        $this->line("  - {$v}");
                    }
                } else {
                    $this->line("<info>{$formattedKey}:</info> {$value}");
                }
            }

            return self::EXIT_SUCCESS;
        } catch (\Throwable $e) {
            return $this->errorOutput(
                "Failed to show resource: {$e->getMessage()}",
                self::EXIT_SYSTEM_FAILURE,
                'system_error'
            );
        }
    }

    /**
     * Get resource data by type and ID
     *
     * @param string $type
     * @param string $id
     * @return array|null
     */
    private function getResourceData(string $type, string $id): ?array
    {
        try {
            return match ($type) {
                'workflow' => $this->getWorkflowData($id),
                'business-function' => $this->getBusinessFunctionData($id),
                'validation' => $this->getValidationData($id),
                'module' => $this->getModuleData($id),
                'entity' => $this->getEntityData($id),
                default => null,
            };
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get workflow data by ID
     *
     * @param string $id
     * @return array|null
     */
    private function getWorkflowData(string $id): ?array
    {
        try {
            $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\WorkflowRegistry::class);
            $workflow = $registry->findById($id);

            return [
                'id' => $workflow->id,
                'name' => $workflow->name,
                'display_name' => $workflow->displayName,
                'description' => $workflow->description,
                'module' => $workflow->moduleId,
                'category' => $workflow->category,
                'version' => $workflow->version,
                'trigger_type' => $workflow->triggerType,
                'trigger_event' => $workflow->triggerEvent,
                'entry_function' => $workflow->entryFunction,
                'exit_function' => $workflow->exitFunction,
                'priority' => $workflow->priority,
                'enabled' => $workflow->enabled ? 'yes' : 'no',
                'tags' => implode(', ', $workflow->tags),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get business function data by ID
     *
     * @param string $id
     * @return array|null
     */
    private function getBusinessFunctionData(string $id): ?array
    {
        try {
            $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\BusinessFunctionRegistry::class);
            $function = $registry->findById($id);

            return [
                'id' => $function->id,
                'name' => $function->name,
                'display_name' => $function->displayName,
                'description' => $function->description,
                'module' => $function->moduleId,
                'category' => $function->category,
                'version' => $function->version,
                'priority' => $function->priority,
                'enabled' => $function->enabled ? 'yes' : 'no',
                'tags' => implode(', ', $function->tags),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get validation data by ID
     *
     * @param string $id
     * @return array|null
     */
    private function getValidationData(string $id): ?array
    {
        try {
            $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\ValidationRegistry::class);
            $validation = $registry->findById($id);

            return [
                'id' => $validation->id,
                'name' => $validation->name,
                'display_name' => $validation->displayName,
                'description' => $validation->description,
                'module' => $validation->moduleId,
                'scope' => $validation->scope,
                'version' => $validation->version,
                'severity' => $validation->severity,
                'priority' => $validation->priority,
                'enabled' => $validation->enabled ? 'yes' : 'no',
                'tags' => implode(', ', $validation->tags),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get module data by ID
     *
     * @param string $id
     * @return array|null
     */
    private function getModuleData(string $id): ?array
    {
        try {
            $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\ModuleRegistry::class);
            $module = $registry->findById($id);

            return [
                'id' => $module->id,
                'name' => $module->name,
                'display_name' => $module->displayName,
                'description' => $module->description,
                'namespace' => $module->namespace,
                'version' => $module->version,
                'author' => $module->author,
                'category' => $module->category,
                'priority' => $module->priority,
                'enabled' => $module->enabled ? 'yes' : 'no',
                'dependencies' => implode(', ', $module->dependencies),
                'tags' => implode(', ', $module->tags),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get entity data by ID
     *
     * @param string $id
     * @return array|null
     */
    private function getEntityData(string $id): ?array
    {
        try {
            $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\EntityRegistry::class);
            $entity = $registry->findById($id);

            return [
                'id' => $entity->id,
                'name' => $entity->name,
                'display_name' => $entity->displayName,
                'description' => $entity->description,
                'class_name' => $entity->className,
                'namespace' => $entity->namespace,
                'version' => $entity->version,
                'tags' => implode(', ', $entity->tags),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }


}
