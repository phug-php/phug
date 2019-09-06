<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\MixinNodeCompiler;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\MixinNodeCompiler
 */
class MixinNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers            ::<public>
     * @expectedException \Phug\CompilerException
     */
    public function testException()
    {
        $this->expectMessageToBeThrown(
            'Unexpected Phug\Parser\Node\ElementNode '.
            'given to mixin compiler.'
        );

        $mixinCompiler = new MixinNodeCompiler(new Compiler());
        $mixinCompiler->compileNode(new ElementNode());
    }

    /**
     * @group mixins
     * @covers ::<public>
     */
    public function testRecursion()
    {
        $this->assertRender(
            [
                '<ul>',
                '<li>1</li>',
                '<li>',
                '<ul>',
                '<li>2</li>',
                '<li>3</li>',
                '</ul>',
                '</li>',
                '<li><ul><li><ul><li>4</li></ul></li></ul></li>',
                '</ul>',
            ],
            [
                'mixin tree($items)'."\n",
                '  ul: each $item in $items'."\n",
                '    if is_array($item)'."\n",
                '      li: +tree($item)'."\n",
                '    else'."\n",
                '      li=$item'."\n",
                '+tree([1, [2, 3], [[4]]])',
            ]
        );
    }

    /**
     * @group mixins
     * @covers ::<public>
     */
    public function testRecursionWithBlock()
    {
        $this->assertRender(
            [
                'Hello<ul>',
                '<li>1</li>',
                '<li>',
                'Hello<ul>',
                '<li>2</li>',
                '<li>3</li>',
                '</ul>',
                '</li>',
                '<li>Hello<ul><li>Hello<ul><li>4</li></ul></li></ul></li>',
                '</ul>',
            ],
            [
                'mixin tree($items)'."\n",
                '  block'."\n",
                '  ul: each $item in $items'."\n",
                '    if is_array($item)'."\n",
                '      li: +tree($item)'."\n",
                '        block'."\n",
                '    else'."\n",
                '      li=$item'."\n",
                '+tree([1, [2, 3], [[4]]])'."\n".
                '  | Hello',
            ]
        );
    }

    /**
     * @group             mixins
     * @covers            \Phug\Compiler\NodeCompiler\BlockNodeCompiler ::compileAnonymousBlock
     * @expectedException \Phug\CompilerException
     */
    public function testAnonymousBlocksOutsideMixin()
    {
        $this->expectMessageToBeThrown(
            'Anonymous blocks are not allowed unless they are part of a mixin.'
        );
        $this->compiler->compile('block');
    }
}
