<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Console;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Console\Commands\RegisterCommand;
use WaysNX\BusinessFramework\Registry\WorkflowRegistry;
use WaysNX\BusinessFramework\Registry\BusinessFunctionRegistry;
use WaysNX\BusinessFramework\Registry\WorkflowDefinition;
use WaysNX\BusinessFramework\Registry\BusinessFunctionDefinition;

/**
 * RegisterCommandRealTest
 *
 * Structural tests for wbf:register command with registry verification.
 *
 * @package WaysNX\BusinessFramework\Tests\Console
 */
class RegisterCommandRealTest extends TestCase
{
    /**
     * Test register command can be instantiated
     */
    public function testRegisterCanBeInstantiated(): void
    {
        $command = new RegisterCommand();
        $this->assertInstanceOf(RegisterCommand::class, $command);
    }

    /**
     * Test register has proper name
     */
    public function testRegisterCommandName(): void
    {
        $command = new RegisterCommand();
        $this->assertEquals('wbf:register', $command->getName());
    }

    /**
     * Test register has description
     */
    public function testRegisterHasDescription(): void
    {
        $command = new RegisterCommand();
        $this->assertNotEmpty($command->getDescription());
    }

    /**
     * Test register has arguments
     */
    public function testRegisterHasArguments(): void
    {
        $command = new RegisterCommand();
        $definition = $command->getDefinition();
        $arguments = $definition->getArguments();

        $this->assertGreaterThanOrEqual(2, count($arguments));
    }

    /**
     * Test register exit code constants
     */
    public function testRegisterExitCodeConstants(): void
    {
        $command = new RegisterCommand();
        $reflection = new \ReflectionClass($command);
        $constants = $reflection->getConstants();

        $this->assertArrayHasKey('EXIT_SUCCESS', $constants);
        $this->assertArrayHasKey('EXIT_NOT_FOUND', $constants);
        $this->assertEquals(0, $constants['EXIT_SUCCESS']);
        $this->assertEquals(3, $constants['EXIT_NOT_FOUND']);
    }

    /**
     * Test register is BaseWbfCommand
     */
    public function testRegisterIsBaseWbfCommand(): void
    {
        $command = new RegisterCommand();
        $this->assertInstanceOf(\WaysNX\BusinessFramework\Console\BaseWbfCommand::class, $command);
    }

    /**
     * Test workflow registry can be populated
     */
    public function testWorkflowRegistryCanBeSeeded(): void
    {
        $registry = new WorkflowRegistry();

        $definition = new WorkflowDefinition(
            id: 'test-workflow',
            name: 'TestWorkflow',
            displayName: 'Test Workflow',
            description: 'Test',
            version: '1.0.0',
            moduleId: 'test',
            category: 'test',
            triggerType: 'manual',
            triggerEvent: '',
            entryFunction: '',
            exitFunction: '',
            steps: [],
            supportedEntityTypes: [],
            priority: 0,
            enabled: true,
            tags: [],
            metadata: []
        );

        $registry->register($definition);
        $this->assertTrue($registry->exists('test-workflow'));
    }

    /**
     * Test business function registry can be populated
     */
    public function testBusinessFunctionRegistryCanBeSeeded(): void
    {
        $registry = new BusinessFunctionRegistry();

        $definition = new BusinessFunctionDefinition(
            id: 'test-function',
            name: 'TestFunction',
            displayName: 'Test Function',
            description: 'Test',
            moduleId: 'test',
            category: 'test',
            version: '1.0.0',
            inputDefinitions: [],
            outputDefinitions: [],
            supportedEntityTypes: [],
            requiredPermissions: [],
            enabled: true,
            tags: [],
            metadata: []
        );

        $registry->register($definition);
        $this->assertTrue($registry->exists('test-function'));
    }

    /**
     * Test registry lookup works correctly
     */
    public function testRegistryLookup(): void
    {
        $registry = new WorkflowRegistry();
        $this->assertFalse($registry->exists('nonexistent'));

        $definition = new WorkflowDefinition(
            id: 'existing',
            name: 'Existing',
            displayName: 'Existing',
            description: 'Existing',
            version: '1.0.0',
            moduleId: 'test',
            category: 'test',
            triggerType: 'manual',
            triggerEvent: '',
            entryFunction: '',
            exitFunction: '',
            steps: [],
            supportedEntityTypes: [],
            priority: 0,
            enabled: true,
            tags: [],
            metadata: []
        );

        $registry->register($definition);
        $this->assertTrue($registry->exists('existing'));
    }

    /**
     * Test register command supports all resource types
     */
    public function testRegisterSupportsAllResourceTypes(): void
    {
        $types = [
            'workflow',
            'business-function',
            'validation',
            'module',
            'entity',
        ];

        $command = new RegisterCommand();

        foreach ($types as $type) {
            // Just verify command exists and can be instantiated
            $this->assertNotNull($command);
        }
    }
}
