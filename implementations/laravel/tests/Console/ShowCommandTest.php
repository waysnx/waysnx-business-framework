<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Console;

use WaysNX\BusinessFramework\Console\Commands\ShowCommand;

/**
 * ShowCommandTest
 *
 * Test the wbf:show command.
 *
 * @package WaysNX\BusinessFramework\Tests\Console
 */
class ShowCommandTest extends \PHPUnit\Framework\TestCase
{
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
     * Test ShowCommand has raw option
     *
     * @return void
     */
    public function testShowCommandIsValid(): void
    {
        $command = new ShowCommand();
        $this->assertNotNull($command->getDefinition());
    }

    /**
     * Test ShowCommand is a BaseWbfCommand
     *
     * @return void
     */
    public function testShowCommandExtendsBaseWbfCommand(): void
    {
        $command = new ShowCommand();
        $this->assertInstanceOf(\WaysNX\BusinessFramework\Console\BaseWbfCommand::class, $command);
    }
}
