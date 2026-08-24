<?php

declare(strict_types=1);

namespace WaysNX\BusinessFramework\Tests\Unit\Collections;

use PHPUnit\Framework\TestCase;
use WaysNX\BusinessFramework\Collections\BaseCollection;
use WaysNX\BusinessFramework\Contracts\CollectionInterface;
use WaysNX\BusinessFramework\Models\BaseModel;

/**
 * BaseCollectionTest
 *
 * PHPUnit test suite for BaseCollection functionality.
 *
 * Tests cover:
 * - Collection initialization
 * - Filtering
 * - Mapping
 * - Grouping
 * - Sorting
 * - Serialization
 * - Metadata handling
 * - Empty collections
 * - Iteration
 * - Extension hooks
 *
 * @package WaysNX\BusinessFramework\Tests\Unit\Collections
 */
class BaseCollectionTest extends TestCase
{
    /**
     * Test collection initialization
     *

     * @return void
     */
    public function testCollectionInitializationEmpty(): void
    {
        $collection = new BaseCollection();

        $this->assertInstanceOf(CollectionInterface::class, $collection);
        $this->assertTrue($collection->isEmpty());
        $this->assertEquals(0, $collection->count());
    }

    /**
     * Test collection initialization with items
     *

     * @return void
     */
    public function testCollectionInitializationWithItems(): void
    {
        $items = ['item1', 'item2', 'item3'];
        $collection = new BaseCollection($items);

        $this->assertEquals(3, $collection->count());
        $this->assertFalse($collection->isEmpty());
    }

    /**
     * Test collection initialization with metadata
     *

     * @return void
     */
    public function testCollectionInitializationWithMetadata(): void
    {
        $metadata = ['page' => 1, 'total' => 100];
        $collection = new BaseCollection([], $metadata);

        $this->assertEquals($metadata, $collection->getMetadata());
    }

    /**
     * Test add item to collection
     *

     * @return void
     */
    public function testAddItemToCollection(): void
    {
        $collection = new BaseCollection();
        $result = $collection->add('item1');

        $this->assertSame($collection, $result);
        $this->assertEquals(1, $collection->count());
        $this->assertEquals('item1', $collection->first());
    }

    /**
     * Test add returns self for chaining
     *

     * @return void
     */
    public function testAddReturnsSelfForChaining(): void
    {
        $collection = new BaseCollection();
        $result = $collection->add('item1')->add('item2');

        $this->assertSame($collection, $result);
        $this->assertEquals(2, $collection->count());
    }

    /**
     * Test remove item from collection
     *

     * @return void
     */
    public function testRemoveItemFromCollection(): void
    {
        $collection = new BaseCollection(['item1', 'item2', 'item3']);
        $result = $collection->remove('item2');

        $this->assertSame($collection, $result);
        $this->assertEquals(2, $collection->count());
        $this->assertFalse($collection->contains('item2'));
    }

    /**
     * Test filter collection
     *

     * @return void
     */
    public function testFilterCollection(): void
    {
        $collection = new BaseCollection([1, 2, 3, 4, 5]);
        $filtered = $collection->filter(fn($item) => $item > 2);

        $this->assertInstanceOf(CollectionInterface::class, $filtered);
        $this->assertEquals(3, $filtered->count());
        $this->assertTrue($filtered->contains(3));
        $this->assertFalse($filtered->contains(2));
    }

    /**
     * Test map collection
     *

     * @return void
     */
    public function testMapCollection(): void
    {
        $collection = new BaseCollection([1, 2, 3]);
        $mapped = $collection->map(fn($item) => $item * 2);

        $this->assertInstanceOf(CollectionInterface::class, $mapped);
        $this->assertEquals(3, $mapped->count());
        $this->assertEquals(2, $mapped->first());
        $this->assertEquals(6, $mapped->last());
    }

    /**
     * Test reduce collection
     *

     * @return void
     */
    public function testReduceCollection(): void
    {
        $collection = new BaseCollection([1, 2, 3, 4]);
        $sum = $collection->reduce(fn($carry, $item) => $carry + $item, 0);

        $this->assertEquals(10, $sum);
    }

