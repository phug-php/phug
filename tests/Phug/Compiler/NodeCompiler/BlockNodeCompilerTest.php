<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\BlockNodeCompiler;
use Phug\Parser\Node\BlockNode;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;
use Phug\Test\Compiler\NodeCompiler\BlockNodeCompilerTest\TestBlockNodeCompiler;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\BlockNodeCompiler
 */
class BlockNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     * @covers ::compileNamedBlock
     * @covers \Phug\Compiler\Element\BlockElement::<public>
     */
    public function testBlock()
    {
        $this->assertCompile(
            [
                '<div>',
                '<p>Foo</p>',
                '</div>',
            ],
            [
                "div\n",
                "  block place\n",
                '    p Foo',
            ]
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
            'given to block compiler.'
        );

        $blockCompiler = new BlockNodeCompiler(new Compiler());
        $blockCompiler->compileNode(new ElementNode());
    }

    /**
     * @covers            \Phug\Compiler::compileBlocks
     * @expectedException \Phug\CompilerException
     */
    public function testCompileBlocksException()
    {
        $this->expectMessageToBeThrown(
            'Unexpected block for the name foo'
        );

        require_once __DIR__.'/BlockNodeCompilerTest/TestBlockNodeCompiler.php';
        $compiler = new Compiler([
            'node_compilers' => [
                BlockNode::class => TestBlockNodeCompiler::class,
            ],
        ]);
        $compiler->compile('block foo');
    }
}
