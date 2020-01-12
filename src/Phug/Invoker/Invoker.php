<?php

namespace Phug;

use Phug\Event\ListenerQueue;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

class Invoker
{
    /**
     * List of callbacks grouped by type.
     *
     * @var ListenerQueue[]
     */
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
     * @return ListenerQueue[]
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

            $parameter = static::getCallbackType($invokable);

            if (!is_string($parameter)) {
                throw new RuntimeException(
                    'Passed callback #'.($index + 1).
                    ' should have at least 1 argument and this first argument must have a typehint.'
                );
            }

            if (!isset($this->invokables[$parameter])) {
                $this->invokables[$parameter] = new ListenerQueue();
            }

            $this->invokables[$parameter]->insert($invokable, 0);
        }
    }

    /**
     * Remove callbacks from the list.
     *
     * @param callable[] $invokables list of callbacks
     */
    public function remove(array $invokables)
    {
        $this->invokables = array_filter(array_map(function (ListenerQueue $queue) use (&$invokables) {
            $filteredQueue = new ListenerQueue();

            foreach ($queue as $invokable) {
                if (!in_array($invokable, $invokables)) {
                    $filteredQueue->insert($invokable, 0);
                }
            }

            return $filteredQueue;
        }, $this->invokables), function (ListenerQueue $queue) {
            return $queue->count();
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

    /**
     * Invoke callbacks that match the passed event.
     *
     * @param object $event instance of callback input.
     *
     * @return array
     */
    public function invoke($event)
    {
        $invocations = [];

        foreach ($this->invokables as $type => $invokables) {
            if (is_a($event, $type)) {
                foreach ($invokables as $invokable) {
                    $invocations[] = $invokable($event);
                }
            }
        }

        return $invocations;
    }

    /**
     * Return the typehint as string of the first argument of a given callback or null if not typed.
     *
     * @param callable $invokable closure or callable
     *
     * @throws ReflectionException
     *
     * @return string|null
     */
    public static function getCallbackType(callable $invokable)
    {
        $reflection = is_array($invokable)
            ? new ReflectionMethod($invokable[0], $invokable[1])
            : new ReflectionFunction($invokable);
        $parameter = $reflection->getParameters();

        if (count($parameter)) {
            $parameter = $parameter[0]->getType();
        }

        return $parameter instanceof ReflectionNamedType ? $parameter->getName() : null;
    }
}
