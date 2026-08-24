<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Unit\Repositories;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Contracts\RepositoryInterface;
use WaysNX\BusinessFramework\Exceptions\EntityNotFoundException;
use WaysNX\BusinessFramework\Models\BaseModel;
use WaysNX\BusinessFramework\Repositories\BaseRepository;

/**
 * BaseRepositoryTest
 *
 * PHPUnit test suite for BaseRepository functionality.
 *
 * Tests cover:
 * - Repository initialization
 * - CRUD operations
 * - Pagination
 * - Filtering hooks
 * - Sorting hooks
 * - Lifecycle hook execution
 * - Error handling
 * - Interface compliance
 *
 * @package WaysNX\BusinessFramework\Tests\Unit\Repositories
 */
class BaseRepositoryTest extends TestCase
{
    /**
     * Test repository for testing
     *
     * @var TestRepository
     */
    private TestRepository $repository;

    /**
     * Set up test fixtures
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->repository = new TestRepository(new TestEntity());
    }

    /**
     * Test repository instantiation
     *
     * @return void
     */
    public function testRepositoryInstantiationWithModel(): void
    {
        $model = new TestEntity();
        $repository = new BaseRepository($model);

        $this->assertInstanceOf(RepositoryInterface::class, $repository);
        $this->assertSame($model, $repository->getModel());
    }

    /**
     * Test model can be set after instantiation
     *
     * @return void
     */
    public function testModelCanBeSet(): void
    {
        $model1 = new TestEntity();
        $model2 = new TestEntity();

        $repository = new BaseRepository($model1);
        $this->assertSame($model1, $repository->getModel());

        $repository->setModel($model2);
        $this->assertSame($model2, $repository->getModel());
    }

    /**
     * Test setModel returns self for method chaining
     *
     * @return void
     */
    public function testSetModelReturnsself(): void
    {
        $repository = new BaseRepository(new TestEntity());
        $result = $repository->setModel(new TestEntity());

        $this->assertSame($repository, $result);
    }

    /**
     * Test newQuery creates a fresh query instance
     *
     * @return void
     */
    public function testNewQueryCreatesFreshInstance(): void
    {
        $query1 = $this->repository->newQuery();
        $query2 = $this->repository->newQuery();

        // Should be different instances
        $this->assertNotSame($query1, $query2);
    }

    /**
     * Test query() returns the current query
     *
     * @return void
     */
    public function testQueryReturnsCurrentQuery(): void
    {
        $query = $this->repository->query();

        $this->assertNotNull($query);
        $this->assertSame($query, $this->repository->query());
    }

    /**
     * Test create() creates a new entity
     *
     * @return void
     */
    public function testCreateCreatesNewEntity(): void
    {
        $data = ['name' => 'Test Entity'];
        $entity = $this->repository->create($data);

        $this->assertInstanceOf(TestEntity::class, $entity);
    }

    /**
     * Test create() calls beforeCreate hook
     *
     * @return void
     */
    public function testCreateCallsBeforeCreateHook(): void
    {
        $entity = $this->repository->create(['name' => 'Test']);

        $this->assertTrue($this->repository->beforeCreateCalled);
    }

    /**
     * Test create() calls afterCreate hook
     *

     * @return void
     */
    public function testCreateCallsAfterCreateHook(): void
    {
        $entity = $this->repository->create(['name' => 'Test']);

        $this->assertTrue($this->repository->afterCreateCalled);
    }

    /**
     * Test update() updates an entity
     *
     * @return void
     */
    public function testUpdateUpdatesEntity(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $result = $this->repository->update('test-id', ['name' => 'Updated']);

        $this->assertTrue($result);
    }

    /**
     * Test update() throws exception if entity not found
     *
     * @return void
     */
    public function testUpdateThrowsExceptionIfNotFound(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->repository->update('non-existent', ['name' => 'Updated']);
    }

    /**
     * Test update() calls beforeUpdate hook
     *
     * @return void
     */
    public function testUpdateCallsBeforeUpdateHook(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $this->repository->update('test-id', ['name' => 'Updated']);

        $this->assertTrue($this->repository->beforeUpdateCalled);
    }

