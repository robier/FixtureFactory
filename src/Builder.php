<?php

declare(strict_types=1);

namespace Robier\ForgeObject;

use InvalidArgumentException;
use Robier\ForgeObject\Exception\StateNotDefined;

final class Builder
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var callable
     */
    private $setup;

    /**
     * @var array
     */
    private $states = [];

    /**
     * @var array
     */
    private $applyStates = [];

    /**
     * @param string $className
     * @param callable $setup
     * @param array $states
     */
    public function __construct(string $className, callable $setup, array $states)
    {
        $this->className = $className;
        $this->setup = $setup;
        $this->states = $states;
    }

    /**
     * Get array of available states
     *
     * @return string[]
     */
    public function availableStates(): array
    {
        return array_keys($this->states);
    }

    /**
     * Apply state to building object.
     *
     * @param string $state
     * @param string ...$states
     *
     * @return Builder
     * @throws StateNotDefined
     */
    public function state(string $state, string ...$states): self
    {
        array_unshift($states, $state);

        $missingStates = [];
        foreach ($states as $state) {
            if (!isset($this->states[$state])) {
                $missingStates[] = $state;
            }
        }

        if (!empty($missingStates)) {
            throw new StateNotDefined($this->className, ...$missingStates);
        }

        $this->applyStates = $states;

        return $this;
    }

    /**
     * Creates only one object. Setup function needs to have return type void or
     * it should return new instance of class this builder is for.
     *
     * @param null|callable $setup
     *
     * @return object
     */
    public function one(callable $setup = null): object
    {
        $this->validateCallableIfNeeded($setup);

        $objects = $this->make(1, $this->applyStates, $setup);

        return $objects[0];
    }

    /**
     * Creates multiple objects depending on $count property. Setup function needs to have return type void or
     * it should return new instance of class this builder is for.
     *
     * @param int $count
     * @param null|callable $setup
     *
     * @return Collection
     */
    public function many(int $count, callable $setup = null): Collection
    {
        $this->validateCallableIfNeeded($setup);

        $objects = $this->make($count, $this->applyStates, $setup);

        return new Collection(...$objects);
    }

    /**
     * Provided callable should not allow nullable return type, return type can be void or
     * class this builder is for.
     *
     * @param callable|null $setup
     * @throws Exception\NoReturnTypeOnCallable
     */
    private function validateCallableIfNeeded(callable $setup = null): void
    {
        if (null === $setup) {
            return;
        }

        $returnType = CallableReturnType::fromCallable($setup);

        if ($returnType->allowsNull()) {
            throw new InvalidArgumentException('Can not allow null');
        }

        if (!$returnType->isReturnTypeVoid() && !$returnType->isReturnType($this->className)) {
            throw new InvalidArgumentException(sprintf('Must return %s or void', $this->className));
        }
    }

    /**
     * Create object and apply states
     *
     * @param int $count
     * @param array $applyStates
     * @param callable|null $setup
     * @return object[]
     */
    private function make(int $count, array $applyStates, callable $setup = null): array
    {
        $objects = [];
        for ($i = 0; $i < $count; ++$i) {
            $object = call_user_func($this->setup);

            // we have an valid object, let's apply states to it
            foreach ($applyStates as $state) {
                $return = call_user_func($this->states[$state], $object);

                if (null === $return) {
                    // applying state can result in returning null, if developer just applied
                    // new state on provided object
                    continue;
                }

                $object = $return;
            }

            // apply provided setup
            if (null !== $setup) {
                $return = call_user_func($setup, $object);

                if ($return instanceof $this->className) {
                    $object = $return;
                }
            }

            $objects[] = $object;
        }

        return $objects;
    }
}
