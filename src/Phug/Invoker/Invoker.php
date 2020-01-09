<?php

namespace Phug;

use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;

class Invoker
{
    private $invokables;

    /**
     * Event constructor.
     *
     * @param callable[] $invokables
     */
    public function __construct(array $invokables)
    {
        $this->invokables = [];

        foreach ($invokables as $invokable) {
            $reflection = is_array($invokable)
                ? new ReflectionMethod($invokable[0], $invokable[1])
                : new ReflectionFunction($invokable);
            $parameter = $reflection->getParameters();

            if (count($parameter)) {
                $parameter = $parameter[0];
            }

            if ($parameter->hasType()) {
                $parameter = $parameter->getType()->getName();
            }

            if (!is_string($parameter)) {
                throw new RuntimeException('Error');
            }

            $this->invokables[$parameter] = $invokable;
        }
    }

    public function invoke($event)
    {
        foreach ($this->invokables as $type => $invokable) {}
    }
}
