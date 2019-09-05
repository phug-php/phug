<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\ExpressionNodeCompiler;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\ExpressionNodeCompiler
 */
class ExpressionNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     */
    public function testCompile()
    {
        $this->assertCompile(
            '<p><?= $foo ?></p>',
            'p?!=$foo'
        );
        $this->assertCompile(
            '<p><?= htmlspecialchars((isset($foo) ? $foo : null)) ?></p>',
            'p=$foo'
        );
    }

    /**
     * @covers            ::<public>
     * @expectedException \Phug\CompilerException
     */
    public function testException()
    {
        $this->expectMessageToBeThrown(
            'Unexpected Phug\Parser\Node\ElementNode '.
            'given to expression compiler.'
        );

        $expressionCompiler = new ExpressionNodeCompiler(new Compiler());
        $expressionCompiler->compileNode(new ElementNode());
    }
}
