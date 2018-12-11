<?php

declare(strict_types=1);

namespace Robier\FixtureFactory\Test\Unit;

use Robier\FixtureFactory\Builder;
use Robier\FixtureFactory\Exception\FactoryNotDefined;
use Robier\FixtureFactory\Manager;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ManagerTest extends TestCase
{
    public function testNewFactoryCanBeRegistered(): void
    {
        $manager = new Manager();

        $this->assertFalse($manager->has(stdClass::class));

        $manager->register(stdClass::class, function(): stdClass{
            return new stdClass();
        });

        $this->assertTrue($manager->has(stdClass::class));
    }

    public function testStateCanNotBeRegisteredForNotDefinedFactory(): void
    {
        // @todo change exception
        $this->expectException(\InvalidArgumentException::class);

        $manager = new Manager();

        $manager->registerState(stdClass::class, 'test-state', function(){});
    }

    public function testManagerCreatesNewBuilderWithoutAnyStates(): void
    {
        $manager = new Manager();

        $manager->register(stdClass::class, function(): stdClass{
            return new stdClass();
        });

        $builder = $manager->new(stdClass::class);

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertCount(0, $builder->availableStates());
    }

    public function testManagerCreatesNewBuilderWithStateApplied(): void
    {
        $manager = new Manager();

        $manager->register(stdClass::class, function(): stdClass{
            return new stdClass();
        });

        $manager->registerState(stdClass::class, 'foo', function(stdClass $item): void{
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

        $manager->register('test', function(){});
        $manager->registerState('test', 'foo', function(){});

        $this->assertFalse($manager->hasState('not-existing', 'bar'));
        $this->assertFalse($manager->hasState('test', 'bar'));
        $this->assertTrue($manager->hasState('test', 'foo'));
    }
}
