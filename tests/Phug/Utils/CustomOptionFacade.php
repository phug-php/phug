<?php

namespace Phug\Test\Utils;

use Phug\Phug;

class CustomOptionFacade extends Phug
{
    private static $options1 = [];

    private static $options2 = [];

    public static function setOptions1($options)
    {
        static::$options1 = $options;
    }

    public static function setOptions2($options)
    {
        static::$options2 = $options;
    }

    public static function getOption($name)
    {
        return isset(static::$options1[$name]) ? static::$options1[$name] : null;
    }

    public static function getOptions(array $options = [])
    {
        return static::$options2;
    }

    public static function hasOption()
    {
        return true;
    }
}
