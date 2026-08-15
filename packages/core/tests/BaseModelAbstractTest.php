<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Core\Tests;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Core\BaseModelAbstract;

/**
 * ConcreteTestModel
 *
 * Concrete implementation of BaseModelAbstract for testing purposes.
 * Implements the abstract methods required by BaseModelAbstract.
 */
class ConcreteTestModel extends BaseModelAbstract
{
    /**
     * Generate a new unique identifier
     */
    protected function generateUuid(): string|int
    {
        // Simple UUID-like string for testing
        static $counter = 0;
        return 'test-uuid-' . (++$counter);
    }

    /**
     * Initialize audit timestamps
     */
    protected function initializeAuditTimestamps(): void
    {
        $this->createdAt = new \DateTime('2024-01-01 12:00:00');
    }

    /**
     * Update the last modification timestamp
     */
    protected function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTime('2024-01-01 13:00:00');
    }

    /**
     * Update the deletion timestamp
     */
    protected function deleteTimestamp(): void
    {
        $this->deletedAt = new \DateTime('2024-01-01 14:00:00');
    }

    /**
     * Convert the entity to an array representation
     */
    public function toArray(): array
    {
        return [
            'entity_id' => $this->getEntityId(),
            'entity_type' => $this->getEntityType(),
            'entity_version' => $this->getEntityVersion(),
            'created_by' => $this->getCreatedBy(),
            'created_at' => $this->getCreatedAt(),
            'updated_by' => $this->getUpdatedBy(),
            'updated_at' => $this->getUpdatedAt(),
            'deleted_by' => $this->getDeletedBy(),
            'deleted_at' => $this->getDeletedAt(),
            'is_deleted' => $this->isDeleted(),
            'metadata' => $this->getMetadata(),
        ];
    }

    /**
     * Convert the entity to JSON representation
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}

/**
 * BaseModelAbstractTest
 *
 * Tests for the framework-independent BaseModelAbstract.
 *
 * Tests the core functionality that should work across all framework implementations:
 * - Entity identification
 * - Entity type
 * - Versioning
 * - Metadata management
 * - Audit tracking
 * - Serialization
 * - Lifecycle hooks
 *
 * @package WaysNX\BusinessFramework\Core\Tests
 */
class BaseModelAbstractTest extends TestCase
{
    /**
     * Test that a model can be created
     */
    public function testModelCanBeCreated(): void
    {
        $model = new ConcreteTestModel();

        $this->assertInstanceOf(ConcreteTestModel::class, $model);
    }

    /**
     * Test that identity is available
     */
    public function testIdentityIsAvailable(): void
    {
        $model = new ConcreteTestModel();

        $this->assertNotEmpty($model->getEntityId());
        $this->assertIsString($model->getEntityId());
        $this->assertStringContainsString('test-uuid-', $model->getEntityId());
    }

    /**
     * Test that model type is available
     */
    public function testModelTypeIsAvailable(): void
    {
        $model = new ConcreteTestModel();

        $type = $model->getEntityType();

        $this->assertNotEmpty($type);
        $this->assertStringContainsString('ConcreteTestModel', $type);
    }

    /**
     * Test that version is available and starts at 1
     */
    public function testVersionIsAvailableAndStartsAtOne(): void
    {
        $model = new ConcreteTestModel();

        $this->assertEquals(1, $model->getEntityVersion());
    }

    /**
     * Test that metadata can be stored
     */
    public function testMetadataCanBeStored(): void
    {
        $model = new ConcreteTestModel();

        $model->setMetadataValue('key1', 'value1');
        $model->setMetadataValue('key2', ['nested' => 'value']);

        $this->assertTrue($model->hasMetadata('key1'));
        $this->assertTrue($model->hasMetadata('key2'));
    }

    /**
     * Test that metadata can be retrieved
     */
    public function testMetadataCanBeRetrieved(): void
    {
        $model = new ConcreteTestModel();

        $model->setMetadataValue('key1', 'value1');

        $this->assertEquals('value1', $model->getMetadataValue('key1'));
    }