    /**
     * Test groupBy with string key
     *

     * @return void
     */
    public function testGroupByWithStringKey(): void
    {
        $items = [
            (object)['type' => 'A', 'value' => 1],
            (object)['type' => 'B', 'value' => 2],
            (object)['type' => 'A', 'value' => 3],
        ];
        $collection = new BaseCollection($items);
        $grouped = $collection->groupBy('type');

        $this->assertEquals(2, count($grouped));
        $this->assertEquals(2, count($grouped['A']));
        $this->assertEquals(1, count($grouped['B']));
    }

    /**
     * Test groupBy with callable
     *

     * @return void
     */
    public function testGroupByWithCallable(): void
    {
        $collection = new BaseCollection([1, 2, 3, 4, 5]);
        $grouped = $collection->groupBy(fn($item) => $item % 2 === 0 ? 'even' : 'odd');

        $this->assertEquals(2, count($grouped));
        $this->assertEquals(3, count($grouped['odd']));
        $this->assertEquals(2, count($grouped['even']));
    }

    /**
     * Test sort collection with default sorting
     *

     * @return void
     */
    public function testSortCollectionDefault(): void
    {
        $collection = new BaseCollection([3, 1, 2]);
        $sorted = $collection->sort();

        $this->assertInstanceOf(CollectionInterface::class, $sorted);
        $this->assertEquals(1, $sorted->first());
        $this->assertEquals(3, $sorted->last());
    }

    /**
     * Test sort collection with custom callback
     *

     * @return void
     */
    public function testSortCollectionWithCallback(): void
    {
        $collection = new BaseCollection([1, 2, 3]);
        $sorted = $collection->sort(fn($a, $b) => $b <=> $a);

        $this->assertEquals(3, $sorted->first());
        $this->assertEquals(1, $sorted->last());
    }

    /**
     * Test first with no callback
     *

     * @return void
     */
    public function testFirstWithNoCallback(): void
    {
        $collection = new BaseCollection(['a', 'b', 'c']);

        $this->assertEquals('a', $collection->first());
    }

    /**
     * Test first with callback
     *

     * @return void
     */
    public function testFirstWithCallback(): void
    {
        $collection = new BaseCollection([1, 2, 3, 4]);
        $first = $collection->first(fn($item) => $item > 2);

        $this->assertEquals(3, $first);
    }

    /**
     * Test first on empty collection
     *

     * @return void
     */
    public function testFirstOnEmptyCollection(): void
    {
        $collection = new BaseCollection();

        $this->assertNull($collection->first());
    }

    /**
     * Test last with no callback
     *

     * @return void
     */
    public function testLastWithNoCallback(): void
    {
        $collection = new BaseCollection(['a', 'b', 'c']);

        $this->assertEquals('c', $collection->last());
    }

    /**
     * Test last with callback
     *

     * @return void
     */
    public function testLastWithCallback(): void
    {
        $collection = new BaseCollection([1, 2, 3, 4]);
        $last = $collection->last(fn($item) => $item < 4);

        $this->assertEquals(3, $last);
    }

    /**
     * Test get item at index
     *

     * @return void
     */
    public function testGetItemAtIndex(): void
    {
        $collection = new BaseCollection(['a', 'b', 'c']);

        $this->assertEquals('a', $collection->get(0));
        $this->assertEquals('b', $collection->get(1));
        $this->assertEquals('c', $collection->get(2));
        $this->assertNull($collection->get(5));
    }

    /**
     * Test contains item
     *

     * @return void
     */
    public function testContainsItem(): void
    {
        $collection = new BaseCollection(['a', 'b', 'c']);

        $this->assertTrue($collection->contains('a'));
        $this->assertFalse($collection->contains('z'));
    }

    /**
     * Test isEmpty
     *

     * @return void
     */
    public function testIsEmpty(): void
    {
        $empty = new BaseCollection();
        $notEmpty = new BaseCollection(['item']);

        $this->assertTrue($empty->isEmpty());
        $this->assertFalse($notEmpty->isEmpty());
    }

    /**
     * Test all returns items
     *

     * @return void
     */
    public function testAllReturnsItems(): void
    {
        $items = ['a', 'b', 'c'];
        $collection = new BaseCollection($items);

        $this->assertEquals($items, $collection->all());
    }

    /**
     * Test values returns items
     *

     * @return void
     */
    public function testValuesReturnsItems(): void
    {
        $collection = new BaseCollection(['a', 'b', 'c']);

        $this->assertEquals(['a', 'b', 'c'], $collection->values());
    }

