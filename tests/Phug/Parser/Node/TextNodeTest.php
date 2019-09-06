<?php

namespace Phug\Test\Parser\Node;

use Phug\Parser\Node\TextNode;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\Node\TextNode
 */
class TextNodeTest extends AbstractParserTest
{
    /**
     * @covers ::setLevel
     * @covers ::getLevel
     * @covers ::setIndent
     * @covers ::getIndent
     */
    public function testLevel()
    {
        $text = new TextNode();

        self::assertNull($text->getLevel());

        $text->setLevel(2);

        self::assertSame(2, $text->getLevel());

        $text->setIndent('  ');

        self::assertSame('  ', $text->getIndent());
    }
}
