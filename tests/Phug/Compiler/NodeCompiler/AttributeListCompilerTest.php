<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\AttributeListNodeCompiler;
use Phug\Parser\Node\AttributeListNode;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\AttributeListNodeCompiler
 */
class AttributeListCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     */
    public function testCompileNode()
    {
        $attributeListCompiler = new AttributeListNodeCompiler(new Compiler());

        self::assertNull($attributeListCompiler->compileNode(new AttributeListNode()));
    }

    /**
     * @covers            ::<public>
     * @expectedException \Phug\CompilerException
     */
    public function testException()
    {
        $this->expectMessageToBeThrown(
            'Unexpected Phug\Parser\Node\ElementNode '.
            'given to attribute list compiler.'
        );

        $attributeListCompiler = new AttributeListNodeCompiler(new Compiler());
        $attributeListCompiler->compileNode(new ElementNode());
    }
}
