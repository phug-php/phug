<?php

namespace Phug\Test\Compiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\KeywordNodeCompiler;
use Phug\Formatter\Element\KeywordElement;
use Phug\Parser\Node\DoNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\KeywordNodeCompiler
 */
class KeywordNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     */
    public function testCompile()
    {
        $this->compiler->setOption(['keywords', 'x:form'], function ($args, KeywordElement $keyword, $name) {
            return [
                'begin' => '<form name="'.$name.'" data-children-count="'.$keyword->getChildCount().'">'.
                    '<input type="hidden" name="token" value="'.$args.'" />',
                'end'   => '</form>',
            ];
        });
        $this->assertRender(
            '<section><form name="x:form" data-children-count="2">'.
            '<input type="hidden" name="token" value="FA4E23C" />'.
            '<input name="name" value="Bob" />'.
            '<input name="email" value="bob@bob" />'.
            '</form></section>',
            'section: x:form FA4E23C'."\n".
            '  input(name="name" value="Bob")'."\n".
            '  input(name="email" value="bob@bob")'
        );

        $this->compiler->setOption(['keywords', 'foobar'], function () {
            return 'foobar';
        });
        $this->assertRender(
            '<div><div><section>foobar</section></div></div>',
            'div'."\n".
            '  div: section: foobar'
        );
    }

    /**
     * @covers            ::<public>
     * @expectedException \Phug\CompilerException
     */
    public function testException()
    {
        $this->expectMessageToBeThrown(
            'Unexpected Phug\Parser\Node\DoNode '.
            'given to keyword compiler.'
        );

        $elementCompiler = new KeywordNodeCompiler(new Compiler());
        $elementCompiler->compileNode(new DoNode());
    }
}
