<?php

declare(strict_types=1);

namespace Robier\FixtureFactory\Test\Unit;

use Robier\FixtureFactory\Collection;
use PHPUnit\Framework\TestCase;
use stdClass;

class CollectionTest extends TestCase
{
    public function testCollectionHaveArrayAccess(): void
    {
        $collection = new Collection(new stdClass(), new stdClass(), new stdClass());

        // test offsetGet method
        $this->assertInstanceOf(stdClass::class, $collection[0]);
        $this->assertInstanceOf(stdClass::class, $collection[1]);
        $this->assertInstanceOf(stdClass::class, $collection[2]);

        // test offsetExists method
        $this->assertArrayNotHasKey(10, $collection);

        // test offsetSet method
        $collection[10] = new stdClass();

        // test offsetExists method
        $this->assertArrayHasKey(10, $collection);

        // test offsetUnset method
        unset($collection[10]);

        // test offsetExists method
        $this->assertArrayNotHasKey(10, $collection);
    }

    public function testCollectionCanBeConvertedToPlainArray(): void
    {
        $array = [
            new stdClass(),
            new stdClass(),
            new stdClass(),
            new stdClass(),
            new stdClass(),
            new stdClass(),
            new stdClass(),
        ];

        $collection = new Collection(...$array);

        $this->assertSame($array, $collection->toArray());
    }

    public function testCollectionIsIterable(): void
    {
        $collection = new Collection(new stdClass(), new stdClass(), new stdClass());

        $this->assertIsIterable($collection);
    }

    public function testCollectionIsCountable(): void
    {
        $collection = new Collection(new stdClass(), new stdClass(), new stdClass(),new stdClass(), new stdClass(), new stdClass());

        $this->assertCount(6, $collection);
    }

    public function testCollectionWillApplyCallbackToAllItemsInCollection(): void
    {
        $collection = new Collection(new stdClass(), new stdClass(), new stdClass(),new stdClass(), new stdClass(), new stdClass());

        foreach($collection as $item){
            $this->assertObjectNotHasAttribute('test_property', $item);
        }

        $collection->apply(function(stdClass $class): void{
            $class->test_property = 'foo';
        });

        // number of items should not change
        $this->assertCount(6, $collection);

        foreach($collection as $item){
            $this->assertObjectHasAttribute('test_property', $item);
        }
    }

    public function testCollectionCanBeMergedWithAnotherCollection(): void
    {
        $collection1 = new Collection(new stdClass(), new stdClass(), new stdClass(),new stdClass(), new stdClass());
        $collection2 = new Collection(new stdClass(), new stdClass(), new stdClass(),new stdClass());

        $mergedCollection = $collection1->merge($collection2);

        $this->assertCount(9, $mergedCollection);

        // merge will return new collection instance
        $this->assertNotSame($collection1, $mergedCollection);
        $this->assertNotSame($collection2, $mergedCollection);
    }

    public function testCollectionCanReturnFirstItemFromCollection(): void
    {
        $firstObject = new stdClass();

        $collection = new Collection($firstObject, new stdClass(), new stdClass(),new stdClass(), new stdClass());

        $this->assertSame($firstObject, $collection->first());
    }

    public function testCollectionCanFilterItems(): void
    {
        $testObject = new stdClass();
        $testObject->test = true;

        $collection = new Collection($testObject, new stdClass(), new stdClass(), $testObject, new stdClass());

        $filteredCollection = $collection->filter(function($item): bool{
            return property_exists($item, 'test');
        });

        // old collection has all items
        $this->assertCount(5, $collection);

        // new collection has only filtered items
        $this->assertCount(2, $filteredCollection);
    }
}
