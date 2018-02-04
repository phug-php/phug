<?php

namespace Phug\Test\Util;

use Phug\Phug;

class CustomFacade extends Phug
{
    private static $output;

    public static function setOutput($output)
    {
        static::$output = $output;
    }

    public static function displayFile($path, array $parameters = [], array $options = [])
    {
        echo static::$output;
    }
}
