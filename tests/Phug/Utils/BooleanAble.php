<?php

namespace Phug\Test\Utils;

class BooleanAble
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __toBoolean()
    {
        if (is_bool($this->value)) {
            return $this->value;
        }

        return $this->value-- > 0;
    }
}
