<?php

namespace Phug\Test\Parser\Node;

use Phug\Parser\Node\FilterNode;
use Phug\Parser\Node\ImportNode;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\Node\FilterNode
 */
class FilterNodeTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     */
    public function testImport()
    {
        $text = new FilterNode();

        self::assertNull($text->getImport());

        $import = new ImportNode();
        $text->setImport($import);

        self::assertSame($import, $text->getImport());
    }
}
