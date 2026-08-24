<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Contracts\RepositoryInterface;
use WaysNX\BusinessFramework\Contracts\ServiceInterface;
use WaysNX\BusinessFramework\Exceptions\EntityNotFoundException;
use WaysNX\BusinessFramework\Models\BaseModel;
use WaysNX\BusinessFramework\Repositories\BaseRepository;
use WaysNX\BusinessFramework\Services\BaseService;

/**
 * BaseServiceTest
 *
 * PHPUnit test suite for BaseService functionality.
 *
 * Tests cover:
 * - Service initialization
 * - CRUD delegation to repository
 * - Lifecycle hook execution
 * - Validation hook execution
 * - Repository interaction
 * - Exception propagation
 * - Repository swapping
 *
 * @package WaysNX\BusinessFramework\Tests\Unit\Services
 */
class BaseServiceTest extends TestCase
{
    /**
     * Test service instance
     *
     * @var TestService
     */
    private TestService $service;

    /**
     * Mock repository
     *
     * @var MockRepository
     */
    private MockRepository $repository;

    /**
     * Set up test fixtures
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->repository = new MockRepository(new TestEntity());
        $this->service = new TestService($this->repository);
    }

    /**
     * Test service instantiation with repository
     *
     * @return void
     */
    public function testServiceInstantiationWithRepository(): void
    {
        $this->assertInstanceOf(ServiceInterface::class, $this->service);
        $this->assertSame($this->repository, $this->service->getRepository());
    }

    /**
     * Test service implements ServiceInterface
     *
     * @return void
     */
    public function testServiceImplementsServiceInterface(): void
    {
        $this->assertInstanceOf(ServiceInterface::class, $this->service);
    }

    /**
     * Test repository can be set after instantiation
     *
     * @return void
     */
    public function testRepositoryCanBeSetAfterInstantiation(): void
    {
        $newRepository = new MockRepository(new TestEntity());
        $this->service->setRepository($newRepository);

        $this->assertSame($newRepository, $this->service->getRepository());
    }

    /**
     * Test setRepository returns self for method chaining
     *
     * @return void
     */
    public function testSetRepositoryReturnsSelfForChaining(): void
    {
        $newRepository = new MockRepository(new TestEntity());
        $result = $this->service->setRepository($newRepository);

        $this->assertSame($this->service, $result);
    }

    /**
     * Test create() delegates to repository
     *
     * @return void
     */
    public function testCreateDelegatesToRepository(): void
    {
        $data = ['name' => 'Test Entity'];
        $entity = $this->service->create($data);

        $this->assertNotNull($entity);
        $this->assertTrue($this->repository->createCalled);
    }

    /**
     * Test create() calls validateCreate hook
     *
     * @return void
     */
    public function testCreateCallsValidateCreateHook(): void
    {
        $this->service->create(['name' => 'Test']);

        $this->assertTrue($this->service->validateCreateCalled);
    }

    /**
     * Test create() calls beforeCreate hook
     *

     * @return void
     */
    public function testCreateCallsBeforeCreateHook(): void
    {
        $this->service->create(['name' => 'Test']);

        $this->assertTrue($this->service->beforeCreateCalled);
    }

    /**
     * Test create() calls afterCreate hook
     *

     * @return void
     */
    public function testCreateCallsAfterCreateHook(): void
    {
        $this->service->create(['name' => 'Test']);

        $this->assertTrue($this->service->afterCreateCalled);
    }

    /**
     * Test create() hook execution order
     *

     * @return void
     */
    public function testCreateHookExecutionOrder(): void
    {
        $this->service->create(['name' => 'Test']);

        // Validate should be called first
        $this->assertLessThan(
            $this->service->beforeCreateOrder,
            $this->service->validateCreateOrder
        );

        // Before should be called before after
        $this->assertLessThan(
            $this->service->afterCreateOrder,
            $this->service->beforeCreateOrder
        );
    }

