<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Unit\Models;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Contracts\AuditableInterface;
use WaysNX\BusinessFramework\Contracts\EntityInterface;
use WaysNX\BusinessFramework\Contracts\MetadataInterface;
use WaysNX\BusinessFramework\Models\BaseModel;

/**
 * BaseModelTest
 *
 * PHPUnit test suite for BaseModel functionality.
 *
 * Tests cover:
 * - Entity instantiation and initialization
 * - Identifier management
 * - Versioning
 * - Metadata operations
 * - Audit tracking
 * - Lifecycle hooks
 * - Serialization
 *
 * @package WaysNX\BusinessFramework\Tests\Unit\Models
 */
class BaseModelTest extends TestCase
{
    /**
     * Concrete implementation of BaseModel for testing
     *
     * @var class-string
     */
    private string $testEntity = TestEntity::class;

    /**
     * Test BaseModel instantiation and initialization
     *
     * @return void
     */
    public function testEntityInstantiationInitializesProperties(): void
    {
        $entity = new $this->testEntity();

        $this->assertInstanceOf(EntityInterface::class, $entity);
        $this->assertInstanceOf(AuditableInterface::class, $entity);
        $this->assertInstanceOf(MetadataInterface::class, $entity);
        $this->assertNotNull($entity->getEntityId());
        $this->assertEquals(1, $entity->getEntityVersion());
        $this->assertInstanceOf(DateTimeImmutable::class, $entity->getCreatedAt());
    }

    /**
     * Test entity type is correctly set
     *
     * @return void
     */
    public function testEntityTypeIsSet(): void
    {
        $entity = new $this->testEntity();

        $this->assertEquals(TestEntity::class, $entity->getEntityType());
    }

    /**
     * Test entity ID is unique
     *
     * @return void
     */
    public function testEntityIdIsUnique(): void
    {
        $entity1 = new $this->testEntity();
        $entity2 = new $this->testEntity();

        $this->assertNotEquals($entity1->getEntityId(), $entity2->getEntityId());
    }

    /**
     * Test entity versioning
     *
     * @return void
     */
    public function testEntityVersioning(): void
    {
        $entity = new $this->testEntity();

        $this->assertEquals(1, $entity->getEntityVersion());

        $entity->incrementVersion();
        $this->assertEquals(2, $entity->getEntityVersion());

        $entity->incrementVersion();
        $this->assertEquals(3, $entity->getEntityVersion());
    }

    /**
     * Test metadata storage and retrieval
     *
     * @return void
     */
    public function testMetadataStorage(): void
    {
        $entity = new $this->testEntity();

        $entity->setMetadataValue('key1', 'value1');
        $entity->setMetadataValue('key2', 123);
        $entity->setMetadataValue('key3', ['nested' => 'array']);

        $this->assertEquals('value1', $entity->getMetadataValue('key1'));
        $this->assertEquals(123, $entity->getMetadataValue('key2'));
        $this->assertEquals(['nested' => 'array'], $entity->getMetadataValue('key3'));
    }

    /**
     * Test metadata default values
     *
     * @return void
     */
    public function testMetadataDefaultValue(): void
    {
        $entity = new $this->testEntity();

        $this->assertNull($entity->getMetadataValue('nonexistent'));
        $this->assertEquals('default', $entity->getMetadataValue('nonexistent', 'default'));
    }

    /**
     * Test metadata existence checking
     *
     * @return void
     */
    public function testMetadataExistenceChecking(): void
    {
        $entity = new $this->testEntity();

        $this->assertFalse($entity->hasMetadata('key1'));

        $entity->setMetadataValue('key1', 'value1');
        $this->assertTrue($entity->hasMetadata('key1'));
    }

    /**
     * Test metadata removal
     *
     * @return void
     */
    public function testMetadataRemoval(): void
    {
        $entity = new $this->testEntity();

        $entity->setMetadataValue('key1', 'value1');
        $this->assertTrue($entity->hasMetadata('key1'));

        $entity->removeMetadata('key1');
        $this->assertFalse($entity->hasMetadata('key1'));
    }

