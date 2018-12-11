<?php

declare(strict_types=1);

namespace Robier\FixtureFactory;

use InvalidArgumentException;
use Robier\FixtureFactory\Exception\FactoryNotDefined;

final class Manager
{
    /**
     * @var array
     */
    private $factory = [];

    /**
     * @var array
     */
    private $states = [];

    /**
     * @param string $className
     * @param array|callable $setup
     *
     * @return Manager
     */
    public function register(string $className, callable $setup): self
    {
        $this->factory[$className] = $setup;

        return $this;
    }

    /**
     * @param string $className
     * @param string $state
     * @param array|callable $setup
     *
     * @return Manager
     */
    public function registerState(string $className, string $state, callable $setup): self
    {
        if (!$this->has($className)) {
            throw new InvalidArgumentException('Missing class definition');
        }

        $this->states[$className][$state] = $setup;

        return $this;
    }

    /**
     * @param string $className
     *
     * @return bool
     */
    public function has(string $className): bool
    {
        return isset($this->factory[$className]);
    }

    /**
     * @param string $className
     * @param string $state
     *
     * @return bool
     */
    public function hasState(string $className, string $state): bool
    {
        if(!isset($this->states[$className])){
            return false;
        }

        if(!isset($this->states[$className][$state])){
            return false;
        }

        return true;
    }

    /**
     * @param string $className
     *
     * @return Builder
     * @throws FactoryNotDefined
     */
    public function new(string $className): Builder
    {
        if (!$this->has($className)) {
            throw new FactoryNotDefined($className);
        }

        $states = [];
        if (!empty($this->states[$className])) {
            $states = $this->states[$className];
        }

        return new Builder($className, $this->factory[$className], $states);
    }
}
