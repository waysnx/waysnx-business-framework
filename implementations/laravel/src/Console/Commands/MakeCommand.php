<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Console\Commands;

use WaysNX\BusinessFramework\Console\BaseWbfCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * MakeCommand
 *
 * Generate WBF artifacts (workflows, business functions, validations, modules, entities).
 *
 * Usage:
 *   php artisan wbf:make workflow <name>
 *   php artisan wbf:make business-function <name>
 *   php artisan wbf:make validation <name>
 *   php artisan wbf:make module <name>
 *   php artisan wbf:make entity <name>
 *
 * @package WaysNX\BusinessFramework\Console\Commands
 */
class MakeCommand extends BaseWbfCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wbf:make 
        {type : The type to generate (workflow, business-function, validation, module, entity)}
        {name : The name of the artifact}
        {--module= : Module name}
        {--domain= : Domain name (for workflows)}
        {--capability= : Capability name (for workflows)}
        {--description= : Description}
        {--namespace= : PHP namespace}
        {--f|force : Overwrite if exists}
        {--json : Output as JSON format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate WBF artifacts (workflow, business-function, validation, module, entity)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $type = $this->argument('type');
        $name = $this->argument('name');

        try {
            // Validate type
            if (!$this->isValidType($type)) {
                return $this->errorOutput(
                    "Invalid type: {$type}. Supported types: workflow, business-function, validation, module, entity",
                    self::EXIT_INVALID_ARGUMENT,
                    'invalid_type'
                );
            }

            // Validate name
            if (!$this->isValidName($name)) {
                return $this->errorOutput(
                    "Invalid name: {$name}. Name must be alphanumeric and start with a letter.",
                    self::EXIT_INVALID_ARGUMENT,
                    'invalid_name'
                );
            }

            // Delegate to type-specific generator
            $result = $this->generate($type, $name);

            if ($result['success']) {
                return $this->successOutput(
                    "Created {$type}: {$name}",
                    [
                        'files' => $result['files'] ?? [],
                        'type' => $type,
                    ]
                );
            } else {
                return $this->errorOutput(
                    $result['message'] ?? 'Generation failed',
                    $result['code'] ?? self::EXIT_ERROR,
                    $result['type'] ?? 'generation_error',
                    ['details' => $result['details'] ?? []]
                );
            }
        } catch (\Throwable $e) {
            return $this->errorOutput(
                "Failed to generate {$type}: {$e->getMessage()}",
                self::EXIT_SYSTEM_FAILURE,
                'system_error'
            );
        }
    }

    /**
     * Validate artifact type
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
     * Validate artifact name
     *
     * @param string $name
     * @return bool
     */
    private function isValidName(string $name): bool
    {
        return preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $name) === 1;
    }

    /**
     * Get default namespace for a resource type
     *
     * @param string $type
     * @return string
     */
    private function getDefaultNamespace(string $type): string
    {
        return match ($type) {
            'workflow' => 'App\\Business\\Workflows',
            'business-function' => 'App\\Business\\Functions',
            'validation' => 'App\\Business\\Validations',
            'module' => 'App\\Modules',
            'entity' => 'App\\Models',
            default => 'App\\Generated',
        };
    }

    /**
     * Get default output path for a resource type
     *
     * @param string $type
     * @return string
     */
    private function getDefaultOutputPath(string $type): string
    {
        $base = base_path('app');
        return match ($type) {
            'workflow' => $base . '/Business/Workflows',
            'business-function' => $base . '/Business/Functions',
            'validation' => $base . '/Business/Validations',
            'module' => $base . '/Modules',
            'entity' => $base . '/Models',
            default => $base . '/Generated',
        };
    }

    /**
     * Get files that will be generated by a generator (for conflict detection)
     *
     * Supports multi-file generators like workflow which generates 2 files.
     * This enables atomic --force checking: all files are checked before any are written.
     *
     * @param string $type
     * @param string $name
     * @param string $outputPath
     * @return array List of file paths that already exist
     */
    private function getConflictingFiles(string $type, string $name, string $outputPath): array
    {
        $conflicting = [];
        $className = $this->toPascalCase($name);

        // Determine which files will be generated
        $filesToGenerate = match ($type) {
            'workflow' => [
                $outputPath . '/' . $className . 'Definition.php',  // WorkflowDefinition
                $outputPath . '/' . 'Workflow' . $className . '.php', // Workflow model
            ],
            'business-function' => [
                $outputPath . '/' . $className . '.php',
            ],
            'validation' => [
                $outputPath . '/' . $className . 'Definition.php',
            ],
            'module' => [
                $outputPath . '/' . $className . 'Definition.php',
            ],
            'entity' => [
                $outputPath . '/' . $className . 'Definition.php',
            ],
            default => [],
        };

        // Check which files already exist
        foreach ($filesToGenerate as $filePath) {
            if (file_exists($filePath)) {
                $conflicting[] = $filePath;
            }
        }

        return $conflicting;
    }

    /**
     * Convert string to PascalCase (helper for conflict detection)
     *
     * @param string $string
     * @return string
     */
    private function toPascalCase(string $string): string
    {
        $parts = preg_split('/[-_]+/', $string);
        return implode('', array_map('ucfirst', $parts));
    }

    /**
     * Check if a generated file already exists
     *
     * @param string $outputPath
     * @param string $name
     * @return bool
     */
    private function fileAlreadyExists(string $outputPath, string $name): bool
    {
        $className = $this->toPascalCase($name);
        $filePath = $outputPath . '/' . $className . '.php';
        return file_exists($filePath);
    }

    /**
     * Generate the artifact
     *
     * @param string $type
     * @param string $name
     * @return array
     */
    private function generate(string $type, string $name): array
    {
        $module = $this->option('module') ?? 'default';
        $namespace = $this->option('namespace') ?? $this->getDefaultNamespace($type);
        $outputPath = $this->getDefaultOutputPath($type);

        try {
            $generator = $this->createGenerator($type, $name, $module, $namespace, $outputPath);
            
            if ($generator === null) {
                return [
                    'success' => false,
                    'message' => "Generator for {$type} not implemented yet",
                    'code' => self::EXIT_SYSTEM_FAILURE,
                ];
            }

            // Check for file conflicts BEFORE generating anything (atomic behavior)
            $force = $this->option('force') === true;
            $conflictingFiles = $this->getConflictingFiles($type, $name, $outputPath);
            
            if (!empty($conflictingFiles) && !$force) {
                $fileList = implode(', ', $conflictingFiles);
                return [
                    'success' => false,
                    'message' => "File(s) already exist: {$fileList}. Use --force to overwrite.",
                    'code' => self::EXIT_CONFLICT,
                    'type' => 'conflict',
                    'details' => ['files' => $conflictingFiles],
                ];
            }

            // Set additional options if applicable
            if ($type === 'workflow') {
                if ($this->option('domain')) {
                    $generator->setDomain($this->option('domain'));
                }
                if ($this->option('capability')) {
                    $generator->setCapability($this->option('capability'));
                }
            }

            if ($this->option('description')) {
                $generator->setDescription($this->option('description'));
            }

            $result = $generator->generate();

            if ($result->isSuccess()) {
                return [
                    'success' => true,
                    'files' => $result->getFiles(),
                    'message' => "Successfully generated {$type}: {$name}",
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result->getErrorMessage(),
                    'code' => self::EXIT_ERROR,
                    'type' => $result->getErrorType(),
                ];
            }
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => "Generation error: {$e->getMessage()}",
                'code' => self::EXIT_SYSTEM_FAILURE,
                'type' => 'system_error',
            ];
        }
    }

    /**
     * Create a generator for the artifact type
     *
     * @param string $type
     * @param string $name
     * @param string $module
     * @param string $namespace
     * @param string $outputPath
     * @return \WaysNX\BusinessFramework\Console\Generators\ArtifactGenerator|null
     */
    private function createGenerator(
        string $type,
        string $name,
        string $module,
        string $namespace,
        string $outputPath
    ): ?\WaysNX\BusinessFramework\Console\Generators\ArtifactGenerator {
        return match ($type) {
            'workflow' => new \WaysNX\BusinessFramework\Console\Generators\WorkflowGenerator(
                $name,
                $module,
                $namespace,
                $outputPath,
                false
            ),
            'business-function' => new \WaysNX\BusinessFramework\Console\Generators\BusinessFunctionGenerator(
                $name,
                $module,
                $namespace,
                $outputPath,
                false
            ),
            'validation' => new \WaysNX\BusinessFramework\Console\Generators\ValidationGenerator(
                $name,
                $module,
                $namespace,
                $outputPath,
                false
            ),
            'module' => new \WaysNX\BusinessFramework\Console\Generators\ModuleGenerator(
                $name,
                $module,
                $namespace,
                $outputPath,
                false
            ),
            'entity' => new \WaysNX\BusinessFramework\Console\Generators\EntityGenerator(
                $name,
                $module,
                $namespace,
                $outputPath,
                false
            ),
            default => null,
        };
    }
}