    /**
     * Test metadata clearing
     *
     * @return void
     */
    public function testMetadataClear(): void
    {
        $entity = new $this->testEntity();

        $entity->setMetadataValue('key1', 'value1');
        $entity->setMetadataValue('key2', 'value2');
        $this->assertEquals(2, count($entity->getMetadata()));

        $entity->clearMetadata();
        $this->assertEquals(0, count($entity->getMetadata()));
    }

    /**
     * Test audit tracking - created by
     *
     * @return void
     */
    public function testAuditTrackingCreatedBy(): void
    {
        $entity = new $this->testEntity();

        $this->assertNull($entity->getCreatedBy());

        $entity->setCreatedBy('user-123');
        $this->assertEquals('user-123', $entity->getCreatedBy());
    }

    /**
     * Test audit tracking - updated by
     *
     * @return void
     */
    public function testAuditTrackingUpdatedBy(): void
    {
        $entity = new $this->testEntity();

        $this->assertNull($entity->getUpdatedBy());
        $this->assertNull($entity->getUpdatedAt());

        $entity->setUpdatedBy('user-456');
        $this->assertEquals('user-456', $entity->getUpdatedBy());
        $this->assertInstanceOf(DateTimeImmutable::class, $entity->getUpdatedAt());
    }

    /**
     * Test soft delete functionality
     *
     * @return void
     */
    public function testSoftDelete(): void
    {
        $entity = new $this->testEntity();

        $this->assertFalse($entity->isDeleted());
        $this->assertNull($entity->getDeletedBy());
        $this->assertNull($entity->getDeletedAt());

        $entity->setDeletedBy('user-789');
        $this->assertTrue($entity->isDeleted());
        $this->assertEquals('user-789', $entity->getDeletedBy());
        $this->assertInstanceOf(DateTimeImmutable::class, $entity->getDeletedAt());
    }

    /**
     * Test soft delete restoration
     *
     * @return void
     */
    public function testSoftDeleteRestoration(): void
    {
        $entity = new $this->testEntity();

        $entity->setDeletedBy('user-789');
        $this->assertTrue($entity->isDeleted());

        $entity->restore();
        $this->assertFalse($entity->isDeleted());
        $this->assertNull($entity->getDeletedBy());
        $this->assertNull($entity->getDeletedAt());
    }

    /**
     * Test entity serialization to array
     *
     * @return void
     */
    public function testSerializationToArray(): void
    {
        $entity = new $this->testEntity();
        $entity->setCreatedBy('user-123');
        $entity->setMetadataValue('key', 'value');

        $array = $entity->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('entity_id', $array);
        $this->assertArrayHasKey('entity_type', $array);
        $this->assertArrayHasKey('entity_version', $array);
        $this->assertArrayHasKey('created_by', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_by', $array);
        $this->assertArrayHasKey('updated_at', $array);
        $this->assertArrayHasKey('deleted_by', $array);
        $this->assertArrayHasKey('deleted_at', $array);
        $this->assertArrayHasKey('is_deleted', $array);
        $this->assertArrayHasKey('metadata', $array);

        $this->assertEquals('user-123', $array['created_by']);
        $this->assertEquals('value', $array['metadata']['key']);
    }

    /**
     * Test entity serialization to JSON
     *
     * @return void
     */
    public function testSerializationToJson(): void
    {
        $entity = new $this->testEntity();
        $entity->setCreatedBy('user-123');

        $json = $entity->toJson();

        $this->assertIsString($json);
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded);
        $this->assertEquals('user-123', $decoded['created_by']);
    }

    /**
     * Test JSON serializable interface
     *
     * @return void
     */
    public function testJsonSerializable(): void
    {
        $entity = new $this->testEntity();
        $entity->setCreatedBy('user-123');

        $json = json_encode($entity, flags: JSON_THROW_ON_ERROR);
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        $this->assertEquals('user-123', $decoded['created_by']);
    }

    /**
     * Test string representation
     *
     * @return void
     */
    public function testStringRepresentation(): void
    {
        $entity = new $this->testEntity();
        $str = (string) $entity;

        $this->assertStringContainsString('TestEntity', $str);
        $this->assertStringContainsString('v1', $str);
        $this->assertStringContainsString('#', $str);
    }

