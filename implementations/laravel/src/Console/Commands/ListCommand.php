<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Console\Commands;

use WaysNX\BusinessFramework\Console\BaseWbfCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * ListCommand
 *
 * List registered WBF resources (workflows, business functions, validations, modules, entities).
 *
 * Usage:
 *   php artisan wbf:list workflows
 *   php artisan wbf:list business-functions
 *   php artisan wbf:list validations
 *   php artisan wbf:list modules
 *   php artisan wbf:list entities
 *   php artisan wbf:list workflows --json
 *   php artisan wbf:list workflows --detailed
 *
 * @package WaysNX\BusinessFramework\Console\Commands
 */
class ListCommand extends BaseWbfCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wbf:list 
        {resource? : Resource type (workflows, business-functions, validations, modules, entities)}
        {--d|detailed : Show detailed information}
        {--filter= : Filter by name or ID}
        {--module= : Filter by module}
        {--json : Output as JSON format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List registered WBF resources';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $resource = $this->argument('resource');

        try {
            // If no resource specified, show available resources
            if (!$resource) {
                return $this->listAvailableResources();
            }

            // Validate resource type
            if (!$this->isValidResource($resource)) {
                return $this->errorOutput(
                    "Invalid resource: {$resource}. Supported: workflows, business-functions, validations, modules, entities",
                    self::EXIT_INVALID_ARGUMENT,
                    'invalid_resource'
                );
            }

            // List the resource
            return $this->listResource($resource);
        } catch (\Throwable $e) {
            return $this->errorOutput(
                "Failed to list resources: {$e->getMessage()}",
                self::EXIT_SYSTEM_FAILURE,
                'system_error'
            );
        }
    }

    /**
     * List available resources
     *
     * @return int
     */
    private function listAvailableResources(): int
    {
        $resources = [
            'workflows',
            'business-functions',
            'validations',
            'modules',
            'entities',
        ];

        if ($this->jsonOutput) {
            return $this->outputJson('success', [
                'resources' => $resources,
            ]);
        }

        $this->line('<info>Available resources:</info>');
        foreach ($resources as $resource) {
            $this->line("  - {$resource}");
        }

        return self::EXIT_SUCCESS;
    }

    /**
     * Check if resource type is valid
     *
     * @param string $resource
     * @return bool
     */
    private function isValidResource(string $resource): bool
    {
        return in_array($resource, [
            'workflows',
            'business-functions',
            'validations',
            'modules',
            'entities',
        ], true);
    }

    /**
     * List a specific resource
     *
     * @param string $resource
     * @return int
     */
    private function listResource(string $resource): int
    {
        try {
            $items = $this->getResourceItems($resource);

            $data = [
                'count' => count($items),
                'items' => $items,
            ];

            if ($this->jsonOutput) {
                return $this->outputJson('success', $data);
            }

            // Human-readable output
            $this->info("Registered {$resource}:");
            if (empty($items)) {
                $this->line('  (none)');
            } else {
                foreach ($items as $item) {
                    $this->line('  - ' . $item['id']);
                    if ($this->option('detailed')) {
                        $this->line('      ' . ($item['displayName'] ?? $item['name'] ?? ''));
                    }
                }
            }

            $this->line("\nTotal: " . count($items));
            return self::EXIT_SUCCESS;
        } catch (\Throwable $e) {
            return $this->errorOutput(
                "Failed to list {$resource}: {$e->getMessage()}",
                self::EXIT_SYSTEM_FAILURE,
                'system_error'
            );
        }
    }

    /**
     * Get items for a resource type from the registry
     *
     * @param string $resource
     * @return array
     */
    private function getResourceItems(string $resource): array
    {
        $items = [];

        try {
            match ($resource) {
                'workflows' => $items = $this->getWorkflows(),
                'business-functions' => $items = $this->getBusinessFunctions(),
                'validations' => $items = $this->getValidations(),
                'modules' => $items = $this->getModules(),
                'entities' => $items = $this->getEntities(),
            };
        } catch (\Throwable $e) {
            // Registry not available yet (empty in non-bootstrapped environment)
            $items = [];
        }

        return $items;
    }

    /**
     * Get workflows from registry
     *
     * @return array
     */
    private function getWorkflows(): array
    {
        $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\WorkflowRegistry::class);
        $workflows = $registry->all();

        return array_map(function ($workflow) {
            return [
                'id' => $workflow->id,
                'name' => $workflow->name,
                'displayName' => $workflow->displayName,
                'moduleId' => $workflow->moduleId,
                'triggerType' => $workflow->triggerType,
                'enabled' => $workflow->enabled,
            ];
        }, $workflows);
    }

    /**
     * Get business functions from registry
     *
     * @return array
     */
    private function getBusinessFunctions(): array
    {
        $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\BusinessFunctionRegistry::class);
        $functions = $registry->all();

        return array_map(function ($function) {
            return [
                'id' => $function->id,
                'name' => $function->name,
                'displayName' => $function->displayName,
                'moduleId' => $function->moduleId,
                'category' => $function->category,
                'enabled' => $function->enabled,
            ];
        }, $functions);
    }

    /**
     * Get validations from registry
     *
     * @return array
     */
    private function getValidations(): array
    {
        $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\ValidationRegistry::class);
        $validations = $registry->all();

        return array_map(function ($validation) {
            return [
                'id' => $validation->id,
                'name' => $validation->name,
                'displayName' => $validation->displayName,
                'moduleId' => $validation->moduleId,
                'scope' => $validation->scope,
                'enabled' => $validation->enabled,
            ];
        }, $validations);
    }

    /**
     * Get modules from registry
     *
     * @return array
     */
    private function getModules(): array
    {
        $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\ModuleRegistry::class);
        $modules = $registry->all();

        return array_map(function ($module) {
            return [
                'id' => $module->id,
                'name' => $module->name,
                'displayName' => $module->displayName,
                'namespace' => $module->namespace,
                'version' => $module->version,
                'enabled' => $module->enabled,
            ];
        }, $modules);
    }

    /**
     * Get entities from registry
     *
     * @return array
     */
    private function getEntities(): array
    {
        $registry = $this->resolve(\WaysNX\BusinessFramework\Registry\EntityRegistry::class);
        $entities = $registry->all();

        return array_map(function ($entity) {
            return [
                'id' => $entity->id,
                'name' => $entity->name,
                'displayName' => $entity->displayName,
                'className' => $entity->className,
            ];
        }, $entities);
    }


}