    /**
     * Test that metadata retrieval returns default value when key doesn't exist
     */
    public function testMetadataReturnsDefaultValue(): void
    {
        $model = new ConcreteTestModel();

        $value = $model->getMetadataValue('non-existent', 'default');

        $this->assertEquals('default', $value);
    }

    /**
     * Test that metadata can be removed
     */
    public function testMetadataCanBeRemoved(): void
    {
        $model = new ConcreteTestModel();

        $model->setMetadataValue('key1', 'value1');
        $this->assertTrue($model->hasMetadata('key1'));

        $model->removeMetadata('key1');
        $this->assertFalse($model->hasMetadata('key1'));
    }

    /**
     * Test that all metadata can be cleared
     */
    public function testAllMetadataCanBeCleared(): void
    {
        $model = new ConcreteTestModel();

        $model->setMetadataValue('key1', 'value1');
        $model->setMetadataValue('key2', 'value2');

        $this->assertCount(2, $model->getMetadata());

        $model->clearMetadata();

        $this->assertCount(0, $model->getMetadata());
    }

    /**
     * Test that serialization to array works
     */
    public function testSerializationToArrayWorks(): void
    {
        $model = new ConcreteTestModel();
        $model->setCreatedBy('user-1');
        $model->setMetadataValue('key', 'value');

        $array = $model->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('entity_id', $array);
        $this->assertArrayHasKey('entity_type', $array);
        $this->assertArrayHasKey('entity_version', $array);
        $this->assertArrayHasKey('created_by', $array);
        $this->assertArrayHasKey('metadata', $array);
    }

    /**
     * Test that serialization to JSON works
     */
    public function testSerializationToJsonWorks(): void
    {
        $model = new ConcreteTestModel();

        $json = $model->toJson();

        $this->assertIsString($json);
        $this->assertStringContainsString('entity_id', $json);
        $this->assertStringContainsString('entity_type', $json);
    }

    /**
     * Test that lifecycle hooks are available
     */
    public function testLifecycleHooksAreAvailable(): void
    {
        $model = new ConcreteTestModel();

        // These should not throw - they're no-ops in the base class
        $this->assertNull($model->beforeCreate ?? null);
        $this->assertNull($model->afterCreate ?? null);
        $this->assertNull($model->beforeUpdate ?? null);
        $this->assertNull($model->afterUpdate ?? null);
    }

    /**
     * Test that audit information can be tracked
     */
    public function testAuditInformationCanBeTracked(): void
    {
        $model = new ConcreteTestModel();

        $model->setCreatedBy('user-1');
        $model->setUpdatedBy('user-2');
        $model->setDeletedBy('user-3');

        $this->assertEquals('user-1', $model->getCreatedBy());
        $this->assertEquals('user-2', $model->getUpdatedBy());
        $this->assertEquals('user-3', $model->getDeletedBy());
    }

    /**
     * Test that entity can be marked as deleted
     */
    public function testEntityCanBeMarkedAsDeleted(): void
    {
        $model = new ConcreteTestModel();

        $this->assertFalse($model->isDeleted());

        $model->setDeletedBy('user-1');

        $this->assertTrue($model->isDeleted());
    }

    /**
     * Test that soft-deleted entity can be restored
     */
    public function testSoftDeletedEntityCanBeRestored(): void
    {
        $model = new ConcreteTestModel();

        $model->setDeletedBy('user-1');
        $this->assertTrue($model->isDeleted());

        $model->restore();

        $this->assertFalse($model->isDeleted());
        $this->assertNull($model->getDeletedBy());
        $this->assertNull($model->getDeletedAt());
    }

    /**
     * Test that version can be incremented
     */
    public function testVersionCanBeIncremented(): void
    {
        $model = new ConcreteTestModel();

        $this->assertEquals(1, $model->getEntityVersion());

        // Use reflection to access the protected method
        $reflection = new \ReflectionMethod($model, 'incrementVersion');
        $reflection->setAccessible(true);
        $newVersion = $reflection->invoke($model);

        $this->assertEquals(2, $newVersion);
        $this->assertEquals(2, $model->getEntityVersion());
    }

