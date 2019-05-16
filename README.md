Forge object
============

<p align="center">
    <a href="https://travis-ci.org/robier/forge-object">
        <img src="https://travis-ci.org/robier/forge-object.svg?branch=master" alt="Build Status">
    </a>
    <a href="https://codecov.io/gh/robier/forge-object">
        <img src="https://codecov.io/gh/robier/forge-object/branch/master/graph/badge.svg" />
    </a>
    <img src="https://badge.stryker-mutator.io/github.com/robier/forge-object/master" alt="Mutation score">
    <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="MIT">
</p>

Easily forge your own object for testing purposes (not intended for production code).

Heavily inspired by [Larvel's factories](https://laravel.com/docs/5.8/database-testing) but with one
big difference, Laravel's factories are used for only creating or persisting models, but this library
can create any type of object (model, entity, value object...). This library do not know how to persist
something. 

Library is framework agnostic and can be implemented in any framework.

Every test has a 3 main parts:
- `setup` - setting up application for test
- `test` - actual asserts
- `cleanup` - killing references in code or database

This library aims to reduce setup part of tests and also to reduce time needed for adding new data to object, especially
when you use that object a lot in your tests.

If you find some feature that you would like to see in this library feel free to contribute or open an issue :).

### Install

Library can be installed via composer:

```bash
composer require --dev robier/forge-object
```

### Usage

First you need to register forge for your object:

```php
$manager = new Robier\ForgeObject\Manager();
$manager->register(\stdClass::class, static function(): \stdClass{
    // apply random valid data to object
    $object = new \stdClass();
    $object->active = (bool)rand(0, 1);
    $object->admin = (bool)rand(0, 1);
    
    return $object;
})
```

After registration you can add states to object:

```php
$manager->registerState(\stdClass::class, 'active', static function(\stdClass $item): void{
    // change random data with exact data in states
    $item->active = true;
})

$manager->registerState(\stdClass::class, 'admin', static function(\stdClass $item): void{
    // change random data with exact data in states
    $item->admin = true;
})
```

Let's say you need one random stdClass object in your test:

```php
$oneStdClassObject = $manager->new(\stdClass::class)->one();
```

Or you need a random stdClass that is active:

```php
$oneStdClassObject = $manager->new(\stdClass::class)->state('active')->one();
```

Maybe you need multiple random stdClass objects that are active, let's say 15:

```php
$manyActiveStdClassObjects = $manager->new(\stdClass::class)->state('active')->many(15);
```

States can be combined and they are applied in order they are provided to `state` method. If you want random stdClass 
that is also an active and admin you would do it like this:

```php
$oneStdClassObject = $manager->new(\stdClass::class)->state('active', 'admin')->one();
```

### Local development

Build docker with command

```bash
docker/build
```

Run any command inside docker

```bash
docker/run {script}
```

for example:

```bash
docker/run composer install
```

Run all tests:
```bash
docker/run composer run test
```
