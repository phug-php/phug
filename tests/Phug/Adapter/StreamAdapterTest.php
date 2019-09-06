<?php

namespace Phug\Test\Adapter;

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
    }
}
