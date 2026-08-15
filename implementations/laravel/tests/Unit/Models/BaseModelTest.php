<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use WaysNX\BusinessFramework\Models\BaseModel;
use WaysNX\BusinessFramework\Contracts\EntityInterface;
use WaysNX\BusinessFramework\Contracts\AuditableInterface;
use WaysNX\BusinessFramework\Contracts\MetadataInterface;

/**
 * ConcreteTestModel
 *
 * Concrete implementation of BaseModel for testing.
 */
class ConcreteTestModel extends BaseModel
{
    // BaseModel is now concrete and can be instantiated directly
    // in Laravel implementation
}

/**
 * BaseModelTest
 *
 * Tests for the Laravel BaseModel implementation.
 *
 * Verifies that:
 * 1. Laravel implementation maintains backward compatibility
 * 2. All contracts are implemented correctly
 * 3. UUID generation works
 * 4. DateTimeImmutable is used for timestamps
 * 5. Serialization works correctly
 *
 * @package Tests\Unit\Models
 */
class BaseModelTest extends TestCase
{
    /**
     * Test that model can be instantiated
     */
    public function testModelCanBeInstantiated(): void
    {
        $model = new ConcreteTestModel();

        $this->assertInstanceOf(ConcreteTestModel::class, $model);
    }

    /**
     * Test that model implements EntityInterface
     */
    public function testModelImplementsEntityInterface(): void
    {
        $model = new ConcreteTestModel();

        $this->assertInstanceOf(EntityInterface::class, $model);
    }

    /**
     * Test that model implements AuditableInterface
     */
    public function testModelImplementsAuditableInterface(): void
    {
        $model = new ConcreteTestModel();

        $this->assertInstanceOf(AuditableInterface::class, $model);
    }

    /**
     * Test that model implements MetadataInterface
     */
    public function testModelImplementsMetadataInterface(): void
    {
        $model = new ConcreteTestModel();

        $this->assertInstanceOf(MetadataInterface::class, $model);
    }

