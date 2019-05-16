<?php

declare(strict_types=1);

namespace Robier\ForgeObject;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use LogicException;

final class Collection implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * @param object ...$items
     */
    public function __construct(object ...$items)
    {
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (!is_object($value)) {
            throw new InvalidArgumentException('Provided value is not a object');
        }

        if (null !== $offset && !is_int($offset)) {
            throw new InvalidArgumentException('Provided offset is not a integer');
        }

        if (empty($offset)) {
            $this->add($value);
            return;
        }

        if ($offset <= count($this->items)) {
            array_splice($this->items, $offset, 0, [$value]);
            return;
        }

        $this->items[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Get item from collection
     *
     * @param int $offset
     * @return object
     * @throws InvalidArgumentException
     */
    public function get(int $offset): object
    {
        if ($this->has($offset)) {
            return $this->items[$offset];
        }

        throw new InvalidArgumentException(sprintf('Item with offset %d does not exist in collection', $offset));
    }

    /**
     * Check if item with offset exists in collection
     *
     * @param int $offset
     * @return bool
     */
    public function has(int $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Add new item to collection
     */
    public function add(object $item, object ...$items): self
    {
        array_unshift($items, $item);

        $this->items = array_merge($this->items, $items);

        return $this;
    }

    /**
     * Creates new Collection containing items from 2 Collection objects
     *
     * @param self $collection
     *
     * @return Collection
     */
    public function merge(self $collection): self
    {
        return new static(...$this->items, ...$collection);
    }

    /**
     * Apply function to all items in collection
     *
     * @param callable $function
     *
     * @return Collection
     */
    public function apply(callable $function): self
    {
        /**
         * @todo maybe add additional parameter for applying $function on certain type, as one collection can have a
         * more than one type if two collections are merged
         * @todo validate callable
         */

        foreach ($this->items as &$item) {
            call_user_func($function, $item);
        }

        return $this;
    }

    /**
     * Get one random element from collection
     *
     * @return object
     */
    public function random(): object
    {
        $key = array_rand($this->items);

        return $this->items[$key];
    }

    /**
     * Gets first item from collection
     *
     * @return object
     * @throws LogicException
     */
    public function first(): object
    {
        $value = reset($this->items);

        if (false === $value) {
            throw new LogicException('Collection can not return first item as it\'s empty');
        }

        return $value;
    }

    /**
     * Get last item from collection
     *
     * @return object
     * @throws LogicException
     */
    public function last(): object
    {
        $value = end($this->items);

        if (false === $value) {
            throw new LogicException('Collection can not return last item as it\'s empty');
        }

        return $value;
    }

    /**
     * Filter collection by given function and returns new Collection
     *
     * @param callable $function
     * @return Collection
     */
    public function filter(callable $function): self
    {
        $items = array_filter($this->items, $function);

        return new self(...$items);
    }
}
