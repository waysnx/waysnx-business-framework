<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Console;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Console\Commands\DoctorCommand;

/**
 * DoctorCommandRealTest
 *
 * Structural tests for wbf:doctor command.
 *
 * @package WaysNX\BusinessFramework\Tests\Console
 */
class DoctorCommandRealTest extends TestCase
{
    /**
     * Test doctor command can be instantiated
     */
    public function testDoctorCanBeInstantiated(): void
    {
        $command = new DoctorCommand();
        $this->assertInstanceOf(DoctorCommand::class, $command);
    }

    /**
     * Test doctor has proper name
     */
    public function testDoctorCommandName(): void
    {
        $command = new DoctorCommand();
        $this->assertEquals('wbf:doctor', $command->getName());
    }

    /**
     * Test doctor has description
     */
    public function testDoctorHasDescription(): void
    {
        $command = new DoctorCommand();
        $this->assertNotEmpty($command->getDescription());
        $this->assertStringContainsString('health', strtolower($command->getDescription()));
    }

    /**
     * Test doctor has proper definition
     */
    public function testDoctorDefinition(): void
    {
        $command = new DoctorCommand();
        $definition = $command->getDefinition();
        $this->assertNotNull($definition);
    }

    /**
     * Test doctor exit code constants
     */
    public function testDoctorExitCodeConstants(): void
    {
        $command = new DoctorCommand();
        $reflection = new \ReflectionClass($command);
        $constants = $reflection->getConstants();

        $this->assertArrayHasKey('EXIT_SUCCESS', $constants);
        $this->assertArrayHasKey('EXIT_SYSTEM_FAILURE', $constants);
        $this->assertEquals(0, $constants['EXIT_SUCCESS']);
        $this->assertEquals(6, $constants['EXIT_SYSTEM_FAILURE']);
    }

    /**
     * Test doctor is BaseWbfCommand
     */
    public function testDoctorIsBaseWbfCommand(): void
    {
        $command = new DoctorCommand();
        $this->assertInstanceOf(\WaysNX\BusinessFramework\Console\BaseWbfCommand::class, $command);
    }
}