    /**
     * Test that entity ID is a valid UUID
     */
    public function testEntityIdIsValidUuid(): void
    {
        $model = new ConcreteTestModel();

        $id = $model->getEntityId();

        $this->assertIsString($id);
        $this->assertNotEmpty($id);
        // UUID v4 format check
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $id
        );
    }

    /**
     * Test that entity type is set to class name
     */
    public function testEntityTypeIsSetToClassName(): void
    {
        $model = new ConcreteTestModel();

        $type = $model->getEntityType();

        $this->assertStringEndsWith('ConcreteTestModel', $type);
    }

    /**
     * Test that version starts at 1
     */
    public function testVersionStartsAtOne(): void
    {
        $model = new ConcreteTestModel();

        $this->assertEquals(1, $model->getEntityVersion());
    }

    /**
     * Test that created_at is DateTimeImmutable
     */
    public function testCreatedAtIsDateTimeImmutable(): void
    {
        $model = new ConcreteTestModel();

        $createdAt = $model->getCreatedAt();

        $this->assertInstanceOf(DateTimeImmutable::class, $createdAt);
    }

    /**
     * Test that metadata can be set and retrieved
     */
    public function testMetadataCanBeSetAndRetrieved(): void
    {
        $model = new ConcreteTestModel();

        $model->setMetadataValue('key', 'value');

        $this->assertEquals('value', $model->getMetadataValue('key'));
        $this->assertTrue($model->hasMetadata('key'));
    }

    /**
     * Test that audit information can be set
     */
    public function testAuditInformationCanBeSet(): void
    {
        $model = new ConcreteTestModel();

        $model->setCreatedBy('user-1');

        $this->assertEquals('user-1', $model->getCreatedBy());
    }

    /**
     * Test that model can be converted to array
     */
    public function testModelCanBeConvertedToArray(): void
    {
        $model = new ConcreteTestModel();
        $model->setCreatedBy('user-1');

        $array = $model->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('entity_id', $array);
        $this->assertArrayHasKey('entity_type', $array);
        $this->assertArrayHasKey('created_by', $array);
    }

    /**
     * Test that model can be converted to JSON
     */
    public function testModelCanBeConvertedToJson(): void
    {
        $model = new ConcreteTestModel();

        $json = $model->toJson();

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('entity_id', $decoded);
    }

    /**
     * Test that model can be JSON serialized
     */
    public function testModelCanBeJsonSerialized(): void
    {
        $model = new ConcreteTestModel();

        $serialized = json_encode($model);

        $this->assertIsString($serialized);
        $decoded = json_decode($serialized, true);
        $this->assertIsArray($decoded);
    }

    /**
     * Test that model has a string representation
     */
    public function testModelHasStringRepresentation(): void
    {
        $model = new ConcreteTestModel();

        $string = (string) $model;

        $this->assertIsString($string);
        $this->assertStringContainsString('v1', $string);
        $this->assertStringContainsString('ConcreteTestModel', $string);
    }

    /**
     * Test that soft-deleted model shows in string representation
     */
    public function testSoftDeletedModelShowsInStringRepresentation(): void
    {
        $model = new ConcreteTestModel();
        $model->setDeletedBy('user-1');

        $string = (string) $model;

        $this->assertStringContainsString('DELETED', $string);
    }

    /**
     * Test that model can be soft deleted
     */
    public function testModelCanBeSoftDeleted(): void
    {
        $model = new ConcreteTestModel();

        $this->assertFalse($model->isDeleted());

        $model->setDeletedBy('user-1');

        $this->assertTrue($model->isDeleted());
        $this->assertEquals('user-1', $model->getDeletedBy());
        $this->assertInstanceOf(DateTimeImmutable::class, $model->getDeletedAt());
    }

    /**
     * Test that soft-deleted model can be restored
     */
    public function testSoftDeletedModelCanBeRestored(): void
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
     * Test that update timestamp is set when updated
     */
    public function testUpdateTimestampIsSetWhenUpdated(): void
    {
        $model = new ConcreteTestModel();

        $this->assertNull($model->getUpdatedAt());

        $model->setUpdatedBy('user-1');

        $this->assertNotNull($model->getUpdatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $model->getUpdatedAt());
    }

    /**
     * Test that different instances have different UUIDs
     */
    public function testDifferentInstancesHaveDifferentUuids(): void
    {
        $model1 = new ConcreteTestModel();
        $model2 = new ConcreteTestModel();

        $this->assertNotEquals($model1->getEntityId(), $model2->getEntityId());
    }

    /**
     * Test that metadata can be cleared
     */
    public function testMetadataCanBeCleared(): void
    {
        $model = new ConcreteTestModel();

        $model->setMetadataValue('key1', 'value1');
        $model->setMetadataValue('key2', 'value2');

        $this->assertCount(2, $model->getMetadata());

        $model->clearMetadata();

        $this->assertCount(0, $model->getMetadata());
    }

    /**
     * Test that metadata can be removed
     */
    public function testMetadataCanBeRemoved(): void
    {
        $model = new ConcreteTestModel();

        $model->setMetadataValue('key', 'value');
        $this->assertTrue($model->hasMetadata('key'));

        $model->removeMetadata('key');

        $this->assertFalse($model->hasMetadata('key'));
    }

    /**
     * Test that array representation includes all expected keys
     */
    public function testArrayRepresentationIncludesAllExpectedKeys(): void
    {
        $model = new ConcreteTestModel();

        $array = $model->toArray();

        $expectedKeys = [
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

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $array, "Missing key: $key");
        }
    }

    /**
     * Test that dates in array are DateTimeImmutable
     */
    public function testDatesInArrayAreDateTimeImmutable(): void
    {
        $model = new ConcreteTestModel();

        $array = $model->toArray();

        $this->assertInstanceOf(DateTimeImmutable::class, $array['created_at']);
    }
}