    /**
     * Test update() delegates to repository
     *

     * @return void
     */
    public function testUpdateDelegatesToRepository(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $entity = $this->service->update('test-id', ['name' => 'Updated']);

        $this->assertNotNull($entity);
        $this->assertTrue($this->repository->updateCalled);
    }

    /**
     * Test update() throws exception if entity not found
     *

     * @return void
     */
    public function testUpdateThrowsExceptionIfNotFound(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->service->update('non-existent', ['name' => 'Updated']);
    }

    /**
     * Test update() calls validateUpdate hook
     *

     * @return void
     */
    public function testUpdateCallsValidateUpdateHook(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $this->service->update('test-id', ['name' => 'Updated']);

        $this->assertTrue($this->service->validateUpdateCalled);
    }

    /**
     * Test update() calls beforeUpdate hook
     *

     * @return void
     */
    public function testUpdateCallsBeforeUpdateHook(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $this->service->update('test-id', ['name' => 'Updated']);

        $this->assertTrue($this->service->beforeUpdateCalled);
    }

    /**
     * Test update() calls afterUpdate hook
     *

     * @return void
     */
    public function testUpdateCallsAfterUpdateHook(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $this->service->update('test-id', ['name' => 'Updated']);

        $this->assertTrue($this->service->afterUpdateCalled);
    }

    /**
     * Test delete() delegates to repository
     *

     * @return void
     */
    public function testDeleteDelegatesToRepository(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $result = $this->service->delete('test-id');

        $this->assertTrue($result);
        $this->assertTrue($this->repository->deleteCalled);
    }

    /**
     * Test delete() throws exception if entity not found
     *

     * @return void
     */
    public function testDeleteThrowsExceptionIfNotFound(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->service->delete('non-existent');
    }

    /**
     * Test delete() calls beforeDelete hook
     *

     * @return void
     */
    public function testDeleteCallsBeforeDeleteHook(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $this->service->delete('test-id');

        $this->assertTrue($this->service->beforeDeleteCalled);
    }

    /**
     * Test delete() calls afterDelete hook
     *

     * @return void
     */
    public function testDeleteCallsAfterDeleteHook(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $this->service->delete('test-id');

        $this->assertTrue($this->service->afterDeleteCalled);
    }

    /**
     * Test restore() delegates to repository
     *

     * @return void
     */
    public function testRestoreDelegatesToRepository(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $result = $this->service->restore('test-id');

        $this->assertTrue($result);
        $this->assertTrue($this->repository->restoreCalled);
    }

    /**
     * Test restore() throws exception if entity not found
     *

     * @return void
     */
    public function testRestoreThrowsExceptionIfNotFound(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->service->restore('non-existent');
    }

    /**
     * Test restore() calls beforeRestore hook
     *

     * @return void
     */
    public function testRestoreCallsBeforeRestoreHook(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $this->service->restore('test-id');

        $this->assertTrue($this->service->beforeRestoreCalled);
    }

    /**
     * Test restore() calls afterRestore hook
     *

     * @return void
     */
    public function testRestoreCallsAfterRestoreHook(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $this->service->restore('test-id');

        $this->assertTrue($this->service->afterRestoreCalled);
    }

    /**
     * Test find() delegates to repository
     *

     * @return void
     */
    public function testFindDelegatesToRepository(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $entity = $this->service->find('test-id');

        $this->assertNotNull($entity);
    }

    /**
     * Test findOrFail() delegates to repository
     *

     * @return void
     */
    public function testFindOrFailDelegatesToRepository(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $entity = $this->service->findOrFail('test-id');

        $this->assertNotNull($entity);
    }

    /**
     * Test findOrFail() throws exception if not found
     *

     * @return void
     */
    public function testFindOrFailThrowsExceptionIfNotFound(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->service->findOrFail('non-existent');
    }

    /**
     * Test all() delegates to repository
     *

     * @return void
     */
    public function testAllDelegatesToRepository(): void
    {
        $entities = $this->service->all();

        $this->assertIsArray($entities);
    }

