<?php

namespace Phug\Test;

use PHPUnit\Framework\TestCase;

abstract class AbstractDependencyInjectionTest extends TestCase
{
    public static function assertSameLines($expected, $actual)
    {
        foreach (['expected', 'actual'] as $var) {
            if (is_array($$var)) {
                $$var = implode(PHP_EOL, $$var);
            }
            $$var = str_replace(PHP_EOL, "\n", $$var);
            $$var = array_filter(array_map(function ($line) {
                return ltrim($line);
            }, explode("\n", $$var)));
            $$var = implode(PHP_EOL, $$var);
        }

        self::assertSame($expected, $actual);
    }
}
