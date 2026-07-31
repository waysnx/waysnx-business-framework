<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Registry;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Exceptions\DuplicateEntityException;
use WaysNX\BusinessFramework\Exceptions\EntityNotFoundException;
use WaysNX\BusinessFramework\Registry\EntityDefinition;
use WaysNX\BusinessFramework\Registry\EntityRegistry;

/**
 * EntityRegistryTest
 *
 * Comprehensive test suite for EntityRegistry.
 *
 * Test Coverage:
 * - Registration and unregistration
 * - Duplicate entity detection
 * - Lookup by ID, class, name, and alias
 * - Metadata and tag operations
 * - Alias management
 * - Edge cases and error conditions
 * - Extension points and hooks
 *
 * @package WaysNX\BusinessFramework\Tests\Registry
 */
class EntityRegistryTest extends TestCase
{
    /**
     * Test registry instance
     *
     * @var EntityRegistry
     */
    private EntityRegistry $registry;

    /**
     * Set up test fixtures
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->registry = new EntityRegistry();
    }

    /**
     * Test registering a single entity
     *
     * @return void
     */
    public function testRegisterSingleEntity(): void
    {
        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models',
            description: 'Core business project entity',
            version: '1.0.0',
            tags: ['core', 'business'],
            metadata: ['author' => 'acme']
        );

        $result = $this->registry->register($definition);

