<?php

namespace Phug;

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
        $this->invokables = $invokables;
    }
}
