<?php

declare(strict_types=1);

namespace Robier\ForgeObject\Test\Unit;

use Robier\ForgeObject\Builder;
use Robier\ForgeObject\Exception\FactoryNotDefined;
use Robier\ForgeObject\Manager;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ManagerTest extends TestCase
{
    public function testNewFactoryCanBeRegistered(): void
    {
        $manager = new Manager();

        $this->assertFalse($manager->has(stdClass::class));

        $manager->register(stdClass::class, static function (): stdClass {
            return new stdClass();
        });

        $this->assertTrue($manager->has(stdClass::class));
    }

    public function testStateCanNotBeRegisteredForNotDefinedFactory(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $manager = new Manager();

        $manager->registerState(stdClass::class, 'test-state', static function () {
        });
    }

    public function testManagerCreatesNewBuilderWithoutAnyStates(): void
    {
        $manager = new Manager();

        $manager->register(stdClass::class, static function (): stdClass {
            return new stdClass();
        });

        $builder = $manager->new(stdClass::class);

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertCount(0, $builder->availableStates());
    }

    public function testManagerCreatesNewBuilderWithStateApplied(): void
    {
        $manager = new Manager();

        $manager->register(stdClass::class, static function (): stdClass {
            return new stdClass();
        });

        $manager->registerState(stdClass::class, 'foo', static function (stdClass $item): void {
            $item->foo = 'bar';
        });

        $builder = $manager->new(stdClass::class);

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertSame(['foo'], $builder->availableStates());
    }

    public function testManagerWillThrowExceptionForNotRegisteredFactory(): void
    {
        $this->expectException(FactoryNotDefined::class);

        $manager = new Manager();

        $manager->new('not-registered-class');
    }

    public function testManagerCanCheckIfStateIsRegistered(): void
    {
        $manager = new Manager();

        $manager->register(stdClass::class, static function (): stdClass {
            return new stdClass();
        });
        $manager->registerState(stdClass::class, 'foo', static function (): void {
        });

        $this->assertFalse($manager->hasState('not-existing', 'bar'));
        $this->assertFalse($manager->hasState(stdClass::class, 'bar'));
        $this->assertTrue($manager->hasState(stdClass::class, 'foo'));
    }
}
