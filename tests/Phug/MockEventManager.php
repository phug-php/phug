<?php

namespace Phug\Test;

use Phug\EventManagerInterface;
use Phug\EventManagerTrait;

class MockEventManager implements EventManagerInterface
{
    use EventManagerTrait;

    public function dumpListeners()
    {
        $events = [];
        foreach ($this->getEventListeners() as $name => $queue) {
            $events[$name] = array_values(iterator_to_array(clone $queue));
        }

        return $events;
    }
}
