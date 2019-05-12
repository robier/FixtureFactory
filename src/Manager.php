<?php

declare(strict_types=1);

namespace Robier\ForgeObject;

use InvalidArgumentException;
use Robier\ForgeObject\Exception\FactoryNotDefined;
use Robier\ForgeObject\Exception\NoReturnTypeOnCallable;

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
     * @throws \ReflectionException
     */
    public function register(string $className, callable $setup): self
    {
        if ($this->has($className)) {
            throw new \InvalidArgumentException(sprintf('Forge factory already exists for class %s', $className));
        }

        // validate setup callable
        try {
            $setupReturnType = CallableReturnType::fromCallable($setup);
        } catch (NoReturnTypeOnCallable $e) {
            throw new \InvalidArgumentException(sprintf('Return type missing on setup callable on %s class', $className));
        }

        if ($setupReturnType->isReturnTypeVoid()) {
            throw new \InvalidArgumentException(sprintf('Setup callable provided for class %s can not allow void as return type', $className));
        }

        if ($setupReturnType->allowsNull()) {
            throw new \InvalidArgumentException(sprintf('Setup callable provided for class %s can not allow null as return type', $className));
        }

        if (!$setupReturnType->isReturnType($className)) {
            throw new \InvalidArgumentException(sprintf('Setup callable provided for class %s does not provide mentioned class', $className));
        }

        $this->factory[$className] = $setup;

        return $this;
    }

    /**
     * @param string $className
     * @param string $state
     * @param array|callable $setup
     *
     * @return Manager
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function registerState(string $className, string $state, callable $setup): self
    {
        if (!$this->has($className)) {
            throw new InvalidArgumentException(
                sprintf('Class %s is not registered so state %s for that class can not be registered', $className, $state)
            );
        }

        // validate setup callable
        try {
            $setupReturnType = CallableReturnType::fromCallable($setup);
        } catch (NoReturnTypeOnCallable $e) {
            throw new \InvalidArgumentException(sprintf('Return type missing on state %s for %s class, set `void` if function does not return anything', $state, $className));
        }

        if (!$setupReturnType->isReturnTypeVoid() && !$setupReturnType->isReturnType($className)) {
            throw new \InvalidArgumentException(
                sprintf('Return type of state %s for class %s does not match %s', $state, $className, $setupReturnType->getReturnType())
            );
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
        if (!isset($this->states[$className])) {
            return false;
        }

        if (!isset($this->states[$className][$state])) {
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
