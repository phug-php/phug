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
}
