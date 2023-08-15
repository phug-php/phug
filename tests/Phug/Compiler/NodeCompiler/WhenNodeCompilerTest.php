<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\WhenNodeCompiler;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\WhenNodeCompiler
 */
class WhenNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers            ::<public>
     *
     * @expectedException \Phug\CompilerException
     */
    public function testException()
    {
        $this->expectMessageToBeThrown(
            'Unexpected Phug\Parser\Node\ElementNode '.
            'given to when compiler.'
        );

        $whenCompiler = new WhenNodeCompiler(new Compiler());
        $whenCompiler->compileNode(new ElementNode());
    }
}
