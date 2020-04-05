<?php

namespace Phug\Test\Parser\Node;

use Phug\Parser\Node\WhileNode;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass \Phug\Parser\Node\WhileNode
 */
class WhileNodeTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     */
    public function testSubject()
    {
        $while = new WhileNode();

        $this->assertTrue($while->hasBooleanSubject());

        $while->setSubject('$foo == 1');

        $this->assertSame('$foo == 1', $while->getSubject());
    }
}
