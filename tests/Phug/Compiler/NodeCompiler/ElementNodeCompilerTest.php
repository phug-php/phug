<?php

namespace Phug\Test\Compiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\ElementNodeCompiler;
use Phug\Parser\Node\DoNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\ElementNodeCompiler
 */
class ElementNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     */
    public function testCompile()
    {
        $this->assertCompile('<section><input /></section>', 'section: input');
        $this->assertCompile('<section></section>', 'section');
        $this->assertCompile('<section></section>', '#{"section"}');
        $this->assertCompile('<section />', 'section/');
    }

    /**
     * @covers ::<public>
     */
    public function testExpansionCompile()
    {
        $this->assertRender(
            '<ul><li class="list-item"><div class="foo"><div id="bar">baz</div></div></li></ul>',
            "ul\n  li.list-item: .foo: #bar baz"
        );
    }

    /**
     * @covers            ::<public>
     *
     * @expectedException \Phug\CompilerException
     */
    public function testException()
    {
        $this->expectMessageToBeThrown(
            'Unexpected Phug\Parser\Node\DoNode '.
            'given to element compiler.'
        );

        $elementCompiler = new ElementNodeCompiler(new Compiler());
        $elementCompiler->compileNode(new DoNode());
    }
}
