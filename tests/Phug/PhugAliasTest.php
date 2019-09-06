<?php

namespace Phug\Test;

/**
 * @coversDefaultClass \Phug
 */
class PhugAliasTest extends AbstractPhugTest
{
    /**
     * @covers ::getRenderer
     * @covers ::renderFile
     */
    public function testRenderFile()
    {
        self::assertSame(
            '<p>Hello world!</p>',
            \Phug::renderFile(__DIR__.'/../views/test.pug')
        );
    }
}