    /**
     * Test update() calls afterUpdate hook
     *
     * @return void
     */
    public function testUpdateCallsAfterUpdateHook(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $this->repository->update('test-id', ['name' => 'Updated']);

        $this->assertTrue($this->repository->afterUpdateCalled);
    }

    /**
     * Test delete() deletes an entity
     *
     * @return void
     */
    public function testDeleteDeletesEntity(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $result = $this->repository->delete('test-id');

        $this->assertTrue($result);
    }

    /**
     * Test delete() throws exception if entity not found
     *
     * @return void
     */
    public function testDeleteThrowsExceptionIfNotFound(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->repository->delete('non-existent');
    }

    /**
     * Test delete() calls beforeDelete hook
     *
     * @return void
     */
    public function testDeleteCallsBeforeDeleteHook(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $this->repository->delete('test-id');

        $this->assertTrue($this->repository->beforeDeleteCalled);
    }

    /**
     * Test delete() calls afterDelete hook
     *
     * @return void
     */
    public function testDeleteCallsAfterDeleteHook(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $this->repository->delete('test-id');

        $this->assertTrue($this->repository->afterDeleteCalled);
    }

    /**
     * Test restore() restores a deleted entity
     *
     * @return void
     */
    public function testRestoreRestoresEntity(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $result = $this->repository->restore('test-id');

        $this->assertTrue($result);
    }

    /**
     * Test restore() throws exception if entity not found
     *
     * @return void
     */
    public function testRestoreThrowsExceptionIfNotFound(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->repository->restore('non-existent');
    }

    /**
     * Test restore() calls beforeRestore hook
     *
     * @return void
     */
    public function testRestoreCallsBeforeRestoreHook(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $this->repository->restore('test-id');

        $this->assertTrue($this->repository->beforeRestoreCalled);
    }

    /**
     * Test restore() calls afterRestore hook
     *
     * @return void
     */
    public function testRestoreCallsAfterRestoreHook(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $this->repository->restore('test-id');

        $this->assertTrue($this->repository->afterRestoreCalled);
    }

    /**
     * Test find() returns entity if found
     *
     * @return void
     */
    public function testFindReturnsEntityIfFound(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $entity = $this->repository->find('test-id');

        $this->assertNotNull($entity);
        $this->assertInstanceOf(TestEntity::class, $entity);
    }

    /**
     * Test find() returns null if not found
     *
     * @return void
     */
    public function testFindReturnsNullIfNotFound(): void
    {
        $entity = $this->repository->find('non-existent');

        $this->assertNull($entity);
    }

    /**
     * Test findOrFail() returns entity if found
     *
     * @return void
     */
    public function testFindOrFailReturnsEntityIfFound(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $entity = $this->repository->findOrFail('test-id');

        $this->assertNotNull($entity);
        $this->assertInstanceOf(TestEntity::class, $entity);
    }

    /**
     * Test findOrFail() throws exception if not found
     *
     * @return void
     */
    public function testFindOrFailThrowsExceptionIfNotFound(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->repository->findOrFail('non-existent');
    }

    /**
     * Test exists() returns true if entity exists
     *
     * @return void
     */
    public function testExistsReturnsTrueIfFound(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $exists = $this->repository->exists('test-id');

        $this->assertTrue($exists);
    }

    /**
     * Test exists() returns false if entity not found
     *
     * @return void
     */
    public function testExistsReturnsFalseIfNotFound(): void
    {
        $exists = $this->repository->exists('non-existent');

        $this->assertFalse($exists);
    }

    /**
     * Test all() returns all entities
     *
     * @return void
     */
    public function testAllReturnsAllEntities(): void
    {
        $entities = $this->repository->all();

        $this->assertIsArray($entities);
    }

