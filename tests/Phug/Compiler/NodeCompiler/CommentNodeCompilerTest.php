<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\CommentNodeCompiler;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\CommentNodeCompiler
 */
class CommentNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     */
    public function testCompile()
    {
        $this->assertCompile(
            '<!-- Comment -->',
            '//Comment'
        );
        $this->assertCompile(
            '',
            '//- Comment'
        );
    }

    /**
     * @covers            ::<public>
     * @covers            \Phug\CompilerException::<public>
     *
     * @expectedException \Phug\CompilerException
     */
    public function testException()
    {
        $this->expectMessageToBeThrown(
            'Unexpected Phug\Parser\Node\ElementNode '.
            'given to comment compiler.'
        );

        $commentCompiler = new CommentNodeCompiler(new Compiler());
        $commentCompiler->compileNode(new ElementNode());
    }
}
