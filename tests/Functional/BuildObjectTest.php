<?php

declare(strict_types=1);

namespace Robier\FixtureFactory\Test\Functional;

use PHPUnit\Framework\TestCase;
use Robier\FixtureFactory\Manager;
use stdClass;

class BuildObjectTest extends TestCase
{
    public function testManagerCanRegisterAndCreateFixture(): void
    {
        $manager = new Manager();

        // register class
        $manager->register(stdClass::class, function(): stdClass{
            $stdClass = new stdClass();
            $stdClass->test = true;

            return $stdClass;
        });

        $manager->registerState(stdClass::class, 'false-state', function(stdClass $stdClass): void{
            $stdClass->test = false;
        });

        $builder = $manager->new(stdClass::class);

        // fixture without any states
        $fixtureWithoutState = $builder->one();

        $this->assertObjectHasAttribute('test', $fixtureWithoutState);
        $this->assertTrue($fixtureWithoutState->test);

        // fixture with state
        $fixtureWithState = $builder->state('false-state')->one();

        $this->assertObjectHasAttribute('test', $fixtureWithState);
        $this->assertFalse($fixtureWithState->test);
    }

    public function testManagerCanRegisterAndCreateCollectionOfFixtures(): void
    {
        $manager = new Manager();

        // register class
        $manager->register(stdClass::class, function(): stdClass{
            $stdClass = new stdClass();
            $stdClass->test = true;

            return $stdClass;
        });

        $manager->registerState(stdClass::class, 'false-state', function(stdClass $stdClass): void{
            $stdClass->test = false;
        });

        $builder = $manager->new(stdClass::class);

        // fixtures without any states
        $fixtureCollection = $builder->many(30);

        foreach($fixtureCollection as $fixture){
            $this->assertObjectHasAttribute('test', $fixture);
            $this->assertTrue($fixture->test);
        }

        // fixtures with state
        $fixtureCollection = $builder->state('false-state')->many(30);

        foreach($fixtureCollection as $fixture){
            $this->assertObjectHasAttribute('test', $fixture);
            $this->assertFalse($fixture->test);
        }
    }
}
