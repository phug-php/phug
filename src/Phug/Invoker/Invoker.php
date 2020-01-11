<?php

namespace Phug;

use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

class Invoker
{
    private $invokables;

    /**
     * Event constructor.
     *
     * @param callable[] $invokables list of callbacks to start with
     *
     * @throws ReflectionException
     */
    public function __construct(array $invokables)
    {
        $this->reset();
        $this->add($invokables);
    }

    /**
     * Remove all callbacks from the list.
     */
    public function reset()
    {
        $this->invokables = [];
    }

    /**
     * Get all callbacks from the list.
     *
     * @return callable[]
     */
    public function all()
    {
        return $this->invokables;
    }

    /**
     * Add callbacks from the list.
     *
     * @param callable[] $invokables list of callbacks
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function add(array $invokables)
    {
        foreach ($invokables as $index => $invokable) {
            if (!is_callable($invokable)) {
                throw new RuntimeException('The #'.($index + 1).' value is not callable.');
            }

            $reflection = is_array($invokable)
                ? new ReflectionMethod($invokable[0], $invokable[1])
                : new ReflectionFunction($invokable);
            $parameter = $reflection->getParameters();

            if (count($parameter)) {
                $parameter = $parameter[0]->getType();
            }

            $parameter = $parameter instanceof ReflectionNamedType ? $parameter->getName() : null;

            if (!is_string($parameter)) {
                throw new RuntimeException('Passed callback #'.($index + 1).' should at least 1 argument and this first argument must have a typehint.');
            }

            if (isset($this->invokables[$parameter])) {
                throw new RuntimeException('Passed callback #'.($index + 1).' tried to use a typehint '.$parameter.' already used.');
            }

            $this->invokables[$parameter] = $invokable;
        }
    }

    /**
     * Remove callbacks from the list.
     *
     * @param callable[] $invokables list of callbacks
     */
    public function remove(array $invokables)
    {
        $this->invokables = array_filter($this->invokables, function ($invokable) use (&$invokables) {
            return !in_array($invokable, $invokables);
        });
    }

    /**
     * Remove callbacks from the list by a given class/interface name.
     *
     * @param string $type exact type of a callback argument.
     */
    public function removeByType($type)
    {
        unset($this->invokables[$type]);
    }

    public function invoke($event)
    {
        $invocations = [];

        foreach ($this->invokables as $type => $invokable) {
            if (is_a($event, $type)) {
                $invocations++;
                $invokable($event);
            }
        }

        return $invocations;
    }
}