    /**
     * Test string representation shows deletion state
     *
     * @return void
     */
    public function testStringRepresentationShowsDeletionState(): void
    {
        $entity = new $this->testEntity();
        $entity->setDeletedBy('user-789');

        $str = (string) $entity;
        $this->assertStringContainsString('DELETED', $str);
    }

    /**
     * Test lifecycle hook - beforeCreate
     *
     * @return void
     */
    public function testLifecycleHookBeforeCreate(): void
    {
        $entity = new TestEntityWithHooks();

        // Hook should be called during construction
        $this->assertTrue($entity->beforeCreateCalled);
    }

    /**
     * Test lifecycle hook - afterCreate
     *
     * @return void
     */
    public function testLifecycleHookAfterCreate(): void
    {
        $entity = new TestEntityWithHooks();

        // Hook should be called during construction
        $this->assertTrue($entity->afterCreateCalled);
    }

    /**
     * Test lifecycle hook - beforeValidate
     *
     * @return void
     */
    public function testLifecycleHookBeforeValidate(): void
    {
        $entity = new TestEntityWithHooks();
        $entity->executeValidation();

        $this->assertTrue($entity->beforeValidateCalled);
    }

    /**
     * Test lifecycle hook - afterValidate
     *
     * @return void
     */
    public function testLifecycleHookAfterValidate(): void
    {
        $entity = new TestEntityWithHooks();
        $entity->executeValidation();

        $this->assertTrue($entity->afterValidateCalled);
    }

    /**
     * Test all audit properties together
     *
     * @return void
     */
    public function testCompleteAuditTrail(): void
    {
        $entity = new $this->testEntity();

        $entity->setCreatedBy('user-create');
        sleep(1);
        $entity->setUpdatedBy('user-update');
        sleep(1);
        $entity->setDeletedBy('user-delete');

        $this->assertEquals('user-create', $entity->getCreatedBy());
        $this->assertEquals('user-update', $entity->getUpdatedBy());
        $this->assertEquals('user-delete', $entity->getDeletedBy());

        $createdAt = $entity->getCreatedAt();
        $updatedAt = $entity->getUpdatedAt();
        $deletedAt = $entity->getDeletedAt();

        $this->assertLessThan($updatedAt, $createdAt);
        $this->assertLessThan($deletedAt, $updatedAt);
    }

    /**
     * Test entity implements all required interfaces
     *
     * @return void
     */
    public function testEntityImplementsRequiredInterfaces(): void
    {
        $entity = new $this->testEntity();

        $this->assertInstanceOf(EntityInterface::class, $entity);
        $this->assertInstanceOf(AuditableInterface::class, $entity);
        $this->assertInstanceOf(MetadataInterface::class, $entity);
    }
}

/**
 * TestEntity - Concrete implementation for testing
 *
 * @package WaysNX\BusinessFramework\Tests\Unit\Models
 */
class TestEntity extends BaseModel
{
    // Concrete implementation for testing abstract class
}

/**
 * TestEntityWithHooks - Implementation with hooks for testing
 *
 * @package WaysNX\BusinessFramework\Tests\Unit\Models
 */
class TestEntityWithHooks extends BaseModel
{
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
     * Track if beforeValidate was called
     *
     * @var bool
     */
    public bool $beforeValidateCalled = false;

    /**
     * Track if afterValidate was called
     *
     * @var bool
     */
    public bool $afterValidateCalled = false;

    /**
     * Override beforeCreate hook
     *
     * @return void
     */
    protected function beforeCreate(): void
    {
        $this->beforeCreateCalled = true;
    }

    /**
     * Override afterCreate hook
     *
     * @return void
     */
    protected function afterCreate(): void
    {
        $this->afterCreateCalled = true;
    }

    /**
     * Override beforeValidate hook
     *
     * @return void
     */
    protected function beforeValidate(): void
    {
        $this->beforeValidateCalled = true;
    }

    /**
     * Override afterValidate hook
     *
     * @return void
     */
    protected function afterValidate(): void
    {
        $this->afterValidateCalled = true;
    }
}
