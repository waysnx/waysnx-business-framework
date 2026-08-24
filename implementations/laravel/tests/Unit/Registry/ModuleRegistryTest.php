<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Registry;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Exceptions\DuplicateModuleException;
use WaysNX\BusinessFramework\Exceptions\ModuleNotFoundException;
use WaysNX\BusinessFramework\Registry\ModuleDefinition;
use WaysNX\BusinessFramework\Registry\ModuleRegistry;

/**
 * ModuleRegistryTest
 *
 * Comprehensive test suite for ModuleRegistry.
 *
 * Test Coverage:
 * - Registration and unregistration
 * - Duplicate detection
 * - Lookup by ID, name, and namespace
 * - Module filtering (enabled, disabled, category, tag)
 * - Dependency queries
 * - Metadata and state operations
 * - Edge cases and error conditions
 * - Extension points and hooks
 *
 * @package WaysNX\BusinessFramework\Tests\Registry
 */
class ModuleRegistryTest extends TestCase
{
    /**
     * Test registry instance
     *

     * @var ModuleRegistry
     */
    private ModuleRegistry $registry;

    /**
     * Set up test fixtures
     *

     * @return void
     */
    protected function setUp(): void
    {
        $this->registry = new ModuleRegistry();
    }

    /**
     * Test registering a single module
     *

     * @return void
     */
    public function testRegisterSingleModule(): void
    {
        $definition = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            description: 'Core project management module',
            namespace: 'App\\Modules\\ProjectManagement',
            version: '1.0.0',
            author: 'acme',
            category: 'business',
            dependencies: [],
            priority: 10,
            enabled: true,
            tags: ['core', 'business'],
            metadata: ['license' => 'MIT']
        );

        $result = $this->registry->register($definition);