    /**
     * Test paginate() delegates to repository
     *

     * @return void
     */
    public function testPaginateDelegatesToRepository(): void
    {
        $result = $this->service->paginate(15, 1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
    }

    /**
     * Test exists() delegates to repository
     *

     * @return void
     */
    public function testExistsDelegatesToRepository(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $exists = $this->service->exists('test-id');

        $this->assertTrue($exists);
    }

    /**
     * Test count() delegates to repository
     *

     * @return void
     */
    public function testCountDelegatesToRepository(): void
    {
        $count = $this->service->count();

        $this->assertIsInt($count);
    }

    /**
     * Test beforeCreate data modification
     *

     * @return void
     */
    public function testBeforeCreateCanModifyData(): void
    {
        $this->service->modifyDataInBeforeCreate = true;
        $this->service->create(['name' => 'Test']);

        // Repository should receive modified data
        $this->assertTrue($this->repository->lastCreateDataWasModified);
    }

    /**
     * Test validateCreate throws exception
     *

     * @return void
     */
    public function testValidateCreateThrowsException(): void
    {
        $this->service->shouldValidateCreateFail = true;

        $this->expectException(\Exception::class);
        $this->service->create(['name' => 'Test']);
    }

    /**
     * Test validateUpdate throws exception
     *

     * @return void
     */
    public function testValidateUpdateThrowsException(): void
    {
        $this->repository->setTestEntity(new TestEntity());
        $this->service->shouldValidateUpdateFail = true;

        $this->expectException(\Exception::class);
        $this->service->update('test-id', ['name' => 'Updated']);
    }

    /**
     * Test transaction support
     *

     * @return void
     */
    public function testTransactionSupport(): void
    {
        // The transaction method is protected on BaseService
        // We test it through MockRepository which has a public wrapper
        $result = $this->repository->publicTransaction(function () {
            return 'result';
        });

        $this->assertEquals('result', $result);
    }
}

/**
 * TestEntity - Concrete entity for testing
 *
 * Implements minimal query builder interface to support service testing
 *
 * @package WaysNX\BusinessFramework\Tests\Unit\Services
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
 * MockRepository - Mock repository for testing services
 *

 * @package WaysNX\BusinessFramework\Tests\Unit\Services
 */
class MockRepository extends BaseRepository
{
    /**
     * Test entity for find operations
     *

     * @var TestEntity|null
     */
    private ?TestEntity $testEntity = null;

    /**
     * Track if create was called
     *

     * @var bool
     */
    public bool $createCalled = false;

    /**
     * Track if update was called
     *

     * @var bool
     */
    public bool $updateCalled = false;

    /**
     * Track if delete was called
     *

     * @var bool
     */
    public bool $deleteCalled = false;

    /**
     * Track if restore was called
     *

     * @var bool
     */
    public bool $restoreCalled = false;

    /**
     * Track if create data was modified
     *

     * @var bool
     */
    public bool $lastCreateDataWasModified = false;

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
     * Override create to track calls
     *

     * @param array $data The data
     *

     * @return mixed
     */
    public function create(array $data): mixed
    {
        $this->createCalled = true;
        $this->lastCreateDataWasModified = isset($data['modified']);
        return parent::create($data);
    }

    /**
     * Override update to track calls
     *

     * @param string|int $id The entity ID
     *

     * @param array $data The data
     *

     * @return bool
     */
    public function update(string|int $id, array $data): bool
    {
        $this->updateCalled = true;
        return parent::update($id, $data);
    }

    /**
     * Override delete to track calls
     *

     * @param string|int $id The entity ID
     *

     * @return bool
     */
    public function delete(string|int $id): bool
    {
        $this->deleteCalled = true;
        return parent::delete($id);
    }

    /**
     * Override restore to track calls
     *

     * @param string|int $id The entity ID
     *

     * @return bool
     */
    public function restore(string|int $id): bool
    {
        $this->restoreCalled = true;
        return parent::restore($id);
    }

