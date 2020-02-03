<?php

namespace Phug\Test\Utils;

class Context
{
    private $input;

    public function __construct($input)
    {
        $this->input = $input;
    }

    public function getInput()
    {
        return $this->input;
    }

    public function __toString()
    {
        return '__toString:'.$this->input;
    }
}
