<?php

namespace Phug\Test\Parser\Node;

use Phug\Parser\Node\ConditionalNode;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass \Phug\Parser\Node\ConditionalNode
 */
class ConditionalNodeTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     */
    public function testSubject()
    {
        $while = new ConditionalNode();

        $this->assertTrue($while->hasBooleanSubject());

        $while->setSubject('$foo == 1');

        $this->assertSame('$foo == 1', $while->getSubject());
    }
}