    /**
     * Test count() returns number of entities
     *
     * @return void
     */
    public function testCountReturnsEntityCount(): void
    {
        $count = $this->repository->count();

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    /**
     * Test paginate() returns paginated results
     *
     * @return void
     */
    public function testPaginateReturnsPaginatedResults(): void
    {
        $result = $this->repository->paginate(15, 1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('per_page', $result);
        $this->assertArrayHasKey('current_page', $result);
        $this->assertArrayHasKey('last_page', $result);
        $this->assertArrayHasKey('from', $result);
        $this->assertArrayHasKey('to', $result);
    }

    /**
     * Test paginate() with valid pagination parameters
     *
     * @return void
     */
    public function testPaginateWithValidParameters(): void
    {
        $result = $this->repository->paginate(10, 2);

        $this->assertEquals(10, $result['per_page']);
        $this->assertEquals(2, $result['current_page']);
    }

    /**
     * Test paginate() with negative page defaults to 1
     *
     * @return void
     */
    public function testPaginateNegativePageDefaultsToOne(): void
    {
        $result = $this->repository->paginate(15, -1);

        $this->assertEquals(1, $result['current_page']);
    }

    /**
     * Test paginate() calls applyFilters hook
     *
     * @return void
     */
    public function testPaginateCallsApplyFiltersHook(): void
    {
        $this->repository->paginate(15, 1);

        $this->assertTrue($this->repository->applyFiltersCalled);
    }

    /**
     * Test paginate() calls applySorting hook
     *
     * @return void
     */
    public function testPaginateCallsApplySortingHook(): void
    {
        $this->repository->paginate(15, 1);

        $this->assertTrue($this->repository->applySortingCalled);
    }

    /**
     * Test all() calls applyFilters hook
     *
     * @return void
     */
    public function testAllCallsApplyFiltersHook(): void
    {
        $this->repository->all();

        $this->assertTrue($this->repository->applyFiltersCalled);
    }

    /**
     * Test all() calls applySorting hook
     *
     * @return void
     */
    public function testAllCallsApplySortingHook(): void
    {
        $this->repository->all();

        $this->assertTrue($this->repository->applySortingCalled);
    }

    /**
     * Test repository is iterable
     *
     * @return void
     */
    public function testRepositoryIsIterable(): void
    {
        $iterator = $this->repository->getIterator();

        $this->assertNotNull($iterator);
    }

    /**
     * Test interface contract is implemented
     *
     * @return void
     */
    public function testImplementsRepositoryInterface(): void
    {
        $this->assertInstanceOf(RepositoryInterface::class, $this->repository);
    }
}

/**
 * TestEntity - Concrete entity for testing
 *
 * Implements minimal query builder interface to support repository testing
 *
 * @package WaysNX\BusinessFramework\Tests\Unit\Repositories
 */
class TestEntity extends BaseModel
{
    /**
     * Query result for get()
     *
     * @var array
     */
    private array $getResult = [];

    /**
     * Query result for count()
     *
     * @var int
     */
    private int $countResult = 0;

    /**
     * Set data for query operations
     *
     * @param array $data
     * @return self
     */
    public function setQueryData(array $data = []): self
    {
        $this->getResult = $data;
        $this->countResult = count($data);
        return $this;
    }

    /**
     * Mock get() - returns query results
     *
     * @return array Query results
     */
    public function get(): array
    {
        return $this->getResult;
    }

    /**
     * Mock count() - returns result count
     *
     * @return int Count of results
     */
    public function count(): int
    {
        return $this->countResult;
    }

    /**
     * Mock offset() - chainable for pagination
     *
     * @param int $offset
     * @return self
     */
    public function offset(int $offset): self
    {
        return $this;
    }

    /**
     * Mock limit() - chainable for pagination
     *
     * @param int $limit
     * @return self
     */
    public function limit(int $limit): self
    {
        return $this;
    }
}

/**
 * TestRepository - Test repository with hook tracking
 *
 * @package WaysNX\BusinessFramework\Tests\Unit\Repositories
 */
class TestRepository extends BaseRepository
{
    /**
     * Test entity for find operations
     *
     * @var TestEntity|null
     */
    private ?TestEntity $testEntity = null;

    /**
     * Track if beforeCreate was called
     *
     * @var bool
     */
    public bool $beforeCreateCalled = false;

    /**
     * Track if afterCreate was called
     *
     * @var bool
     */
    public bool $afterCreateCalled = false;

    /**
     * Track if beforeUpdate was called
     *
     * @var bool
     */
    public bool $beforeUpdateCalled = false;

    /**
     * Track if afterUpdate was called
     *
     * @var bool
     */
    public bool $afterUpdateCalled = false;

    /**
     * Track if beforeDelete was called
     *
     * @var bool
     */
    public bool $beforeDeleteCalled = false;

    /**
     * Track if afterDelete was called
     *
     * @var bool
     */
    public bool $afterDeleteCalled = false;

    /**
     * Track if beforeRestore was called
     *
     * @var bool
     */
    public bool $beforeRestoreCalled = false;

    /**
     * Track if afterRestore was called
     *
     * @var bool
     */
    public bool $afterRestoreCalled = false;

    /**
     * Track if applyFilters was called
     *
     * @var bool
     */
    public bool $applyFiltersCalled = false;

    /**
     * Track if applySorting was called
     *
     * @var bool
     */
    public bool $applySortingCalled = false;

    /**
     * Set a test entity for find operations
     *
     * @param TestEntity $entity The test entity
     *
     * @return void
     */
    public function setTestEntity(TestEntity $entity): void
    {
        $this->testEntity = $entity;
    }

    /**
     * Override find to use test entity
     *
     * @param string|int $id The entity ID
     *
     * @return mixed|null
     */
    public function find(string|int $id): mixed
    {
        if ($id === 'test-id' && $this->testEntity !== null) {
            return $this->testEntity;
        }
        return null;
    }

    /**
     * Override beforeCreate hook
     *
     * @param array &$data The data array
     *
     * @return void
     */
    protected function beforeCreate(array &$data): void
    {
        $this->beforeCreateCalled = true;
    }

    /**
     * Override afterCreate hook
     *
     * @param mixed $entity The created entity
     *
     * @return void
     */
    protected function afterCreate(mixed $entity): void
    {
        $this->afterCreateCalled = true;
    }

    /**
     * Override beforeUpdate hook
     *
     * @param mixed $entity The entity
     * @param array &$data The data
     *
     * @return void
     */
    protected function beforeUpdate(mixed $entity, array &$data): void
    {
        $this->beforeUpdateCalled = true;
    }

    /**
     * Override afterUpdate hook
     *
     * @param mixed $entity The entity
     *
     * @return void
     */
    protected function afterUpdate(mixed $entity): void
    {
        $this->afterUpdateCalled = true;
    }

    /**
     * Override beforeDelete hook
     *
     * @param mixed $entity The entity
     *
     * @return void
     */
    protected function beforeDelete(mixed $entity): void
    {
        $this->beforeDeleteCalled = true;
    }

    /**
     * Override afterDelete hook
     *
     * @param mixed $entity The entity
     *
     * @return void
     */
    protected function afterDelete(mixed $entity): void
    {
        $this->afterDeleteCalled = true;
    }

    /**
     * Override beforeRestore hook
     *
     * @param mixed $entity The entity
     *
     * @return void
     */
    protected function beforeRestore(mixed $entity): void
    {
        $this->beforeRestoreCalled = true;
    }

    /**
     * Override afterRestore hook
     *
     * @param mixed $entity The entity
     *
     * @return void
     */
    protected function afterRestore(mixed $entity): void
    {
        $this->afterRestoreCalled = true;
    }

    /**
     * Override applyFilters hook
     *
     * @param array $filters The filters
     *
     * @return void
     */
    protected function applyFilters(array $filters): void
    {
        $this->applyFiltersCalled = true;
    }

    /**
     * Override applySorting hook
     *
     * @param array $sortParams The sort parameters
     *
     * @return void
     */
    protected function applySorting(array $sortParams): void
    {
        $this->applySortingCalled = true;
    }
}