    /**
     * Test keys returns array keys
     *

     * @return void
     */
    public function testKeysReturnsArrayKeys(): void
    {
        $items = ['a' => 1, 'b' => 2, 'c' => 3];
        $collection = new BaseCollection($items);

        // BaseCollection stores items sequentially via array_values()
        // so keys() returns numeric indices, not original associative keys
        $this->assertEquals([0, 1, 2], $collection->keys());
    }

    /**
     * Test pluck from items
     *

     * @return void
     */
    public function testPluckFromItems(): void
    {
        $items = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ];
        $collection = new BaseCollection($items);
        $ids = $collection->pluck('id');

        $this->assertEquals([1, 2], $ids);
    }

    /**
     * Test each iterates over items
     *

     * @return void
     */
    public function testEachIteratesOverItems(): void
    {
        $collection = new BaseCollection([1, 2, 3]);
        $sum = 0;

        $result = $collection->each(function ($item) use (&$sum) {
            $sum += $item;
        });

        $this->assertSame($collection, $result);
        $this->assertEquals(6, $sum);
    }

    /**
     * Test merge collections
     *

     * @return void
     */
    public function testMergeCollections(): void
    {
        $collection1 = new BaseCollection([1, 2]);
        $collection2 = new BaseCollection([3, 4]);
        $merged = $collection1->merge($collection2);

        $this->assertInstanceOf(CollectionInterface::class, $merged);
        $this->assertEquals(4, $merged->count());
        $this->assertTrue($merged->contains(1));
        $this->assertTrue($merged->contains(4));
    }

    /**
     * Test toArray serialization
     *

     * @return void
     */
    public function testToArraySerialization(): void
    {
        $items = ['a', 'b', 'c'];
        $collection = new BaseCollection($items);
        $array = $collection->toArray();

        // toArray() calls (array) $item on strings, converting 'a' to [0 => 'a']
        $expected = [
            [0 => 'a'],
            [0 => 'b'],
            [0 => 'c'],
        ];
        $this->assertEquals($expected, $array);
    }

    /**
     * Test toJson serialization
     *

     * @return void
     */
    public function testToJsonSerialization(): void
    {
        $collection = new BaseCollection(['a', 'b', 'c']);
        $json = $collection->toJson();

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        // toArray() converts strings to arrays, so toJson() reflects this
        $expected = [
            [0 => 'a'],
            [0 => 'b'],
            [0 => 'c'],
        ];
        $this->assertEquals($expected, $decoded);
    }

    /**
     * Test count returns correct number
     *

     * @return void
     */
    public function testCountReturnsCorrectNumber(): void
    {
        $collection = new BaseCollection(['a', 'b', 'c']);

        $this->assertEquals(3, $collection->count());
    }

    /**
     * Test getMetadata returns metadata
     *

     * @return void
     */
    public function testGetMetadataReturnsMetadata(): void
    {
        $metadata = ['page' => 1, 'total' => 100];
        $collection = new BaseCollection([], $metadata);

        $this->assertEquals($metadata, $collection->getMetadata());
    }

    /**
     * Test setMetadata returns self
     *

     * @return void
     */
    public function testSetMetadataReturnsSelf(): void
    {
        $collection = new BaseCollection();
        $result = $collection->setMetadata(['page' => 1]);

        $this->assertSame($collection, $result);
        $this->assertEquals(['page' => 1], $collection->getMetadata());
    }

    /**
     * Test getMetadataValue
     *

     * @return void
     */
    public function testGetMetadataValue(): void
    {
        $collection = new BaseCollection([], ['page' => 1, 'total' => 100]);

        $this->assertEquals(1, $collection->getMetadataValue('page'));
        $this->assertEquals(100, $collection->getMetadataValue('total'));
        $this->assertNull($collection->getMetadataValue('missing'));
        $this->assertEquals('default', $collection->getMetadataValue('missing', 'default'));
    }

    /**
     * Test setMetadataValue returns self
     *

     * @return void
     */
    public function testSetMetadataValueReturnsSelf(): void
    {
        $collection = new BaseCollection();
        $result = $collection->setMetadataValue('page', 1);

        $this->assertSame($collection, $result);
        $this->assertEquals(1, $collection->getMetadataValue('page'));
    }

    /**
     * Test collection is iterable
     *

     * @return void
     */
    public function testCollectionIsIterable(): void
    {
        $collection = new BaseCollection(['a', 'b', 'c']);
        $items = [];

        foreach ($collection as $item) {
            $items[] = $item;
        }

        $this->assertEquals(['a', 'b', 'c'], $items);
    }

    /**
     * Test JSON serializable
     *

     * @return void
     */
    public function testJsonSerializable(): void
    {
        $collection = new BaseCollection(['a', 'b', 'c']);
        $json = json_encode($collection);
        $decoded = json_decode($json, true);

        // json_encode uses jsonSerialize() which calls toArray(), converting strings to arrays
        $expected = [
            [0 => 'a'],
            [0 => 'b'],
            [0 => 'c'],
        ];
        $this->assertEquals($expected, $decoded);
    }

    /**
     * Test extension hooks are called
     *

     * @return void
     */
    public function testExtensionHooksAreCalled(): void
    {
        $service = new TestCollection(['a', 'b', 'c']);
        
        $service->filter(fn($item) => true);
        $this->assertTrue($service->beforeTransformCalled);
        $this->assertTrue($service->afterTransformCalled);

        $service->beforeTransformCalled = false;
        $service->afterTransformCalled = false;

        $service->toArray();
        $this->assertTrue($service->beforeSerializeCalled);
        $this->assertTrue($service->afterSerializeCalled);
    }

    /**
     * Test filter creates new collection
     *

     * @return void
     */
    public function testFilterCreatesNewCollection(): void
    {
        $collection = new BaseCollection([1, 2, 3]);
        $filtered = $collection->filter(fn($item) => $item > 1);

        $this->assertNotSame($collection, $filtered);
        $this->assertEquals(3, $collection->count());
        $this->assertEquals(2, $filtered->count());
    }

    /**
     * Test metadata persists through transformations
     *

     * @return void
     */
    public function testMetadataPersistsThroughTransformations(): void
    {
        $collection = new BaseCollection([1, 2, 3], ['page' => 1]);
        $filtered = $collection->filter(fn($item) => $item > 1);

        $this->assertEquals(['page' => 1], $filtered->getMetadata());
    }

    /**
     * Test empty collection operations
     *

     * @return void
     */
    public function testEmptyCollectionOperations(): void
    {
        $collection = new BaseCollection();

        $this->assertTrue($collection->isEmpty());
        $this->assertEquals(0, $collection->count());
        $this->assertNull($collection->first());
        $this->assertNull($collection->last());
        $this->assertEquals([], $collection->all());
        $this->assertEquals([], $collection->keys());
    }

    /**
     * Test collection with mixed types
     *

     * @return void
     */
    public function testCollectionWithMixedTypes(): void
    {
        $collection = new BaseCollection([1, 'string', 3.14, true, null]);

        $this->assertEquals(5, $collection->count());
        $this->assertTrue($collection->contains(1));
        $this->assertTrue($collection->contains('string'));
        $this->assertTrue($collection->contains(null));
    }
}