    /**
     * Public transaction method for testing
     *
     * @param callable $callback
     * @return mixed
     */
    public function publicTransaction(callable $callback): mixed
    {
        return $this->transaction($callback);
    }
}

/**
 * TestService - Test service with hook tracking
 *

 * @package WaysNX\BusinessFramework\Tests\Unit\Services
 */
class TestService extends BaseService
{
    /**
     * Track if validateCreate was called
     *

     * @var bool
     */
    public bool $validateCreateCalled = false;

    /**
     * Track if validateUpdate was called
     *

     * @var bool
     */
    public bool $validateUpdateCalled = false;

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
     * Order of hook calls
     *

     * @var int
     */
    public int $validateCreateOrder = 0;

    /**
     * Order of hook calls
     *

     * @var int
     */
    public int $beforeCreateOrder = 0;

    /**
     * Order of hook calls
     *

     * @var int
     */
    public int $afterCreateOrder = 0;

    /**
     * Modify data in before hook
     *

     * @var bool
     */
    public bool $modifyDataInBeforeCreate = false;

    /**
     * Should validation fail
     *

     * @var bool
     */
    public bool $shouldValidateCreateFail = false;

    /**
     * Should validation fail
     *

     * @var bool
     */
    public bool $shouldValidateUpdateFail = false;

    /**
     * Counter for hook order tracking
     *

     * @var int
     */
    private int $hookCounter = 0;

    /**
     * Override validateCreate hook
     *

     * @param array &$data The data
     *

     * @return void
     */
    protected function validateCreate(array &$data): void
    {
        $this->validateCreateCalled = true;
        $this->validateCreateOrder = ++$this->hookCounter;

        if ($this->shouldValidateCreateFail) {
            throw new \Exception('Validation failed');
        }
    }

    /**
     * Override validateUpdate hook
     *

     * @param array &$data The data
     *

     * @return void
     */
    protected function validateUpdate(array &$data): void
    {
        $this->validateUpdateCalled = true;

        if ($this->shouldValidateUpdateFail) {
            throw new \Exception('Validation failed');
        }
    }

    /**
     * Override beforeCreate hook
     *

     * @param array &$data The data
     *

     * @return void
     */
    protected function beforeCreate(array &$data): void
    {
        $this->beforeCreateCalled = true;
        $this->beforeCreateOrder = ++$this->hookCounter;

        if ($this->modifyDataInBeforeCreate) {
            $data['modified'] = true;
        }
    }

    /**
     * Override afterCreate hook
     *

     * @param mixed $entity The entity
     *

     * @return void
     */
    protected function afterCreate(mixed $entity): void
    {
        $this->afterCreateCalled = true;
        $this->afterCreateOrder = ++$this->hookCounter;
    }

    /**
     * Override beforeUpdate hook
     *

     * @param string|int $id The entity ID
     *

     * @param array &$data The data
     *

     * @return void
     */
    protected function beforeUpdate(string|int $id, array &$data): void
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

     * @param string|int $id The entity ID
     *

     * @return void
     */
    protected function beforeDelete(string|int $id): void
    {
        $this->beforeDeleteCalled = true;
    }

    /**
     * Override afterDelete hook
     *

     * @param string|int $id The entity ID
     *

     * @return void
     */
    protected function afterDelete(string|int $id): void
    {
        $this->afterDeleteCalled = true;
    }

    /**
     * Override beforeRestore hook
     *

     * @param string|int $id The entity ID
     *

     * @return void
     */
    protected function beforeRestore(string|int $id): void
    {
        $this->beforeRestoreCalled = true;
    }

    /**
     * Override afterRestore hook
     *

     * @param string|int $id The entity ID
     *

     * @return void
     */
    protected function afterRestore(string|int $id): void
    {
        $this->afterRestoreCalled = true;
    }

    /**
     * Make transaction public for testing
     *

     * @param callable $callback The callback
     *

     * @return mixed
     */
    public function transaction(callable $callback): mixed
    {
        return parent::transaction($callback);
    }
}
