<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Console;

use WaysNX\BusinessFramework\Console\Commands\MakeCommand;
use WaysNX\BusinessFramework\Console\Commands\ListCommand;
use WaysNX\BusinessFramework\Console\Commands\ShowCommand;
use WaysNX\BusinessFramework\Console\Commands\RegisterCommand;
use WaysNX\BusinessFramework\Console\Commands\DoctorCommand;
use PHPUnit\Framework\TestCase;

/**
 * CommandFoundationTest
 *
 * Test CLI command foundation.
 *
 * Verifies:
 * - Commands exist and can be instantiated
 * - BaseWbfCommand provides required infrastructure
 * - Commands have proper descriptions
 *
 * @package WaysNX\BusinessFramework\Tests\Console
 */
class CommandFoundationTest extends TestCase
{
    /**
     * Test MakeCommand can be instantiated
     *
     * @return void
     */
    public function testMakeCommandCanBeInstantiated(): void
    {
        $command = new MakeCommand();
        $this->assertInstanceOf(MakeCommand::class, $command);
    }

    /**
     * Test ListCommand can be instantiated
     *
     * @return void
     */
    public function testListCommandCanBeInstantiated(): void
    {
        $command = new ListCommand();
        $this->assertInstanceOf(ListCommand::class, $command);
    }

    /**
     * Test ShowCommand can be instantiated
     *
     * @return void
     */
    public function testShowCommandCanBeInstantiated(): void
    {
        $command = new ShowCommand();
        $this->assertInstanceOf(ShowCommand::class, $command);
    }

    /**
     * Test RegisterCommand can be instantiated
     *
     * @return void
     */
    public function testRegisterCommandCanBeInstantiated(): void
    {
        $command = new RegisterCommand();
        $this->assertInstanceOf(RegisterCommand::class, $command);
    }

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
     * Test MakeCommand has signature
     *
     * @return void
     */
    public function testMakeCommandHasSignature(): void
    {
        $reflection = new \ReflectionClass(MakeCommand::class);
        $this->assertTrue($reflection->hasProperty('signature'));
    }

    /**
     * Test ListCommand has signature
     *
     * @return void
     */
    public function testListCommandHasSignature(): void
    {
        $reflection = new \ReflectionClass(ListCommand::class);
        $this->assertTrue($reflection->hasProperty('signature'));
    }

    /**
     * Test ShowCommand has signature
     *
     * @return void
     */
    public function testShowCommandHasSignature(): void
    {
        $reflection = new \ReflectionClass(ShowCommand::class);
        $this->assertTrue($reflection->hasProperty('signature'));
    }

    /**
     * Test RegisterCommand has signature
     *
     * @return void
     */
    public function testRegisterCommandHasSignature(): void
    {
        $reflection = new \ReflectionClass(RegisterCommand::class);
        $this->assertTrue($reflection->hasProperty('signature'));
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
     * Test MakeCommand has description
     *
     * @return void
     */
    public function testMakeCommandHasDescription(): void
    {
        $command = new MakeCommand();
        $this->assertNotEmpty($command->getDescription());
    }

    /**
     * Test ListCommand has description
     *
     * @return void
     */
    public function testListCommandHasDescription(): void
    {
        $command = new ListCommand();
        $this->assertNotEmpty($command->getDescription());
    }

    /**
     * Test ShowCommand has description
     *
     * @return void
     */
    public function testShowCommandHasDescription(): void
    {
        $command = new ShowCommand();
        $this->assertNotEmpty($command->getDescription());
    }

    /**
     * Test RegisterCommand has description
     *
     * @return void
     */
    public function testRegisterCommandHasDescription(): void
    {
        $command = new RegisterCommand();
        $this->assertNotEmpty($command->getDescription());
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
     * Test all commands are BaseWbfCommand subclasses
     *
     * @return void
     */
    public function testAllCommandsExtendBaseWbfCommand(): void
    {
        $this->assertInstanceOf(\WaysNX\BusinessFramework\Console\BaseWbfCommand::class, new MakeCommand());
        $this->assertInstanceOf(\WaysNX\BusinessFramework\Console\BaseWbfCommand::class, new ListCommand());
        $this->assertInstanceOf(\WaysNX\BusinessFramework\Console\BaseWbfCommand::class, new ShowCommand());
        $this->assertInstanceOf(\WaysNX\BusinessFramework\Console\BaseWbfCommand::class, new RegisterCommand());
        $this->assertInstanceOf(\WaysNX\BusinessFramework\Console\BaseWbfCommand::class, new DoctorCommand());
    }

    /**
     * Test BaseWbfCommand has exit code constants
     *
     * @return void
     */
    public function testBaseWbfCommandHasExitCodeConstants(): void
    {
        $reflection = new \ReflectionClass(\WaysNX\BusinessFramework\Console\BaseWbfCommand::class);
        $constants = $reflection->getConstants();

        $this->assertArrayHasKey('EXIT_SUCCESS', $constants);
        $this->assertArrayHasKey('EXIT_ERROR', $constants);
        $this->assertArrayHasKey('EXIT_INVALID_ARGUMENT', $constants);
        $this->assertArrayHasKey('EXIT_NOT_FOUND', $constants);
        $this->assertArrayHasKey('EXIT_PERMISSION_DENIED', $constants);
        $this->assertArrayHasKey('EXIT_CONFLICT', $constants);
        $this->assertArrayHasKey('EXIT_SYSTEM_FAILURE', $constants);
    }

    /**
     * Test exit code values are correct
     *
     * @return void
     */
    public function testExitCodeValuesAreCorrect(): void
    {
        $reflection = new \ReflectionClass(\WaysNX\BusinessFramework\Console\BaseWbfCommand::class);
        $constants = $reflection->getConstants();

        $this->assertEquals(0, $constants['EXIT_SUCCESS']);
        $this->assertEquals(1, $constants['EXIT_ERROR']);
        $this->assertEquals(2, $constants['EXIT_INVALID_ARGUMENT']);
        $this->assertEquals(3, $constants['EXIT_NOT_FOUND']);
        $this->assertEquals(4, $constants['EXIT_PERMISSION_DENIED']);
        $this->assertEquals(5, $constants['EXIT_CONFLICT']);
        $this->assertEquals(6, $constants['EXIT_SYSTEM_FAILURE']);
    }
}
