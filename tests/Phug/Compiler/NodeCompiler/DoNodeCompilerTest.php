<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\DoNodeCompiler;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\DoNodeCompiler
 */
class DoNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers            ::<public>
     * @expectedException \Phug\CompilerException
     */
    public function testException()
    {
        $this->expectMessageToBeThrown(
            'Unexpected Phug\Parser\Node\ElementNode '.
            'given to do compiler.'
        );

        $doCompiler = new DoNodeCompiler(new Compiler());
        $doCompiler->compileNode(new ElementNode());
    }
}
