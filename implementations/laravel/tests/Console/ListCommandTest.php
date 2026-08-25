<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Console;

use WaysNX\BusinessFramework\Console\Commands\ListCommand;
use WaysNX\BusinessFramework\Registry\WorkflowDefinition;
use WaysNX\BusinessFramework\Registry\WorkflowRegistry;

/**
 * ListCommandTest
 *
 * Test the wbf:list command.
 *
 * @package WaysNX\BusinessFramework\Tests\Console
 */
class ListCommandTest extends \PHPUnit\Framework\TestCase
{
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
     * Test ListCommand has detailed option
     *
     * @return void
     */
    public function testListCommandIsValid(): void
    {
        $command = new ListCommand();
        $this->assertNotNull($command->getDefinition());
    }

    /**
     * Test ListCommand is a BaseWbfCommand
     *
     * @return void
     */
    public function testListCommandExtendsBaseWbfCommand(): void
    {
        $command = new ListCommand();
        $this->assertInstanceOf(\WaysNX\BusinessFramework\Console\BaseWbfCommand::class, $command);
    }
}
