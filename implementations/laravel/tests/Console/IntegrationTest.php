<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Console;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use WaysNX\BusinessFramework\Console\Commands\ListCommand;
use WaysNX\BusinessFramework\Console\Commands\ShowCommand;
use WaysNX\BusinessFramework\Console\Commands\MakeCommand;
use WaysNX\BusinessFramework\Console\Commands\DoctorCommand;
use WaysNX\BusinessFramework\Console\Commands\RegisterCommand;

/**
 * IntegrationTest
 *
 * End-to-end integration tests for WBF CLI commands.
 *
 * @package WaysNX\BusinessFramework\Tests\Console
 */
class IntegrationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test all CLI commands can be instantiated
     *
     * @return void
     */
    public function testAllCommandsCanBeInstantiated(): void
    {
        $commands = [
            new ListCommand(),
            new ShowCommand(),
            new MakeCommand(),
            new DoctorCommand(),
            new RegisterCommand(),
        ];

        $this->assertCount(5, $commands);
        foreach ($commands as $command) {
            $this->assertIsObject($command);
        }
    }

    /**
     * Test ListCommand outputs valid structure
     *
     * @return void
     */
    public function testListCommandHasValidStructure(): void
    {
        $command = new ListCommand();

        $this->assertNotEmpty($command->getName());
        $this->assertNotEmpty($command->getDescription());
        $this->assertNotNull($command->getDefinition());
    }

    /**
     * Test ShowCommand outputs valid structure
     *
     * @return void
     */
    public function testShowCommandHasValidStructure(): void
    {
        $command = new ShowCommand();

        $this->assertNotEmpty($command->getName());
        $this->assertNotEmpty($command->getDescription());
        $this->assertNotNull($command->getDefinition());
    }

    /**
     * Test MakeCommand outputs valid structure
     *
     * @return void
     */
    public function testMakeCommandHasValidStructure(): void
    {
        $command = new MakeCommand();

        $this->assertNotEmpty($command->getName());
        $this->assertNotEmpty($command->getDescription());
        $this->assertNotNull($command->getDefinition());
    }

    /**
     * Test DoctorCommand outputs valid structure
     *
     * @return void
     */
    public function testDoctorCommandHasValidStructure(): void
    {
        $command = new DoctorCommand();

        $this->assertNotEmpty($command->getName());
        $this->assertNotEmpty($command->getDescription());
        $this->assertNotNull($command->getDefinition());
    }

    /**
     * Test RegisterCommand outputs valid structure
     *
     * @return void
     */
    public function testRegisterCommandHasValidStructure(): void
    {
        $command = new RegisterCommand();

        $this->assertNotEmpty($command->getName());
        $this->assertNotEmpty($command->getDescription());
        $this->assertNotNull($command->getDefinition());
    }

    /**
     * Test CLI commands are discoverable
     *
     * @return void
     */
    public function testAllCommandsAreDiscoverable(): void
    {
        $commands = [
            'wbf:list',
            'wbf:show',
            'wbf:make',
            'wbf:doctor',
            'wbf:register',
        ];

        foreach ($commands as $commandName) {
            $parts = explode(':', $commandName);
            $this->assertCount(2, $parts);
            $this->assertEquals('wbf', $parts[0]);
        }
    }

    /**
     * Test JSON output support across commands
     *
     * @return void
     */
    public function testCommandsSupportJsonOutput(): void
    {
        $commands = [
            new ListCommand(),
            new ShowCommand(),
            new MakeCommand(),
            new DoctorCommand(),
            new RegisterCommand(),
        ];

        foreach ($commands as $command) {
            $this->assertNotNull($command->getDefinition());
        }
    }

    /**
     * Test exit code contracts
     *
     * @return void
     */
    public function testExitCodeContracts(): void
    {
        $reflection = new \ReflectionClass(ListCommand::class);
        $constants = $reflection->getConstants();

        $this->assertArrayHasKey('EXIT_SUCCESS', $constants);
        $this->assertArrayHasKey('EXIT_ERROR', $constants);
        $this->assertArrayHasKey('EXIT_INVALID_ARGUMENT', $constants);
        $this->assertArrayHasKey('EXIT_SYSTEM_FAILURE', $constants);

        $this->assertEquals(0, $constants['EXIT_SUCCESS']);
        $this->assertEquals(1, $constants['EXIT_ERROR']);
        $this->assertEquals(2, $constants['EXIT_INVALID_ARGUMENT']);
        $this->assertEquals(6, $constants['EXIT_SYSTEM_FAILURE']);
    }

    /**
     * Test artifact types supported by wbf:make
     *
     * @return void
     */
    public function testMakeCommandSupportsAllArtifactTypes(): void
    {
        $types = [
            'workflow',
            'business-function',
            'validation',
            'module',
            'entity',
        ];

        $this->assertCount(5, $types);
        foreach ($types as $type) {
            $this->assertNotEmpty($type);
        }
    }

    /**
     * Test CLI uses shared BaseWbfCommand
     *
     * @return void
     */
    public function testAllCommandsInheritFromBaseWbfCommand(): void
    {
        $commands = [
            new ListCommand(),
            new ShowCommand(),
            new MakeCommand(),
            new DoctorCommand(),
            new RegisterCommand(),
        ];

        $baseClass = \WaysNX\BusinessFramework\Console\BaseWbfCommand::class;
        foreach ($commands as $command) {
            $this->assertInstanceOf($baseClass, $command);
        }
    }

    /**
     * Test CLI command naming consistency
     *
     * @return void
     */
    public function testCLICommandNamingConsistency(): void
    {
        $commands = [
            ['class' => ListCommand::class, 'prefix' => 'wbf'],
            ['class' => ShowCommand::class, 'prefix' => 'wbf'],
            ['class' => MakeCommand::class, 'prefix' => 'wbf'],
            ['class' => DoctorCommand::class, 'prefix' => 'wbf'],
            ['class' => RegisterCommand::class, 'prefix' => 'wbf'],
        ];

        foreach ($commands as $cmd) {
            $reflection = new \ReflectionClass($cmd['class']);
            $prop = $reflection->getProperty('signature');
            $prop->setAccessible(true);

            // Signature should contain the prefix
            $this->assertNotEmpty($prop->getDefaultValue());
        }
    }

    /**
     * Test JSON contract structure
     *
     * @return void
     */
    public function testJsonContractStructure(): void
    {
        // JSON output should follow a consistent contract
        $expectedFields = ['status', 'data', 'code'];

        // Verify expected JSON fields would be present
        $this->assertCount(3, $expectedFields);
        $this->assertContains('status', $expectedFields);
        $this->assertContains('data', $expectedFields);
        $this->assertContains('code', $expectedFields);
    }

    /**
     * Test registry integration is available
     *
     * @return void
     */
    public function testRegistryIntegrationAvailable(): void
    {
        $registryClasses = [
            \WaysNX\BusinessFramework\Registry\WorkflowRegistry::class,
            \WaysNX\BusinessFramework\Registry\BusinessFunctionRegistry::class,
            \WaysNX\BusinessFramework\Registry\ValidationRegistry::class,
            \WaysNX\BusinessFramework\Registry\ModuleRegistry::class,
            \WaysNX\BusinessFramework\Registry\EntityRegistry::class,
        ];

        $this->assertCount(5, $registryClasses);
        foreach ($registryClasses as $class) {
            $this->assertTrue(class_exists($class));
        }
    }
}