        $this->assertSame($this->registry, $result);
        $this->assertTrue($this->registry->exists('project-management'));
        $this->assertSame(1, $this->registry->count());
    }

    /**
     * Test registering multiple modules
     *

     * @return void
     */
    public function testRegisterMultipleModules(): void
    {
        $project = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            namespace: 'App\\Modules\\ProjectManagement'
        );

        $user = new ModuleDefinition(
            id: 'user-management',
            name: 'UserManagement',
            displayName: 'User Management',
            namespace: 'App\\Modules\\UserManagement'
        );

        $this->registry->register($project);
        $this->registry->register($user);

        $this->assertSame(2, $this->registry->count());
        $this->assertTrue($this->registry->exists('project-management'));
        $this->assertTrue($this->registry->exists('user-management'));
    }

    /**
     * Test registering duplicate ID throws exception
     *

     * @return void
     */
    public function testRegisterDuplicateIdThrowsException(): void
    {
        $definition = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            namespace: 'App\\Modules\\ProjectManagement'
        );

        $this->registry->register($definition);

        $this->expectException(DuplicateModuleException::class);
        $this->expectExceptionMessage("Module with ID 'project-management' is already registered");

        $this->registry->register($definition);
    }

    /**
     * Test registering duplicate name throws exception
     *

     * @return void
     */
    public function testRegisterDuplicateNameThrowsException(): void
    {
        $project1 = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            namespace: 'App\\Modules\\ProjectManagement'
        );

        $project2 = new ModuleDefinition(
            id: 'project-management-alt',
            name: 'ProjectManagement',
            displayName: 'Project Management Alt',
            namespace: 'App\\Modules\\ProjectManagementAlt'
        );

        $this->registry->register($project1);

        $this->expectException(DuplicateModuleException::class);
        $this->expectExceptionMessage("Module with name 'ProjectManagement' is already registered");

        $this->registry->register($project2);
    }

    /**
     * Test registering duplicate namespace throws exception
     *

     * @return void
     */
    public function testRegisterDuplicateNamespaceThrowsException(): void
    {
        $project1 = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            namespace: 'App\\Modules\\ProjectManagement'
        );

        $project2 = new ModuleDefinition(
            id: 'project-management-alt',
            name: 'ProjectManagementAlt',
            displayName: 'Project Management Alt',
            namespace: 'App\\Modules\\ProjectManagement'
        );

        $this->registry->register($project1);

        $this->expectException(DuplicateModuleException::class);
        $this->expectExceptionMessage("Module with namespace 'App\Modules\ProjectManagement' is already registered");

        $this->registry->register($project2);
    }

    /**
     * Test finding module by ID
     *

     * @return void
     */
    public function testFindById(): void
    {
        $definition = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            namespace: 'App\\Modules\\ProjectManagement'
        );

        $this->registry->register($definition);

        $found = $this->registry->findById('project-management');

        $this->assertSame('project-management', $found->id);
        $this->assertSame('ProjectManagement', $found->name);
    }

    /**
     * Test finding non-existent module by ID throws exception
     *

     * @return void
     */
    public function testFindByIdNotFoundThrowsException(): void
    {
        $this->expectException(ModuleNotFoundException::class);
        $this->expectExceptionMessage("Module with ID 'project-management' not found in registry");

        $this->registry->findById('project-management');
    }

    /**
     * Test finding module by name
     *

     * @return void
     */
    public function testFindByName(): void
    {
        $definition = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            namespace: 'App\\Modules\\ProjectManagement'
        );

        $this->registry->register($definition);

        $found = $this->registry->findByName('ProjectManagement');

        $this->assertSame('project-management', $found->id);
    }

    /**
     * Test finding non-existent module by name throws exception
     *

     * @return void
     */
    public function testFindByNameNotFoundThrowsException(): void
    {
        $this->expectException(ModuleNotFoundException::class);
        $this->expectExceptionMessage("Module with name 'ProjectManagement' not found in registry");

        $this->registry->findByName('ProjectManagement');
    }

    /**
     * Test finding module by namespace
     *

     * @return void
     */
    public function testFindByNamespace(): void
    {
        $definition = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            namespace: 'App\\Modules\\ProjectManagement'
        );

        $this->registry->register($definition);

        $found = $this->registry->findByNamespace('App\\Modules\\ProjectManagement');

        $this->assertSame('project-management', $found->id);
    }

    /**
     * Test finding non-existent module by namespace throws exception
     *

     * @return void
     */
    public function testFindByNamespaceNotFoundThrowsException(): void
    {
        $this->expectException(ModuleNotFoundException::class);
        $this->expectExceptionMessage("Module with namespace 'App\Modules\ProjectManagement' not found in registry");

        $this->registry->findByNamespace('App\\Modules\\ProjectManagement');
    }

    /**
     * Test unregistering a module
     *

     * @return void
     */
    public function testUnregisterModule(): void
    {
        $definition = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            namespace: 'App\\Modules\\ProjectManagement'
        );

        $this->registry->register($definition);
        $this->assertTrue($this->registry->exists('project-management'));

        $result = $this->registry->unregister('project-management');

        $this->assertSame($this->registry, $result);
        $this->assertFalse($this->registry->exists('project-management'));
        $this->assertSame(0, $this->registry->count());
    }

    /**
     * Test unregistering non-existent module throws exception
     *

     * @return void
     */
    public function testUnregisterNonExistentThrowsException(): void
    {
        $this->expectException(ModuleNotFoundException::class);

        $this->registry->unregister('project-management');
    }

    /**
     * Test getting all modules
     *

     * @return void
     */
    public function testGetAllModules(): void
    {
        $project = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            namespace: 'App\\Modules\\ProjectManagement'
        );

        $user = new ModuleDefinition(
            id: 'user-management',
            name: 'UserManagement',
            displayName: 'User Management',
            namespace: 'App\\Modules\\UserManagement'
        );

        $this->registry->register($project);
        $this->registry->register($user);

        $all = $this->registry->all();

        $this->assertCount(2, $all);
        $this->assertArrayHasKey('project-management', $all);
        $this->assertArrayHasKey('user-management', $all);
    }

    /**
     * Test counting modules
     *

     * @return void
     */
    public function testCountModules(): void
    {
        $this->assertSame(0, $this->registry->count());

        $definition = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            namespace: 'App\\Modules\\ProjectManagement'
        );

        $this->registry->register($definition);

        $this->assertSame(1, $this->registry->count());
    }

    /**
     * Test checking module existence
     *

     * @return void
     */
    public function testCheckModuleExists(): void
    {
        $this->assertFalse($this->registry->exists('project-management'));

        $definition = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            namespace: 'App\\Modules\\ProjectManagement'
        );

        $this->registry->register($definition);

        $this->assertTrue($this->registry->exists('project-management'));
    }

    /**
     * Test checking name existence
     *

     * @return void
     */
    public function testCheckNameExists(): void
    {
        $this->assertFalse($this->registry->nameExists('ProjectManagement'));

        $definition = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            namespace: 'App\\Modules\\ProjectManagement'
        );

        $this->registry->register($definition);

        $this->assertTrue($this->registry->nameExists('ProjectManagement'));
    }

    /**
     * Test checking namespace existence
     *

     * @return void
     */
    public function testCheckNamespaceExists(): void
    {
        $this->assertFalse($this->registry->namespaceExists('App\\Modules\\ProjectManagement'));

        $definition = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            namespace: 'App\\Modules\\ProjectManagement'
        );

        $this->registry->register($definition);

        $this->assertTrue($this->registry->namespaceExists('App\\Modules\\ProjectManagement'));
    }

    /**
     * Test getting enabled modules
     *

     * @return void
     */
    public function testGetEnabledModules(): void
    {
        $enabled = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            enabled: true
        );

        $disabled = new ModuleDefinition(
            id: 'user-management',
            name: 'UserManagement',
            displayName: 'User Management',
            enabled: false
        );

        $this->registry->register($enabled);
        $this->registry->register($disabled);

        $enabledModules = $this->registry->enabled();

        $this->assertCount(1, $enabledModules);
        $this->assertArrayHasKey('project-management', $enabledModules);
    }

    /**
     * Test getting disabled modules
     *

     * @return void
     */
    public function testGetDisabledModules(): void
    {
        $enabled = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            enabled: true
        );

        $disabled = new ModuleDefinition(
            id: 'user-management',
            name: 'UserManagement',
            displayName: 'User Management',
            enabled: false
        );

        $this->registry->register($enabled);
        $this->registry->register($disabled);

        $disabledModules = $this->registry->disabled();

        $this->assertCount(1, $disabledModules);
        $this->assertArrayHasKey('user-management', $disabledModules);
    }

    /**
     * Test finding modules by category
     *

     * @return void
     */
    public function testFindByCategory(): void
    {
        $business = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            category: 'business'
        );

        $infrastructure = new ModuleDefinition(
            id: 'authentication',
            name: 'Authentication',
            displayName: 'Authentication',
            category: 'infrastructure'
        );

        $this->registry->register($business);
        $this->registry->register($infrastructure);

        $businessModules = $this->registry->findByCategory('business');

        $this->assertCount(1, $businessModules);
        $this->assertArrayHasKey('project-management', $businessModules);
    }

    /**
     * Test finding modules by tag
     *

     * @return void
     */
    public function testFindByTag(): void
    {
        $project = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            tags: ['core', 'business']
        );

        $user = new ModuleDefinition(
            id: 'user-management',
            name: 'UserManagement',
            displayName: 'User Management',
            tags: ['core', 'infrastructure']
        );

        $this->registry->register($project);
        $this->registry->register($user);

        $coreModules = $this->registry->findByTag('core');

        $this->assertCount(2, $coreModules);
        $this->assertArrayHasKey('project-management', $coreModules);
        $this->assertArrayHasKey('user-management', $coreModules);
    }

    /**
     * Test getting modules with dependencies
     *

     * @return void
     */
    public function testGetModuleDependencies(): void
    {
        $definition = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            dependencies: ['core', 'user', 'validation']
        );

        $this->registry->register($definition);

        $dependencies = $this->registry->dependencies('project-management');

        $this->assertCount(3, $dependencies);
        $this->assertContains('core', $dependencies);
        $this->assertContains('user', $dependencies);
        $this->assertContains('validation', $dependencies);
    }

    /**
     * Test checking if module has dependencies
     *

     * @return void
     */
    public function testCheckModuleHasDependencies(): void
    {
        $withDeps = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            dependencies: ['core']
        );

        $noDeps = new ModuleDefinition(
            id: 'core',
            name: 'Core',
            displayName: 'Core',
            dependencies: []
        );

        $this->registry->register($withDeps);
        $this->registry->register($noDeps);

        $this->assertTrue($this->registry->hasDependencies('project-management'));
        $this->assertFalse($this->registry->hasDependencies('core'));
    }

    /**
     * Test getting dependent modules
     *

     * @return void
     */
    public function testGetDependentModules(): void
    {
        $core = new ModuleDefinition(
            id: 'core',
            name: 'Core',
            displayName: 'Core'
        );

        $project = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management',
            dependencies: ['core']
        );

        $user = new ModuleDefinition(
            id: 'user-management',
            name: 'UserManagement',
            displayName: 'User Management',
            dependencies: ['core']
        );

        $this->registry->register($core);
        $this->registry->register($project);
        $this->registry->register($user);

        $dependents = $this->registry->dependents('core');

        $this->assertCount(2, $dependents);
        $this->assertArrayHasKey('project-management', $dependents);
        $this->assertArrayHasKey('user-management', $dependents);
    }

    /**
     * Test getting dependents for non-existent module throws exception
     *

     * @return void
     */
    public function testGetDependentsForNonExistentThrowsException(): void
    {
        $this->expectException(ModuleNotFoundException::class);

        $this->registry->dependents('core');
    }

    /**
     * Test getting experimental modules
     *

     * @return void
     */
    public function testGetExperimentalModules(): void
    {
        $experimental = new ModuleDefinition(
            id: 'advanced-features',
            name: 'AdvancedFeatures',
            displayName: 'Advanced Features',
            metadata: ['experimental' => true]
        );

        $stable = new ModuleDefinition(
            id: 'core',
            name: 'Core',
            displayName: 'Core'
        );

        $this->registry->register($experimental);
        $this->registry->register($stable);

        $experimModules = $this->registry->experimental();

        $this->assertCount(1, $experimModules);
        $this->assertArrayHasKey('advanced-features', $experimModules);
    }

    /**
     * Test getting deprecated modules
     *

     * @return void
     */
    public function testGetDeprecatedModules(): void
    {
        $deprecated = new ModuleDefinition(
            id: 'old-system',
            name: 'OldSystem',
            displayName: 'Old System',
            metadata: ['deprecated' => true]
        );

        $current = new ModuleDefinition(
            id: 'new-system',
            name: 'NewSystem',
            displayName: 'New System'
        );

        $this->registry->register($deprecated);
        $this->registry->register($current);

        $deprecatedModules = $this->registry->deprecated();

        $this->assertCount(1, $deprecatedModules);
        $this->assertArrayHasKey('old-system', $deprecatedModules);
    }

    /**
     * Test sorting modules by priority
     *

     * @return void
     */
    public function testSortedByPriority(): void
    {
        $low = new ModuleDefinition(
            id: 'low',
            name: 'Low',
            displayName: 'Low Priority',
            priority: 1
        );

        $high = new ModuleDefinition(
            id: 'high',
            name: 'High',
            displayName: 'High Priority',
            priority: 100
        );

        $medium = new ModuleDefinition(
            id: 'medium',
            name: 'Medium',
            displayName: 'Medium Priority',
            priority: 50
        );

        $this->registry->register($low);
        $this->registry->register($high);
        $this->registry->register($medium);

        $sorted = $this->registry->sortedByPriority();

        $ids = array_keys($sorted);
        $this->assertSame('high', $ids[0]);
        $this->assertSame('medium', $ids[1]);
        $this->assertSame('low', $ids[2]);
    }

    /**
     * Test clearing all modules
     *

     * @return void
     */
    public function testClearAllModules(): void
    {
        $definition = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management'
        );

        $this->registry->register($definition);

        $this->assertSame(1, $this->registry->count());

        $result = $this->registry->clear();

        $this->assertSame($this->registry, $result);
        $this->assertSame(0, $this->registry->count());
    }

    /**
     * Test empty registry
     *

     * @return void
     */
    public function testEmptyRegistry(): void
    {
        $this->assertSame(0, $this->registry->count());
        $this->assertCount(0, $this->registry->all());
        $this->assertCount(0, $this->registry->enabled());
    }

    /**
     * Test method chaining
     *

     * @return void
     */
    public function testMethodChaining(): void
    {
        $project = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management'
        );

        $user = new ModuleDefinition(
            id: 'user-management',
            name: 'UserManagement',
            displayName: 'User Management'
        );

        $result = $this->registry
            ->register($project)
            ->register($user);

        $this->assertSame($this->registry, $result);
        $this->assertSame(2, $this->registry->count());
    }

    /**
     * Test extension hooks are called
     *

     * @return void
     */
    public function testExtensionHooksAreCalled(): void
    {
        $registry = new TestModuleRegistry();

        $definition = new ModuleDefinition(
            id: 'project-management',
            name: 'ProjectManagement',
            displayName: 'Project Management'
        );

        $registry->register($definition);

        $this->assertTrue($registry->beforeRegisterCalled);
        $this->assertTrue($registry->afterRegisterCalled);

        $registry->unregister('project-management');

        $this->assertTrue($registry->beforeUnregisterCalled);
        $this->assertTrue($registry->afterUnregisterCalled);
    }

    /**
     * Test module without namespace
     *

     * @return void
     */
    public function testModuleWithoutNamespace(): void
    {
        $definition = new ModuleDefinition(
            id: 'simple',
            name: 'Simple',
            displayName: 'Simple Module'
        );

        $result = $this->registry->register($definition);

        $this->assertSame($this->registry, $result);
        $this->assertTrue($this->registry->exists('simple'));
        $this->assertFalse($this->registry->namespaceExists(''));
    }
}