        $this->assertSame($this->registry, $result);
        $this->assertTrue($this->registry->exists('project'));
        $this->assertSame(1, $this->registry->count());
    }

    /**
     * Test registering multiple entities
     *
     * @return void
     */
    public function testRegisterMultipleEntities(): void
    {
        $project = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $requirement = new EntityDefinition(
            id: 'requirement',
            name: 'Requirement',
            displayName: 'Business Requirement',
            className: 'App\\Models\\Requirement',
            namespace: 'App\\Models'
        );

        $this->registry->register($project);
        $this->registry->register($requirement);

        $this->assertSame(2, $this->registry->count());
        $this->assertTrue($this->registry->exists('project'));
        $this->assertTrue($this->registry->exists('requirement'));
    }

    /**
     * Test registering duplicate ID throws exception
     *
     * @return void
     */
    public function testRegisterDuplicateIdThrowsException(): void
    {
        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($definition);

        $this->expectException(DuplicateEntityException::class);
        $this->expectExceptionMessage("Entity with ID 'project' is already registered");

        $this->registry->register($definition);
    }

    /**
     * Test registering duplicate class throws exception
     *
     * @return void
     */
    public function testRegisterDuplicateClassThrowsException(): void
    {
        $project1 = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $project2 = new EntityDefinition(
            id: 'project_alias',
            name: 'ProjectAlias',
            displayName: 'Project Alias',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($project1);

        $this->expectException(DuplicateEntityException::class);
        $this->expectExceptionMessage("Entity class 'App\\Models\\Project' is already registered");

        $this->registry->register($project2);
    }

    /**
     * Test registering duplicate name throws exception
     *
     * @return void
     */
    public function testRegisterDuplicateNameThrowsException(): void
    {
        $project1 = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $project2 = new EntityDefinition(
            id: 'project_alt',
            name: 'Project',
            displayName: 'Project Alternative',
            className: 'App\\Models\\ProjectAlt',
            namespace: 'App\\Models'
        );

        $this->registry->register($project1);

        $this->expectException(DuplicateEntityException::class);
        $this->expectExceptionMessage("Entity name 'Project' is already registered");

        $this->registry->register($project2);
    }

    /**
     * Test finding entity by ID
     *
     * @return void
     */
    public function testFindById(): void
    {
        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($definition);

        $found = $this->registry->findById('project');

        $this->assertSame('project', $found->id);
        $this->assertSame('Project', $found->name);
    }

    /**
     * Test finding non-existent entity by ID throws exception
     *
     * @return void
     */
    public function testFindByIdNotFoundThrowsException(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage("Entity with ID 'project' not found in registry");

        $this->registry->findById('project');
    }

    /**
     * Test finding entity by class name
     *

     * @return void
     */
    public function testFindByClass(): void
    {
        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($definition);

        $found = $this->registry->findByClass('App\\Models\\Project');

        $this->assertSame('project', $found->id);
    }

    /**
     * Test finding non-existent entity by class throws exception
     *

     * @return void
     */
    public function testFindByClassNotFoundThrowsException(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage("Entity with class 'App\\Models\\Project' not found in registry");

        $this->registry->findByClass('App\\Models\\Project');
    }

    /**
     * Test finding entity by name
     *

     * @return void
     */
    public function testFindByName(): void
    {
        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($definition);

        $found = $this->registry->findByName('Project');

        $this->assertSame('project', $found->id);
    }

    /**
     * Test finding non-existent entity by name throws exception
     *

     * @return void
     */
    public function testFindByNameNotFoundThrowsException(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage("Entity with name 'Project' not found in registry");

        $this->registry->findByName('Project');
    }

    /**
     * Test unregistering an entity
     *

     * @return void
     */
    public function testUnregisterEntity(): void
    {
        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($definition);
        $this->assertTrue($this->registry->exists('project'));

        $result = $this->registry->unregister('project');

        $this->assertSame($this->registry, $result);
        $this->assertFalse($this->registry->exists('project'));
        $this->assertSame(0, $this->registry->count());
    }

    /**
     * Test unregistering non-existent entity throws exception
     *

     * @return void
     */
    public function testUnregisterNonExistentThrowsException(): void
    {
        $this->expectException(EntityNotFoundException::class);

        $this->registry->unregister('project');
    }

    /**
     * Test getting all registered entities
     *

     * @return void
     */
    public function testGetAllEntities(): void
    {
        $project = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $requirement = new EntityDefinition(
            id: 'requirement',
            name: 'Requirement',
            displayName: 'Business Requirement',
            className: 'App\\Models\\Requirement',
            namespace: 'App\\Models'
        );

        $this->registry->register($project);
        $this->registry->register($requirement);

        $all = $this->registry->all();

        $this->assertCount(2, $all);
        $this->assertArrayHasKey('project', $all);
        $this->assertArrayHasKey('requirement', $all);
    }

    /**
     * Test counting entities
     *

     * @return void
     */
    public function testCountEntities(): void
    {
        $this->assertSame(0, $this->registry->count());

        $project = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($project);

        $this->assertSame(1, $this->registry->count());
    }

    /**
     * Test checking entity existence
     *

     * @return void
     */
    public function testCheckEntityExists(): void
    {
        $this->assertFalse($this->registry->exists('project'));

        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($definition);

        $this->assertTrue($this->registry->exists('project'));
    }

    /**
     * Test checking class existence
     *

     * @return void
     */
    public function testCheckClassExists(): void
    {
        $this->assertFalse($this->registry->classExists('App\\Models\\Project'));

        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($definition);

        $this->assertTrue($this->registry->classExists('App\\Models\\Project'));
    }

    /**
     * Test checking name existence
     *

     * @return void
     */
    public function testCheckNameExists(): void
    {
        $this->assertFalse($this->registry->nameExists('Project'));

        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($definition);

        $this->assertTrue($this->registry->nameExists('Project'));
    }

    /**
     * Test adding an alias
     *

     * @return void
     */
    public function testAddAlias(): void
    {
        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($definition);

        $result = $this->registry->alias('project', 'proj');

        $this->assertSame($this->registry, $result);
        $this->assertTrue($this->registry->aliasExists('proj'));
    }

    /**
     * Test finding entity by alias
     *

     * @return void
     */
    public function testFindByAlias(): void
    {
        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($definition);
        $this->registry->alias('project', 'proj');

        $found = $this->registry->findByAlias('proj');

        $this->assertSame('project', $found->id);
    }

    /**
     * Test finding non-existent alias throws exception
     *

     * @return void
     */
    public function testFindByAliasNotFoundThrowsException(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage("Entity alias 'proj' not found in registry");

        $this->registry->findByAlias('proj');
    }

    /**
     * Test adding duplicate alias throws exception
     *

     * @return void
     */
    public function testAddDuplicateAliasThrowsException(): void
    {
        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($definition);
        $this->registry->alias('project', 'proj');

        $this->expectException(DuplicateEntityException::class);
        $this->expectExceptionMessage("Alias 'proj' is already registered");

        $this->registry->alias('project', 'proj');
    }

    /**
     * Test adding alias for non-existent entity throws exception
     *

     * @return void
     */
    public function testAliasNonExistentEntityThrowsException(): void
    {
        $this->expectException(EntityNotFoundException::class);

        $this->registry->alias('project', 'proj');
    }

    /**
     * Test removing an alias
     *

     * @return void
     */
    public function testRemoveAlias(): void
    {
        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($definition);
        $this->registry->alias('project', 'proj');

        $this->assertTrue($this->registry->aliasExists('proj'));

        $result = $this->registry->removeAlias('proj');

        $this->assertSame($this->registry, $result);
        $this->assertFalse($this->registry->aliasExists('proj'));
    }

    /**
     * Test removing non-existent alias throws exception
     *

     * @return void
     */
    public function testRemoveNonExistentAliasThrowsException(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage("Alias 'proj' not found in registry");

        $this->registry->removeAlias('proj');
    }

    /**
     * Test getting aliases for entity
     *

     * @return void
     */
    public function testGetAliasesForEntity(): void
    {
        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($definition);
        $this->registry->alias('project', 'proj');
        $this->registry->alias('project', 'project-entity');

        $aliases = $this->registry->getAliasesFor('project');

        $this->assertCount(2, $aliases);
        $this->assertContains('proj', $aliases);
        $this->assertContains('project-entity', $aliases);
    }

    /**
     * Test getting aliases for non-existent entity throws exception
     *

     * @return void
     */
    public function testGetAliasesForNonExistentThrowsException(): void
    {
        $this->expectException(EntityNotFoundException::class);

        $this->registry->getAliasesFor('project');
    }

    /**
     * Test finding entities by tag
     *

     * @return void
     */
    public function testFindByTag(): void
    {
        $project = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models',
            tags: ['core', 'business']
        );

        $requirement = new EntityDefinition(
            id: 'requirement',
            name: 'Requirement',
            displayName: 'Business Requirement',
            className: 'App\\Models\\Requirement',
            namespace: 'App\\Models',
            tags: ['core', 'workflow']
        );

        $task = new EntityDefinition(
            id: 'task',
            name: 'Task',
            displayName: 'Business Task',
            className: 'App\\Models\\Task',
            namespace: 'App\\Models',
            tags: ['workflow']
        );

        $this->registry->register($project);
        $this->registry->register($requirement);
        $this->registry->register($task);

        $coreEntities = $this->registry->findByTag('core');

        $this->assertCount(2, $coreEntities);
        $this->assertArrayHasKey('project', $coreEntities);
        $this->assertArrayHasKey('requirement', $coreEntities);
    }

    /**
     * Test finding entities by non-existent tag returns empty array
     *

     * @return void
     */
    public function testFindByNonExistentTagReturnsEmpty(): void
    {
        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models',
            tags: ['core']
        );

        $this->registry->register($definition);

        $result = $this->registry->findByTag('nonexistent');

        $this->assertCount(0, $result);
    }

    /**
     * Test getting experimental entities
     *

     * @return void
     */
    public function testGetExperimentalEntities(): void
    {
        $project = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models',
            metadata: ['experimental' => true]
        );

        $requirement = new EntityDefinition(
            id: 'requirement',
            name: 'Requirement',
            displayName: 'Business Requirement',
            className: 'App\\Models\\Requirement',
            namespace: 'App\\Models'
        );

        $this->registry->register($project);
        $this->registry->register($requirement);

        $experimental = $this->registry->experimental();

        $this->assertCount(1, $experimental);
        $this->assertArrayHasKey('project', $experimental);
    }

    /**
     * Test getting deprecated entities
     *

     * @return void
     */
    public function testGetDeprecatedEntities(): void
    {
        $project = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models',
            metadata: ['deprecated' => true]
        );

        $requirement = new EntityDefinition(
            id: 'requirement',
            name: 'Requirement',
            displayName: 'Business Requirement',
            className: 'App\\Models\\Requirement',
            namespace: 'App\\Models'
        );

        $this->registry->register($project);
        $this->registry->register($requirement);

        $deprecated = $this->registry->deprecated();

        $this->assertCount(1, $deprecated);
        $this->assertArrayHasKey('project', $deprecated);
    }

    /**
     * Test clearing all entities
     *

     * @return void
     */
    public function testClearAllEntities(): void
    {
        $project = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($project);
        $this->registry->alias('project', 'proj');

        $this->assertSame(1, $this->registry->count());

        $result = $this->registry->clear();

        $this->assertSame($this->registry, $result);
        $this->assertSame(0, $this->registry->count());
        $this->assertFalse($this->registry->aliasExists('proj'));
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
        $this->assertCount(0, $this->registry->findByTag('core'));
    }

    /**
     * Test unregistering entity removes aliases
     *

     * @return void
     */
    public function testUnregisterRemovesAliases(): void
    {
        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $this->registry->register($definition);
        $this->registry->alias('project', 'proj');
        $this->registry->alias('project', 'project-entity');

        $this->assertTrue($this->registry->aliasExists('proj'));
        $this->assertTrue($this->registry->aliasExists('project-entity'));

        $this->registry->unregister('project');

        $this->assertFalse($this->registry->aliasExists('proj'));
        $this->assertFalse($this->registry->aliasExists('project-entity'));
    }

    /**
     * Test method chaining
     *

     * @return void
     */
    public function testMethodChaining(): void
    {
        $project = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $requirement = new EntityDefinition(
            id: 'requirement',
            name: 'Requirement',
            displayName: 'Business Requirement',
            className: 'App\\Models\\Requirement',
            namespace: 'App\\Models'
        );

        $result = $this->registry
            ->register($project)
            ->register($requirement)
            ->alias('project', 'proj')
            ->alias('requirement', 'req');

        $this->assertSame($this->registry, $result);
        $this->assertSame(2, $this->registry->count());
        $this->assertTrue($this->registry->aliasExists('proj'));
        $this->assertTrue($this->registry->aliasExists('req'));
    }

    /**
     * Test extension hooks are called
     *

     * @return void
     */
    public function testExtensionHooksAreCalled(): void
    {
        $registry = new TestRegistry();

        $definition = new EntityDefinition(
            id: 'project',
            name: 'Project',
            displayName: 'Business Project',
            className: 'App\\Models\\Project',
            namespace: 'App\\Models'
        );

        $registry->register($definition);

        $this->assertTrue($registry->beforeRegisterCalled);
        $this->assertTrue($registry->afterRegisterCalled);

        $registry->unregister('project');

        $this->assertTrue($registry->beforeUnregisterCalled);
        $this->assertTrue($registry->afterUnregisterCalled);
    }
}

/**
 * TestRegistry
 *
 * Test implementation of EntityRegistry that tracks hook calls.
 *
 * @package WaysNX\BusinessFramework\Tests\Registry
 */
class TestRegistry extends EntityRegistry
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

     * @param EntityDefinition $definition The entity definition
     *

     * @return void
     */
    protected function beforeRegister(EntityDefinition $definition): void
    {
        $this->beforeRegisterCalled = true;
    }

    /**
     * Override afterRegister to track calls
     *

     * @param EntityDefinition $definition The entity definition
     *

     * @return void
     */
    protected function afterRegister(EntityDefinition $definition): void
    {
        $this->afterRegisterCalled = true;
    }

    /**
     * Override beforeUnregister to track calls
     *

     * @param EntityDefinition $definition The entity definition
     *

     * @return void
     */
    protected function beforeUnregister(EntityDefinition $definition): void
    {
        $this->beforeUnregisterCalled = true;
    }

    /**
     * Override afterUnregister to track calls
     *

     * @param EntityDefinition $definition The entity definition
     *

     * @return void
     */
    protected function afterUnregister(EntityDefinition $definition): void
    {
        $this->afterUnregisterCalled = true;
    }
}