    /**
     * Test that version can be reset
     */
    public function testVersionCanBeReset(): void
    {
        $model = new ConcreteTestModel();

        // Use reflection to set a higher version
        $reflection = new \ReflectionMethod($model, 'setEntityVersion');
        $reflection->setAccessible(true);
        $reflection->invoke($model, 5);

        $this->assertEquals(5, $model->getEntityVersion());

        // Now reset
        $resetMethod = new \ReflectionMethod($model, 'resetVersion');
        $resetMethod->setAccessible(true);
        $resetMethod->invoke($model);

        $this->assertEquals(1, $model->getEntityVersion());
    }

    /**
     * Test that timestamps are set during initialization
     */
    public function testTimestampsAreSetDuringInitialization(): void
    {
        $model = new ConcreteTestModel();

        $createdAt = $model->getCreatedAt();

        $this->assertNotNull($createdAt);
    }

    /**
     * Test that update timestamp is set when model is updated
     */
    public function testUpdateTimestampIsSetWhenModelIsUpdated(): void
    {
        $model = new ConcreteTestModel();

        $this->assertNull($model->getUpdatedAt());

        $model->setUpdatedBy('user-1');

        $this->assertNotNull($model->getUpdatedAt());
    }

    /**
     * Test that delete timestamp is set when model is deleted
     */
    public function testDeleteTimestampIsSetWhenModelIsDeleted(): void
    {
        $model = new ConcreteTestModel();

        $this->assertNull($model->getDeletedAt());

        $model->setDeletedBy('user-1');

        $this->assertNotNull($model->getDeletedAt());
    }

    /**
     * Test that multiple metadata values can be retrieved as array
     */
    public function testMultipleMetadataValuesCanBeRetrievedAsArray(): void
    {
        $model = new ConcreteTestModel();

        $model->setMetadataValue('key1', 'value1');
        $model->setMetadataValue('key2', 'value2');
        $model->setMetadataValue('key3', ['nested' => 'value']);

        $metadata = $model->getMetadata();

        $this->assertCount(3, $metadata);
        $this->assertEquals('value1', $metadata['key1']);
        $this->assertEquals('value2', $metadata['key2']);
        $this->assertIsArray($metadata['key3']);
    }

    /**
     * Test that model implements EntityInterface
     */
    public function testModelImplementsEntityInterface(): void
    {
        $model = new ConcreteTestModel();

        $this->assertInstanceOf(\WaysNX\BusinessFramework\Types\EntityInterface::class, $model);
    }

    /**
     * Test that model implements AuditableInterface
     */
    public function testModelImplementsAuditableInterface(): void
    {
        $model = new ConcreteTestModel();

        $this->assertInstanceOf(\WaysNX\BusinessFramework\Types\AuditableInterface::class, $model);
    }

    /**
     * Test that model implements MetadataInterface
     */
    public function testModelImplementsMetadataInterface(): void
    {
        $model = new ConcreteTestModel();

        $this->assertInstanceOf(\WaysNX\BusinessFramework\Types\MetadataInterface::class, $model);
    }

    /**
     * Test that serialized array includes all required keys
     */
    public function testSerializedArrayIncludesAllRequiredKeys(): void
    {
        $model = new ConcreteTestModel();
        $model->setCreatedBy('user-1');
        $model->setMetadataValue('test', 'value');

        $array = $model->toArray();

        $requiredKeys = [
            'entity_id',
            'entity_type',
            'entity_version',
            'created_by',
            'created_at',
            'updated_by',
            'updated_at',
            'deleted_by',
            'deleted_at',
            'is_deleted',
            'metadata',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $array, "Missing key: $key");
        }
    }

    /**
     * Test that model type is set to class name
     */
    public function testModelTypeIsSetToClassName(): void
    {
        $model = new ConcreteTestModel();

        $type = $model->getEntityType();

        $this->assertStringEndsWith('ConcreteTestModel', $type);
    }

    /**
     * Test that different model instances have different IDs
     */
    public function testDifferentModelInstancesHaveDifferentIds(): void
    {
        $model1 = new ConcreteTestModel();
        $model2 = new ConcreteTestModel();

        $this->assertNotEquals($model1->getEntityId(), $model2->getEntityId());
    }
}
