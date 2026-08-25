<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Console;

use WaysNX\BusinessFramework\Console\Commands\DoctorCommand;

/**
 * DoctorCommandTest
 *
 * Test the wbf:doctor command.
 *
 * @package WaysNX\BusinessFramework\Tests\Console
 */
class DoctorCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test DoctorCommand can be instantiated
     *
     * @return void
     */
    public function testDoctorCommandCanBeInstantiated(): void
    {
        $command = new DoctorCommand();
        $this->assertInstanceOf(DoctorCommand::class, $command);
    }

    /**
     * Test DoctorCommand has signature
     *
     * @return void
     */
    public function testDoctorCommandHasSignature(): void
    {
        $reflection = new \ReflectionClass(DoctorCommand::class);
        $this->assertTrue($reflection->hasProperty('signature'));
    }

    /**
     * Test DoctorCommand has description
     *
     * @return void
     */
    public function testDoctorCommandHasDescription(): void
    {
        $command = new DoctorCommand();
        $this->assertNotEmpty($command->getDescription());
    }

    /**
     * Test DoctorCommand has options
     *
     * @return void
     */
    public function testDoctorCommandHasOptions(): void
    {
        $command = new DoctorCommand();
        $this->assertNotNull($command->getDefinition());
    }

    /**
     * Test DoctorCommand is a BaseWbfCommand
     *
     * @return void
     */
    public function testDoctorCommandExtendsBaseWbfCommand(): void
    {
        $command = new DoctorCommand();
        $this->assertInstanceOf(\WaysNX\BusinessFramework\Console\BaseWbfCommand::class, $command);
    }

    /**
     * Test exit code constants
     *
     * @return void
     */
    public function testExitCodeConstants(): void
    {
        $reflection = new \ReflectionClass(DoctorCommand::class);
        $constants = $reflection->getConstants();

        $this->assertEquals(0, $constants['EXIT_SUCCESS']);
        $this->assertEquals(6, $constants['EXIT_SYSTEM_FAILURE']);
    }
}
