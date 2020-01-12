<?php

namespace Phug\Util;

use Generator;
use IteratorAggregate;
use Traversable;

class Collection implements IteratorAggregate
{
    /**
     * @var iterable
     */
    private $traversable;

    public function __construct($value)
    {
        $this->traversable = static::isIterable($value) ? $value : [$value];
    }

    /**
     * Polyfill of is_iterable.
     *
     * @see https://www.php.net/manual/en/function.is-iterable.php
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isIterable($value)
    {
        return is_array($value) || (is_object($value) && $value instanceof Traversable);
    }

    /**
     * Retrieve an external iterator.
     *
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Traversable An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>
     */
    public function getIterator()
    {
        return $this->traversable instanceof Traversable ? $this->traversable : $this->getGenerator();
    }

    /**
     * Get input data as iterable value.
     *
     * @return iterable
     */
    public function getIterable()
    {
        return $this->traversable;
    }

    /**
     * Get input data as a generator of values.
     *
     * @return Generator
     */
    public function getGenerator()
    {
        foreach ($this->traversable as $value) {
            yield $value;
        }
    }

    /**
     * Return the result of the passed function for each item of the collection.
     *
     * @return static
     */
    public function map($callback)
    {
        return new static($this->yieldMap($callback));
    }

    /**
     * Return the result of the passed function for each item of the collection.
     * If item is a generator (using yield from inside the function), then it will
     * flatten the yielded values for one more level.
     *
     * @return static
     */
    public function flatMap($callback)
    {
        return new static($this->yieldFlatMap($callback));
    }

    /**
     * Return the result of the passed function for each item of the collection.
     *
     * @return Generator
     */
    public function yieldMap($callback)
    {
        foreach ($this->traversable as $value) {
            yield $callback($value);
        }
    }

    /**
     * Return the result of the passed function for each item of the collection.
     * If item is a generator (using yield from inside the function), then it will
     * flatten the yielded values for one more level.
     *
     * @return Generator
     */
    public function yieldFlatMap($callback)
    {
        foreach ($this->traversable as $value) {
            $result = $callback($value);

            if ($result instanceof Generator) {
                foreach ($result as $item) {
                    yield $item;
                }

                continue;
            }

            yield $result;
        }
    }
}
