<?php

namespace Phug\Test\Profiler;

use JsPhpize\JsPhpizePhug;
use PHPUnit\Framework\TestCase;
use Phug\Renderer;
use Phug\Renderer\Event\HtmlEvent;
use Phug\Renderer\Event\RenderEvent;
use Phug\Renderer\Profiler\Dump;

/**
 * @coversDefaultClass \Phug\Renderer\Profiler\Dump
 */
class DumpTest extends TestCase
{
    /**
     * @covers ::<public>
     * @covers ::dumpValue
     * @covers ::dumpObject
     * @covers ::dumpArray
     * @covers ::getExposedProperties
     */
    public function testDump()
    {
        $dump = function ($value) {
            return (new Dump($value))->dump();
        };

        self::assertSame('1', $dump(1));
        self::assertSame('true', $dump(true));
        self::assertSame('NULL', $dump(null));
        self::assertSame(implode("\n", [
            'array (2) [',
            "  0 => 'string'",
            "  'key' => Phug\Renderer\Event\RenderEvent {",
            "    Method => 'c'",
            "    Input => 'a'",
            '    Parameters => array (1) [',
            "      0 => 'd'",
            '    ]',
            "    Path => 'b'",
            "    Name => 'renderer.render'",
            '    Target => NULL',
            '    Params => array []',
            '  }',
            ']',
        ]), $dump([
            'string',
            'key' => new RenderEvent('a', 'b', 'c', ['d']),
        ]));

        $array = new \ArrayObject();
        for ($i = 0; $i < 100; $i++) {
            $array[] = $i;
        }

        self::assertSame(implode("\n", [
            'ArrayObject (100) [',
            '  0 => 0',
            '  1 => 1',
            '  2 => 2',
            '  3 => 3',
            '  4 => 4',
            '  5 => 5',
            '  6 => 6',
            '  7 => 7',
            '  8 => 8',
            '  9 => 9',
            '  10 => 10',
            '  11 => 11',
            '  12 => 12',
            '  13 => 13',
            '  14 => 14',
            '  ...',
            ']',
        ]), $dump($array));

        $jsPhpizeDump = $dump(new JsPhpizePhug(new Renderer()));

        if (defined('HHVM_VERSION')) {
            self::assertContains('$JsPhpize\\JsPhpizePhug::getEventListeners', $jsPhpizeDump);

            $jsPhpizeDump = preg_replace(
                '/Closure\$.+?\d+\s*\{/',
                'Closure {',
                $jsPhpizeDump
            );
        }

        self::assertSame(implode("\n", [
            'JsPhpize\JsPhpizePhug {',
            '  EventListeners => array (1) [',
            '    \'compiler.output\' => Closure {',
            '    }',
            '  ]',
            '}',
        ]), $jsPhpizeDump);

        $event = new RenderEvent('a', 'b', 'c', ['d']);

        self::assertSame(implode("\n", [
            'Phug\Renderer\Event\HtmlEvent {',
            '  RenderEvent => renderer.render event',
            '  Buffer => \'a+...',
        ]), preg_replace(
            '/a+/',
            'a+',
            $dump(new HtmlEvent($event, null, str_repeat('a', 0x80000), null))
        ));
    }
}
