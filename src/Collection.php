<?php

declare(strict_types=1);

namespace Robier\FixtureFactory;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

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
        return $this->items[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if(!is_object($value)){
            // @todo throw library exception
            throw new \InvalidArgumentException('Not object');
        }

        if(!is_int($offset) && !empty($offset)){
            // @todo throw library exception
            throw new \InvalidArgumentException('Not integer');
        }

        if(empty($offset)){
            $this->add($value);
            return;
        }

        if($offset <= count($this->items)){
            array_splice($this->items, $offset, 0, $value);
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
     * Add new item to collection
     *
     * @param object $item
     * @return Collection
     */
    public function add(object $item): self
    {
        $this->items[] = $item;

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
        foreach ($this->items as $item) {
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
     */
    public function first(): object
    {
        return $this->items[0];
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
