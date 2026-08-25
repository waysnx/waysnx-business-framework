<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Console;

use WaysNX\BusinessFramework\Console\Commands\RegisterCommand;

/**
 * RegisterCommandTest
 *
 * Test the wbf:register command.
 *
 * @package WaysNX\BusinessFramework\Tests\Console
 */
class RegisterCommandTest extends \PHPUnit\Framework\TestCase
{
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
     * Test RegisterCommand has options
     *
     * @return void
     */
    public function testRegisterCommandHasOptions(): void
    {
        $command = new RegisterCommand();
        $this->assertNotNull($command->getDefinition());
    }

    /**
     * Test RegisterCommand is a BaseWbfCommand
     *
     * @return void
     */
    public function testRegisterCommandExtendsBaseWbfCommand(): void
    {
        $command = new RegisterCommand();
        $this->assertInstanceOf(\WaysNX\BusinessFramework\Console\BaseWbfCommand::class, $command);
    }

    /**
     * Test exit codes
     *
     * @return void
     */
    public function testExitCodes(): void
    {
        $reflection = new \ReflectionClass(RegisterCommand::class);
        $constants = $reflection->getConstants();

        $this->assertEquals(0, $constants['EXIT_SUCCESS']);
        $this->assertEquals(3, $constants['EXIT_NOT_FOUND']);
    }
}
