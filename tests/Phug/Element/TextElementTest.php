<?php

namespace Phug\Test\Element;

use Phug\Formatter\Element\TextElement;
use Phug\Util\TestCase;

/**
 * @coversDefaultClass \Phug\Formatter\Element\TextElement
 */
class TextElementTest extends TestCase
{
    /**
     * @covers ::<public>
     */
    public function testTextElement()
    {
        $text = new TextElement('foobar');

        self::assertNull($text->isEnd());
        $text->setEnd(true);
        self::assertTrue($text->isEnd());
        $text->setEnd(false);
        self::assertFalse($text->isEnd());
    }
}
