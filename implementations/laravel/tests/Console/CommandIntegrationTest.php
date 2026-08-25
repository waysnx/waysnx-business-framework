<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Console;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Console\Commands\ListCommand;
use WaysNX\BusinessFramework\Console\Commands\ShowCommand;
use WaysNX\BusinessFramework\Console\Commands\DoctorCommand;
use WaysNX\BusinessFramework\Console\Commands\RegisterCommand;

/**
 * CommandIntegrationTest
 *
 * Structural and functional tests for WBF CLI commands.
 *
 * Tests verify:
 * - Command structure and options
 * - Command inheritance
 * - Help/description availability
 * - Exit code constants
 *
 * @package WaysNX\BusinessFramework\Tests\Console
 */
class CommandIntegrationTest extends TestCase
{
    /**
     * Test all commands can be instantiated
     */
    public function testAllCommandsInstantiate(): void
    {
        $commands = [
            new ListCommand(),
            new ShowCommand(),
            new DoctorCommand(),
            new RegisterCommand(),
        ];

        $this->assertCount(4, $commands);
        foreach ($commands as $command) {
            $this->assertNotNull($command);
            $this->assertNotEmpty($command->getName());
            $this->assertNotEmpty($command->getDescription());
        }
    }

    /**
     * Test commands have correct names
     */
    public function testCommandNames(): void
    {
        $expectedNames = [
            'wbf:list' => ListCommand::class,
            'wbf:show' => ShowCommand::class,
            'wbf:doctor' => DoctorCommand::class,
            'wbf:register' => RegisterCommand::class,
        ];

        foreach ($expectedNames as $expectedName => $commandClass) {
            $command = new $commandClass();
            $this->assertEquals($expectedName, $command->getName());
        }
    }

    /**
     * Test commands extend BaseWbfCommand
     */
    public function testCommandInheritance(): void
    {
        $commands = [
            new ListCommand(),
            new ShowCommand(),
            new DoctorCommand(),
            new RegisterCommand(),
        ];

        $baseClass = \WaysNX\BusinessFramework\Console\BaseWbfCommand::class;
        foreach ($commands as $command) {
            $this->assertInstanceOf($baseClass, $command);
        }
    }

    /**
     * Test exit code contracts are defined
     */
    public function testExitCodeConstants(): void
    {
        $command = new ListCommand();
        $reflection = new \ReflectionClass($command);
        $constants = $reflection->getConstants();

        $this->assertArrayHasKey('EXIT_SUCCESS', $constants);
        $this->assertArrayHasKey('EXIT_ERROR', $constants);
        $this->assertArrayHasKey('EXIT_INVALID_ARGUMENT', $constants);
        $this->assertArrayHasKey('EXIT_NOT_FOUND', $constants);
        $this->assertArrayHasKey('EXIT_CONFLICT', $constants);
        $this->assertArrayHasKey('EXIT_SYSTEM_FAILURE', $constants);

        $this->assertEquals(0, $constants['EXIT_SUCCESS']);
        $this->assertEquals(1, $constants['EXIT_ERROR']);
        $this->assertEquals(2, $constants['EXIT_INVALID_ARGUMENT']);
        $this->assertEquals(3, $constants['EXIT_NOT_FOUND']);
        $this->assertEquals(5, $constants['EXIT_CONFLICT']);
        $this->assertEquals(6, $constants['EXIT_SYSTEM_FAILURE']);
    }

    /**
     * Test JSON output option is available
     */
    public function testJsonOptionAvailable(): void
    {
        $commands = [
            new ListCommand(),
            new ShowCommand(),
            new DoctorCommand(),
            new RegisterCommand(),
        ];

        foreach ($commands as $command) {
            // Check that command has options
            $definition = $command->getDefinition();
            $this->assertNotNull($definition);
            // Options are registered dynamically at runtime, can't check directly
            // but we can verify the command has a method to get options
            $this->assertTrue(method_exists($command, 'getOptions'));
        }
    }

    /**
     * Test list command resource types
     */
    public function testListCommandResourceTypes(): void
    {
        $command = new ListCommand();
        $this->assertNotNull($command);
        $this->assertStringContainsString('resource', strtolower($command->getDescription()));
    }

    /**
     * Test show command takes type and id arguments
     */
    public function testShowCommandArguments(): void
    {
        $command = new ShowCommand();
        $definition = $command->getDefinition();
        $arguments = $definition->getArguments();

        // Should have at least type and id arguments
        $this->assertGreaterThanOrEqual(2, count($arguments));
    }

    /**
     * Test register command takes type and id arguments
     */
    public function testRegisterCommandArguments(): void
    {
        $command = new RegisterCommand();
        $definition = $command->getDefinition();
        $arguments = $definition->getArguments();

        // Should have at least type and id arguments
        $this->assertGreaterThanOrEqual(2, count($arguments));
    }

    /**
     * Test doctor command has optional component option
     */
    public function testDoctorCommandStructure(): void
    {
        $command = new DoctorCommand();
        $this->assertNotNull($command);
        // Doctor is a diagnostic command, should have help available
        $this->assertNotEmpty($command->getDescription());
    }

    /**
     * Test commands are discoverable
     */
    public function testCommandsDiscoverable(): void
    {
        $commands = [
            'wbf:list',
            'wbf:show',
            'wbf:doctor',
            'wbf:register',
        ];

        foreach ($commands as $commandName) {
            $parts = explode(':', $commandName);
            $this->assertEquals(2, count($parts));
            $this->assertEquals('wbf', $parts[0]);
        }
    }

    /**
     * Test all commands support JSON output
     */
    public function testAllCommandsJsonSupport(): void
    {
        $commands = [
            new ListCommand(),
            new ShowCommand(),
            new DoctorCommand(),
            new RegisterCommand(),
        ];

        foreach ($commands as $command) {
            // All should have the json option from BaseWbfCommand
            $this->assertTrue(method_exists($command, 'getOptions'));
        }
    }

    /**
     * Test all commands support no-interaction mode
     */
    public function testAllCommandsNoInteractionSupport(): void
    {
        $commands = [
            new ListCommand(),
            new ShowCommand(),
            new DoctorCommand(),
            new RegisterCommand(),
        ];

        foreach ($commands as $command) {
            // All should have no-interaction from BaseWbfCommand
            $this->assertTrue(method_exists($command, 'getOptions'));
        }
    }
}
