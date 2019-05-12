<?php

declare(strict_types=1);

namespace Robier\ForgeObject\Test\Functional;

use PHPUnit\Framework\TestCase;
use Robier\ForgeObject\Collection;
use Robier\ForgeObject\Manager;
use stdClass;

final class BuildObjectTest extends TestCase
{
    public function testManagerCanRegisterAndCreateFixture(): void
    {
        $manager = new Manager();

        // register class
        $manager->register(stdClass::class, static function (): stdClass {
            $stdClass = new stdClass();
            $stdClass->test = (bool)rand(0, 1);

            return $stdClass;
        });

        $manager->registerState(stdClass::class, 'false-state', static function (stdClass $stdClass): void {
            $stdClass->test = false;
        });

        $manager->registerState(stdClass::class, 'true-state', static function (stdClass $stdClass): void {
            $stdClass->test = true;
        });

        $builder = $manager->new(stdClass::class);

        // fixture without any states
        $fixtureWithoutState = $builder->one();

        $this->assertObjectHasAttribute('test', $fixtureWithoutState);
        // it's a random bool value so we can not test exact value
        $this->assertIsBool($fixtureWithoutState->test);

        // fixture with state
        $fixtureWithState = $builder->state('false-state')->one();

        $this->assertObjectHasAttribute('test', $fixtureWithState);
        $this->assertFalse($fixtureWithState->test);

        // fixture with state
        $fixtureWithState = $builder->state('true-state')->one();

        $this->assertObjectHasAttribute('test', $fixtureWithState);
        $this->assertTrue($fixtureWithState->test);
    }

    public function testManagerCanRegisterAndCreateCollectionOfFixtures(): void
    {
        $manager = new Manager();

        // register class
        $manager->register(stdClass::class, static function (): stdClass {
            $stdClass = new stdClass();
            $stdClass->test = (bool)rand(0, 1);

            return $stdClass;
        });

        $manager->registerState(stdClass::class, 'false-state', static function (stdClass $stdClass): void {
            $stdClass->test = false;
        });

        $manager->registerState(stdClass::class, 'true-state', static function (stdClass $stdClass): void {
            $stdClass->test = true;
        });

        $builder = $manager->new(stdClass::class);

        // fixtures without any states
        $fixtureCollection = $builder->many(25);

        $this->assertInstanceOf(Collection::class, $fixtureCollection);

        $this->assertCount(25, $fixtureCollection);

        foreach ($fixtureCollection as $fixture) {
            $this->assertObjectHasAttribute('test', $fixture);
            $this->assertIsBool($fixture->test);
        }

        // fixtures with state
        $fixtureCollection = $builder->state('false-state')->many(30);

        $this->assertInstanceOf(Collection::class, $fixtureCollection);

        $this->assertCount(30, $fixtureCollection);

        foreach ($fixtureCollection as $fixture) {
            $this->assertObjectHasAttribute('test', $fixture);
            $this->assertFalse($fixture->test);
        }

        // fixtures with state
        $fixtureCollection = $builder->state('true-state')->many(35);

        $this->assertCount(35, $fixtureCollection);

        foreach ($fixtureCollection as $fixture) {
            $this->assertObjectHasAttribute('test', $fixture);
            $this->assertTrue($fixture->test);
        }
    }
}
