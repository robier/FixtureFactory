<?php

declare(strict_types=1);

namespace Robier\FixtureFactory;

use Robier\FixtureFactory\Exception\StateNotDefined;

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
     * @param null|callable $setup
     *
     * @return object
     */
    public function one(callable $setup = null): object
    {
        $objects = $this->make(1, $this->applyStates, $setup);

        return $objects[0];
    }

    /**
     * @param int $count
     * @param null|callable $setup
     *
     * @return Collection
     */
    public function many(int $count, callable $setup = null): Collection
    {
        $objects = $this->make($count, $this->applyStates, $setup);

        return new Collection(...$objects);
    }

    /**
     * Create object and apply states
     *
     * @param int $count
     * @param array $applyStates
     * @param callable|null $setup
     * @return array
     */
    private function make(int $count, array $applyStates, callable $setup = null): array
    {
        $objects = [];
        for ($i = 0; $i < $count; ++$i) {
            // @todo throw exception if null returned
            $object = call_user_func($this->setup);

            // we have an valid object, let's apply states to it
            foreach ($applyStates as $state) {
                $return = call_user_func($this->states[$state], $object);
                if ($return instanceof $this->className) {
                    $object = $return;
                }
                // @todo throw exception if different type is returned and !== null
            }

            // apply provided setup
            if (!empty($setup)) {
                $return = call_user_func($setup, $object);
                if ($return instanceof $this->className) {
                    $object = $return;
                }
                // @todo throw exception if different type is returned and !== null
            }
            $objects[] = $object;
        }

        return $objects;
    }
}