/**
 * TestModuleRegistry
 *
 * Test implementation of ModuleRegistry that tracks hook calls.
 *

 * @package WaysNX\BusinessFramework\Tests\Registry
 */
class TestModuleRegistry extends ModuleRegistry
{
    /**
     * Track if beforeRegister was called
     *

     * @var bool
     */
    public bool $beforeRegisterCalled = false;

    /**
     * Track if afterRegister was called
     *

     * @var bool
     */
    public bool $afterRegisterCalled = false;

    /**
     * Track if beforeUnregister was called
     *

     * @var bool
     */
    public bool $beforeUnregisterCalled = false;

    /**
     * Track if afterUnregister was called
     *

     * @var bool
     */
    public bool $afterUnregisterCalled = false;

    /**
     * Override beforeRegister to track calls
     *

     * @param ModuleDefinition $definition The module definition
     *

     * @return void
     */
    protected function beforeRegister(ModuleDefinition $definition): void
    {
        $this->beforeRegisterCalled = true;
    }

    /**
     * Override afterRegister to track calls
     *

     * @param ModuleDefinition $definition The module definition
     *

     * @return void
     */
    protected function afterRegister(ModuleDefinition $definition): void
    {
        $this->afterRegisterCalled = true;
    }

    /**
     * Override beforeUnregister to track calls
     *

     * @param ModuleDefinition $definition The module definition
     *

     * @return void
     */
    protected function beforeUnregister(ModuleDefinition $definition): void
    {
        $this->beforeUnregisterCalled = true;
    }

    /**
     * Override afterUnregister to track calls
     *

     * @param ModuleDefinition $definition The module definition
     *

     * @return void
     */
    protected function afterUnregister(ModuleDefinition $definition): void
    {
        $this->afterUnregisterCalled = true;
    }
}
