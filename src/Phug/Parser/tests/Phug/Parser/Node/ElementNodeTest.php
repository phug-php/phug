<?php

namespace Phug\Test\Parser\Node;

use Phug\Parser\Node\AttributeNode;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\Node\ElementNode
 */
class ElementNodeTest extends AbstractParserTest
{
    /**
     * @covers ::getAttribute
     */
    public function testGetAttribute()
    {
        $tag = new ElementNode();
        $id = new AttributeNode();
        $id->setName('id');
        $id->setValue('foo');
        $tag->getAttributes()->attach($id);
        $class = new AttributeNode();
        $class->setName('class');
        $class->setValue('bar');
        $tag->getAttributes()->attach($class);
        $src = new AttributeNode();
        $src->setName('src');
        $src->setValue('img');
        $tag->getAttributes()->attach($src);

        self::assertSame('foo', $tag->getAttribute('id'));
        self::assertSame('img', $tag->getAttribute('src'));
        self::assertSame('bar', $tag->getAttribute('class'));
        self::assertNull($tag->getAttribute('alt'));
    }
}
