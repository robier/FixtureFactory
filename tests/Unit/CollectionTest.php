<?php

declare(strict_types=1);

namespace Robier\ForgeObject\Test\Unit;

use InvalidArgumentException;
use LogicException;
use Robier\ForgeObject\Collection;
use PHPUnit\Framework\TestCase;
use stdClass;

final class CollectionTest extends TestCase
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
        $collection = new Collection(new stdClass(), new stdClass(), new stdClass(), new stdClass(), new stdClass(), new stdClass());

        $this->assertCount(6, $collection);
    }

    public function testCollectionWillApplyCallbackToAllItemsInCollection(): void
    {
        $collection = new Collection(new stdClass(), new stdClass(), new stdClass(), new stdClass(), new stdClass(), new stdClass());

        foreach ($collection as $item) {
            $this->assertObjectNotHasAttribute('test_property', $item);
        }

        $collection->apply(static function (stdClass $class): void {
            $class->test_property = 'foo';
        });

        // number of items should not change
        $this->assertCount(6, $collection);

        foreach ($collection as $item) {
            $this->assertObjectHasAttribute('test_property', $item);
        }
    }

    public function testCollectionCanBeMergedWithAnotherCollection(): void
    {
        $collection1 = new Collection(new stdClass(), new stdClass(), new stdClass(), new stdClass(), new stdClass());
        $collection2 = new Collection(new stdClass(), new stdClass(), new stdClass(), new stdClass());

        $mergedCollection = $collection1->merge($collection2);

        $this->assertCount(9, $mergedCollection);

        // merge will return new collection instance
        $this->assertNotSame($collection1, $mergedCollection);
        $this->assertNotSame($collection2, $mergedCollection);
    }

    public function testCollectionWillReturnFirstItemFromCollection(): void
    {
        $firstObject = new stdClass();

        $collection = new Collection($firstObject, new stdClass(), new stdClass(), new stdClass(), new stdClass());

        $this->assertSame($firstObject, $collection->first());
    }

    public function testCollectionWillReturnLastItemFromCollection(): void
    {
        $lastItem = new stdClass();

        $collection = new Collection(new stdClass(), new stdClass(), new stdClass(), new stdClass(), $lastItem);

        $this->assertSame($lastItem, $collection->last());
    }

    public function testCollectionCanFilterItems(): void
    {
        $testObject = new stdClass();
        $testObject->test = true;

        $collection = new Collection($testObject, new stdClass(), new stdClass(), $testObject, new stdClass());

        $filteredCollection = $collection->filter(function ($item): bool {
            return property_exists($item, 'test');
        });

        // old collection has all items
        $this->assertCount(5, $collection);

        // new collection has only filtered items
        $this->assertCount(2, $filteredCollection);
    }

    public function testGettingRandomItemFromCollection(): void
    {
        $items = [
            (object)['test' => 1],
            (object)['test' => 2],
            (object)['test' => 3],
            (object)['test' => 4],
            (object)['test' => 5],
            (object)['test' => 6],
            (object)['test' => 7],
        ];

        $collection = new Collection(...$items);

        $randomItems = [
            $collection->random(),
            $collection->random(),
            $collection->random(),
            $collection->random(),
            $collection->random(),
            $collection->random(),
            $collection->random(),
            $collection->random(),
            $collection->random(),
            $collection->random(),
        ];

        // if all returned elements are same array_unique will return only one item ()
        $this->assertNotEquals(count(array_unique($randomItems, SORT_REGULAR)), 1);
    }

    /**
     * @dataProvider badOffsetSetArgumentsProvider
     *
     * @param $offset
     * @param $value
     */
    public function testOffsetSetThrowsExceptionsOnBadArgumentsProvided($offset, $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $collection = new Collection();

        $collection->offsetSet($offset, $value);
    }

    /**
     * @dataProvider badOffsetSetArgumentsProvider
     *
     * @param $offset
     * @param $value
     */
    public function testOffsetSetArrayAccessThrowsExceptionsOnBadArgumentsProvided($offset, $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $collection = new Collection();

        $collection[$offset] = $value;
    }

    public function testOffsetSetAmendsItemIfOffsetNotProvided(): void
    {
        $collection = new Collection();

        $items = [];

        $items[] = (object)['test' => 1];
        $items[] = (object)['test' => 2];
        $items[] = (object)['test' => 3];

        $collection->offsetSet(null, $items[0]);
        $collection->offsetSet(null, $items[1]);
        $collection->offsetSet(null, $items[2]);

        $this->assertSame($items[0], $collection->get(0));
        $this->assertSame($items[1], $collection->get(1));
        $this->assertSame($items[2], $collection->get(2));
    }

    public function testAddingNewItemToCollection(): void
    {
        $collection = new Collection();

        $items = [];

        $items[] = (object)['test' => 1];
        $items[] = (object)['test' => 2];
        $items[] = (object)['test' => 3];

        $collection->add(...$items);

        $this->assertSame($items[0], $collection[0]);
        $this->assertSame($items[1], $collection[1]);
        $this->assertSame($items[2], $collection[2]);
    }

    public function testCollectionCannotReturnFirstValueIfEmpty(): void
    {
        $this->expectException(LogicException::class);

        (new Collection())->first();
    }

    public function testCollectionCannotReturnLastValueIfEmpty(): void
    {
        $this->expectException(LogicException::class);

        (new Collection())->last();
    }

    public function testCollectionWillThrowExceptionIfGettingNotExistingItem(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Item with offset %d does not exist in collection', 55));

        (new Collection())->get(55);
    }

    public function testInsertingItemToSpecificPlaceInCollection(): void
    {
        $collection = new Collection();

        $items = [];

        $items[] = (object)['test' => 1];
        $items[] = (object)['test' => 2];
        $items[] = (object)['test' => 3];

        $collection->add(...$items);

        $target = (object)['test' => 'target'];

        $collection->offsetSet(1, $target);

        $this->assertSame($target, $collection->get(1));
        $this->assertCount(4, $collection);
    }

    public function badOffsetSetArgumentsProvider(): \Generator
    {
        yield 'offset is string, value is object' => ['bad offset', new stdClass()];
        yield 'offset is string, value is string' => ['bad offset', 'bad value'];
        yield 'offset is string, value is integer' => ['bad offset', 123];
        yield 'offset is string, value is null' => ['bad offset', null];
        //
        yield 'offset is object, value is object' => [new stdClass(), new stdClass()];
        yield 'offset is object, value is string' => [new stdClass(), 'bad value'];
        yield 'offset is object, value is integer' => [new stdClass(), 123];
        yield 'offset is object, value is null' => [new stdClass(), null];
        //
        yield 'offset is null, value is string' => [null, 'bad value'];
        yield 'offset is null, value is integer' => [null, 134];
        yield 'offset is null, value is null' => [null, null];
    }
}