/**
 * TestCollection - Test collection with hook tracking
 *

 * @package WaysNX\BusinessFramework\Tests\Unit\Collections
 */
class TestCollection extends BaseCollection
{
    /**
     * Track if beforeTransform was called
     *

     * @var bool
     */
    public bool $beforeTransformCalled = false;

    /**
     * Track if afterTransform was called
     *

     * @var bool
     */
    public bool $afterTransformCalled = false;

    /**
     * Track if beforeSerialize was called
     *

     * @var bool
     */
    public bool $beforeSerializeCalled = false;

    /**
     * Track if afterSerialize was called
     *

     * @var bool
     */
    public bool $afterSerializeCalled = false;

    /**
     * Override beforeTransform
     *

     * @return void
     */
    protected function beforeTransform(): void
    {
        $this->beforeTransformCalled = true;
    }

    /**
     * Override afterTransform
     *

     * @return void
     */
    protected function afterTransform(): void
    {
        $this->afterTransformCalled = true;
    }

    /**
     * Override beforeSerialize
     *

     * @return void
     */
    protected function beforeSerialize(): void
    {
        $this->beforeSerializeCalled = true;
    }

    /**
     * Override afterSerialize
     *

     * @return void
     */
    protected function afterSerialize(): void
    {
        $this->afterSerializeCalled = true;
    }
}
