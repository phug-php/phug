<?php

namespace Phug\Test;

use Phug\Util\TestCase;

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

    protected function expectMessageToBeThrown($type, $message, $code = null)
    {
        if (method_exists($this, 'expectExceptionMessage')) {
            $this->expectException($type);
            $this->expectExceptionMessage($message);

            if ($code !== null) {
                $this->expectExceptionCode($code);
            }

            return;
        }

        $this->setExpectedException($type, $message, $code);
    }
}
