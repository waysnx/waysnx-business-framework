<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Console;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Console\Commands\MakeCommand;
use WaysNX\BusinessFramework\Console\Generators\GenerationResult;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * MakeCommandTest
 *
 * Real behavioral tests for wbf:make command.
 *
 * @package WaysNX\BusinessFramework\Tests\Console
 */
class MakeCommandTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/wbf-test-' . uniqid();
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up temp directory
        if (is_dir($this->tempDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $fileinfo) {
                if ($fileinfo->isDir()) {
                    rmdir($fileinfo->getRealPath());
                } else {
                    unlink($fileinfo->getRealPath());
                }
            }
            rmdir($this->tempDir);
        }
        parent::tearDown();
    }

    /**
     * Test wbf:make workflow generates valid PHP
     */
    public function testMakeWorkflowGeneratesValidPhp(): void
    {
        $outputPath = $this->tempDir . '/Workflows';
        mkdir($outputPath, 0755, true);

        $generator = new \WaysNX\BusinessFramework\Console\Generators\WorkflowGenerator(
            'test-workflow',
            'test-module',
            'App\\Business\\Workflows',
            $outputPath,
            false
        );

        $result = $generator->generate();

        $this->assertTrue($result->isSuccess());
        $files = $result->getFiles();
        $this->assertNotEmpty($files);

        // Verify generated files exist
        foreach ($files as $filePath) {
            $this->assertTrue(file_exists($filePath), "File should exist: {$filePath}");
        }

        // Verify PHP syntax is valid
        foreach ($files as $filePath) {
            $output = shell_exec("php -l " . escapeshellarg($filePath) . " 2>&1");
            $this->assertStringContainsString('No syntax errors', $output, "File should have valid PHP syntax: {$filePath}");
        }
    }

    /**
     * Test wbf:make business-function generates valid PHP with all mandatory fields
     */
    public function testMakeBusinessFunctionGeneratesValidPhp(): void
    {
        $outputPath = $this->tempDir . '/Functions';
        mkdir($outputPath, 0755, true);

        $generator = new \WaysNX\BusinessFramework\Console\Generators\BusinessFunctionGenerator(
            'calculate-salary',
            'payroll',
            'App\\Business\\Functions',
            $outputPath,
            false
        );
        $generator->setDomain('compensation');
        $generator->setCapability('payroll-processing');
        $generator->setDescription('Calculate employee salary based on hours and rate');

        $result = $generator->generate();

        $this->assertTrue($result->isSuccess());
        $files = $result->getFiles();
        $this->assertNotEmpty($files);

        // Verify generated file exists
        $filePath = $files[0];
        $this->assertTrue(file_exists($filePath));

        // Verify PHP syntax
        $output = shell_exec("php -l " . escapeshellarg($filePath) . " 2>&1");
        $this->assertStringContainsString('No syntax errors', $output);

        // Verify file contains mandatory fields
        $content = file_get_contents($filePath);
        $this->assertStringContainsString('$this->functionId', $content);
        $this->assertStringContainsString('$this->functionName', $content);
        $this->assertStringContainsString('$this->domain', $content);
        $this->assertStringContainsString('$this->capability', $content);
        $this->assertStringContainsString('$this->description', $content);
        $this->assertStringContainsString('$this->businessOwner', $content);
        $this->assertStringContainsString('$this->technicalOwner', $content);
        $this->assertStringContainsString('initializeBusinessFunction', $content);
        $this->assertStringContainsString('executeBusiness', $content);
    }

    /**
     * Test wbf:make validation generates valid PHP
     */
    public function testMakeValidationGeneratesValidPhp(): void
    {
        $outputPath = $this->tempDir . '/Validations';
        mkdir($outputPath, 0755, true);

        $generator = new \WaysNX\BusinessFramework\Console\Generators\ValidationGenerator(
            'email-format',
            'validation-module',
            'App\\Business\\Validations',
            $outputPath,
            false
        );

        $result = $generator->generate();

        $this->assertTrue($result->isSuccess());
        $files = $result->getFiles();
        $this->assertNotEmpty($files);

        $filePath = $files[0];
        $this->assertTrue(file_exists($filePath));

        // Verify PHP syntax
        $output = shell_exec("php -l " . escapeshellarg($filePath) . " 2>&1");
        $this->assertStringContainsString('No syntax errors', $output);
    }

    /**
     * Test wbf:make module generates valid PHP
     */
    public function testMakeModuleGeneratesValidPhp(): void
    {
        $outputPath = $this->tempDir . '/Modules';
        mkdir($outputPath, 0755, true);

        $generator = new \WaysNX\BusinessFramework\Console\Generators\ModuleGenerator(
            'payroll',
            'payroll',
            'App\\Modules',
            $outputPath,
            false
        );

        $result = $generator->generate();

        $this->assertTrue($result->isSuccess());
        $files = $result->getFiles();
        $this->assertNotEmpty($files);

        $filePath = $files[0];
        $this->assertTrue(file_exists($filePath));

        // Verify PHP syntax
        $output = shell_exec("php -l " . escapeshellarg($filePath) . " 2>&1");
        $this->assertStringContainsString('No syntax errors', $output);
    }

    /**
     * Test wbf:make entity generates valid PHP
     */
    public function testMakeEntityGeneratesValidPhp(): void
    {
        $outputPath = $this->tempDir . '/Models';
        mkdir($outputPath, 0755, true);

        $generator = new \WaysNX\BusinessFramework\Console\Generators\EntityGenerator(
            'Employee',
            'hr',
            'App\\Models',
            $outputPath,
            false
        );

        $result = $generator->generate();

        $this->assertTrue($result->isSuccess());
        $files = $result->getFiles();
        $this->assertNotEmpty($files);

        $filePath = $files[0];
        $this->assertTrue(file_exists($filePath));

        // Verify PHP syntax
        $output = shell_exec("php -l " . escapeshellarg($filePath) . " 2>&1");
        $this->assertStringContainsString('No syntax errors', $output);
    }

    /**
     * Test GenerationResult success state
     */
    public function testGenerationResultSuccess(): void
    {
        $result = GenerationResult::success(['/path/to/file1', '/path/to/file2']);

        $this->assertTrue($result->isSuccess());
        $this->assertCount(2, $result->getFiles());
        $this->assertEmpty($result->getErrorMessage());
    }

    /**
     * Test GenerationResult failure state
     */
    public function testGenerationResultFailure(): void
    {
        $result = GenerationResult::failure('Test error', 'test_error');

        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Test error', $result->getErrorMessage());
        $this->assertEquals('test_error', $result->getErrorType());
    }

    /**
     * Test workflow generator creates both Definition and Model
     */
    public function testWorkflowGeneratorCreatesBothArtifacts(): void
    {
        $outputPath = $this->tempDir . '/Workflows';
        mkdir($outputPath, 0755, true);

        $generator = new \WaysNX\BusinessFramework\Console\Generators\WorkflowGenerator(
            'apply-leave',
            'hr',
            'App\\Business\\Workflows',
            $outputPath,
            false
        );

        $result = $generator->generate();

        $this->assertTrue($result->isSuccess());
        $files = $result->getFiles();
        
        // Should generate both Definition and Model
        $this->assertCount(2, $files, 'Should generate 2 files: Definition and Model');

        // Verify both are PHP files
        foreach ($files as $file) {
            $this->assertStringEndsWith('.php', $file);
            $this->assertTrue(file_exists($file));
        }
    }

    /**
     * Test generated Workflow model has no recursive method calls
     */
    public function testWorkflowModelNoRecursion(): void
    {
        $outputPath = $this->tempDir . '/Workflows';
        mkdir($outputPath, 0755, true);

        $generator = new \WaysNX\BusinessFramework\Console\Generators\WorkflowGenerator(
            'test-workflow',
            'test',
            'App\\Business\\Workflows',
            $outputPath,
            false
        );

        $result = $generator->generate();
        $this->assertTrue($result->isSuccess());

        // Find the Workflow model file (second file, should be WorkflowTestWorkflow.php)
        $files = $result->getFiles();
        $modelFile = null;
        foreach ($files as $file) {
            // Model file contains 'Workflow' + class name
            if (strpos($file, 'WorkflowTest') !== false) {
                $modelFile = $file;
                break;
            }
        }

        $this->assertNotNull($modelFile, 'Should generate Workflow model file');
        $this->assertTrue(file_exists($modelFile));

        // Verify PHP syntax
        $output = shell_exec("php -l " . escapeshellarg($modelFile) . " 2>&1");
        $this->assertStringContainsString('No syntax errors', $output);

        // Read the generated content and verify no recursive calls
        $content = file_get_contents($modelFile);
        
        // Check that addKpi() and setSla() don't exist in the generated class
        // (they were removed to avoid recursion)
        $this->assertStringNotContainsString('public function addKpi', $content);
        $this->assertStringNotContainsString('public function setSla', $content);
        
        // Verify comments show how to use parent methods
        $this->assertStringContainsString('addKpi', $content); // In comments
        $this->assertStringContainsString('setSla', $content); // In comments
    }

    /**
     * Test namespace is properly used in generated code
     */
    public function testGeneratedCodeUsesCorrectNamespace(): void
    {
        $outputPath = $this->tempDir . '/Functions';
        mkdir($outputPath, 0755, true);
        $namespace = 'App\\Business\\Functions';

        $generator = new \WaysNX\BusinessFramework\Console\Generators\BusinessFunctionGenerator(
            'test-function',
            'test',
            $namespace,
            $outputPath,
            false
        );

        $result = $generator->generate();

        $this->assertTrue($result->isSuccess());
        $filePath = $result->getFiles()[0];
        
        $content = file_get_contents($filePath);
        $this->assertStringContainsString("namespace {$namespace}", $content);
    }
}
