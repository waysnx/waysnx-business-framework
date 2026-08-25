<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Console\Generators;

/**
 * ArtifactGenerator
 *
 * Base class for WBF artifact generators.
 *
 * Provides common generation logic for all artifact types.
 *
 * @package WaysNX\BusinessFramework\Console\Generators
 */
abstract class ArtifactGenerator
{
    /**
     * The artifact name
     *
     * @var string
     */
    protected string $name;

    /**
     * The artifact type
     *
     * @var string
     */
    protected string $type;

    /**
     * The module name
     *
     * @var string
     */
    protected string $module;

    /**
     * The namespace
     *
     * @var string
     */
    protected string $namespace;

    /**
     * The output path
     *
     * @var string
     */
    protected string $outputPath;

    /**
     * Whether to generate tests
     *
     * @var bool
     */
    protected bool $generateTests;

    /**
     * Initialize the generator
     *
     * @param string $name
     * @param string $module
     * @param string $namespace
     * @param string $outputPath
     * @param bool $generateTests
     */
    public function __construct(
        string $name,
        string $module,
        string $namespace,
        string $outputPath,
        bool $generateTests = true
    ) {
        $this->name = $name;
        $this->module = $module;
        $this->namespace = $namespace;
        $this->outputPath = $outputPath;
        $this->generateTests = $generateTests;
    }

    /**
     * Generate the artifact
     *
     * @return GenerationResult
     */
    abstract public function generate(): GenerationResult;

    /**
     * Generate the PHP class code
     *
     * @param string $classContent
     * @return string
     */
    protected function generateClassFile(string $classContent): string
    {
        return "<?php\n\n" . $classContent;
    }

    /**
     * Get the class name for the artifact
     *
     * @return string
     */
    protected function getClassName(): string
    {
        return $this->toPascalCase($this->name);
    }

    /**
     * Get the definition class name
     *
     * @return string
     */
    protected function getDefinitionClassName(): string
    {
        return $this->getClassName() . 'Definition';
    }

    /**
     * Convert string to PascalCase
     *
     * @param string $string
     * @return string
     */
    protected function toPascalCase(string $string): string
    {
        $parts = preg_split('/[-_]+/', $string);
        return implode('', array_map('ucfirst', $parts));
    }

    /**
     * Convert string to snake_case
     *
     * @param string $string
     * @return string
     */
    protected function toSnakeCase(string $string): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

    /**
     * Write file to disk
     *
     * @param string $filePath
     * @param string $content
     * @return bool
     */
    protected function writeFile(string $filePath, string $content): bool
    {
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($filePath, $content) !== false;
    }
}
