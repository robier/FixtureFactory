<?php

declare(strict_types=1);

namespace Robier\ForgeObject\Test\Unit;

use Generator;
use PHPUnit\Framework\TestCase;
use Robier\ForgeObject\Builder;
use Robier\ForgeObject\Collection;
use Robier\ForgeObject\Exception\StateNotDefined;
use stdClass;

final class BuilderTest extends TestCase
{
    public function testBuilderCanReturnOneInstance(): void
    {
        $builder = new Builder(
            stdClass::class,
            static function (): stdClass {
                return new stdClass();
            },
            []
        );

        $this->assertInstanceOf(stdClass::class, $builder->one());
    }

    public function testBuilderCanReturnOneInstanceWithAppliedState(): void
    {
        $builder = new Builder(
            stdClass::class,
            static function (): stdClass {
                return new stdClass();
            },
            [
                'test' => static function (stdClass $testClass): void {
                    $testClass->testProperty = 'property-value';
                },
            ]
        );

        /** @var stdClass $fixture */
        $fixture = $builder->state('test')->one();

        $this->assertInstanceOf(stdClass::class, $fixture);
        $this->assertObjectHasAttribute('testProperty', $fixture);
        $this->assertSame('property-value', $fixture->testProperty);
    }

    public function testBuilderCanReturnOneInstanceWithAppliedCallback(): void
    {
        $builder = new Builder(
            stdClass::class,
            static function (): stdClass {
                return new stdClass();
            },
            []
        );

        /** @var stdClass $fixture */
        $fixture = $builder
            ->one(
                static function (stdClass $testClass): void {
                    $testClass->testProperty = 'property-value';
                }
            );

        $this->assertInstanceOf(stdClass::class, $fixture);
        $this->assertObjectHasAttribute('testProperty', $fixture);
        $this->assertSame('property-value', $fixture->testProperty);
    }

    public function testBuilderCanReturnCollectionOfInstances(): void
    {
        $builder = new Builder(
            stdClass::class,
            static function (): stdClass {
                return new stdClass();
            },
            []
        );

        $collection = $builder->many(15);

        $this->assertCount(15, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
    }

    public function testBuilderCanReturnCollectionOfInstancesWithAppliedState(): void
    {
        $builder = new Builder(
            stdClass::class,
            static function (): stdClass {
                return new stdClass();
            },
            [
                'test' => static function (stdClass $testClass): void {
                    $testClass->testProperty = 'property-value';
                },
            ]
        );

        $collection = $builder->state('test')->many(15);

        $this->assertCount(15, $collection);
        $this->assertInstanceOf(Collection::class, $collection);

        foreach ($collection as $fixture) {
            $this->assertInstanceOf(stdClass::class, $fixture);
            $this->assertObjectHasAttribute('testProperty', $fixture);
            $this->assertSame('property-value', $fixture->testProperty);
        }
    }

    public function testBuilderCanReturnCollectionOfInstancesWithAppliedCallback(): void
    {
        $builder = new Builder(
            stdClass::class,
            static function (): stdClass {
                return new stdClass();
            },
            []
        );

        $collection = $builder->many(15, static function (stdClass $testClass): void {
            $testClass->testProperty = 'property-value';
        });

        $this->assertCount(15, $collection);
        $this->assertInstanceOf(Collection::class, $collection);

        foreach ($collection as $fixture) {
            $this->assertInstanceOf(stdClass::class, $fixture);
            $this->assertTrue(property_exists($fixture, 'testProperty'), 'Property `testProperty` not found on generated fixture');
            $this->assertSame('property-value', $fixture->testProperty);
        }
    }

    /**
     * @dataProvider availableStatesDataProvider
     * @param int $count
     * @param array $states
     */
    public function testDefinedStatesCanBeFetched(int $count, array $states): void
    {
        $builder = new Builder(
            stdClass::class,
            static function (): stdClass {
                return new stdClass();
            },
            $states
        );

        $availableStates = $builder->availableStates();
        $this->assertCount($count, $availableStates);
        $this->assertSame(array_keys($states), $availableStates);
    }

    public function testUndefinedStateCanNotBeAppliedToBuilder(): void
    {
        $this->expectException(StateNotDefined::class);
        $this->expectExceptionMessage(
            sprintf('States [%s] not defined for class %s', 'not-existing-state', stdClass::class)
        );

        $builder = new Builder(
            stdClass::class,
            static function (): stdClass {
                return new stdClass();
            },
            []
        );

        $builder->state('not-existing-state');
    }

    public function testBuilderWillReturnNewInstanceWhenStateReturnsNewInstance(): void
    {
        $stdClass = new stdClass();

        $builder = new Builder(
            stdClass::class,
            static function (): stdClass {
                return new stdClass();
            },
            [
                'test' => static function () use ($stdClass): stdClass {
                    return $stdClass;
                },
            ]
        );

        $this->assertNotSame($stdClass, $builder->one());
        $this->assertSame($stdClass, $builder->state('test')->one());
    }

    public function testBuilderWillReturnNewInstanceWhenAppliedCallbackReturnsNewInstance(): void
    {
        $stdClass = new stdClass();

        $builder = new Builder(
            stdClass::class,
            static function (): stdClass {
                return new stdClass();
            },
            []
        );

        $this->assertNotSame($stdClass, $builder->one());
        $this->assertSame(
            $stdClass,
            $builder->one(
                static function () use ($stdClass): stdClass {
                    return $stdClass;
                }
            )
        );
    }

    public function availableStatesDataProvider(): Generator
    {
        yield '2 available states' => [
            2,
            [
                'foo' => '',
                'bar' => '',
            ],
        ];

        yield '0 available states' => [
            0,
            [],
        ];
    }
}
