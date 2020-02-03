<?php

namespace Phug\Test\Adapter;

use DateTime;
use Phug\Renderer;
use Phug\Renderer\Adapter\Stream\Template;
use Phug\Renderer\Adapter\StreamAdapter;
use Phug\Test\AbstractRendererTest;

/**
 * @coversDefaultClass \Phug\Renderer\Adapter\StreamAdapter
 */
class StreamAdapterTest extends AbstractRendererTest
{
    /**
     * @covers ::<public>
     * @covers ::setRenderingFile
     * @covers \Phug\Renderer\Adapter\Stream\Template::<public>
     */
    public function testRender()
    {
        $renderer = new Renderer([
            'adapter_class_name' => StreamAdapter::class,
        ]);

        self::assertSame('<p>Hello</p>', $renderer->render('p=$message', [
            'message' => 'Hello',
        ]));

        $stream = new Template();
        self::assertTrue(is_int($stream->stream_tell()));
        self::assertTrue(is_array($stream->url_stat('a', 'b')));
        self::assertTrue($stream->stream_set_option('a', 'b', 'c'));
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Renderer\AbstractAdapter::execute
     */
    public function testThisOverride()
    {
        $renderer = new Renderer([
            'adapter_class_name' => StreamAdapter::class,
        ]);

        self::assertSame('<p>2020-02</p>', $renderer->render('p=$this->format("Y-m")', [
            'this' => new DateTime('2020-02-05'),
        ]));
    }
}
