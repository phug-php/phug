<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\TextNodeCompiler;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\TextNodeCompiler
 */
class TextNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     */
    public function testText()
    {
        $this->assertCompile('Hello', '| Hello');
        $this->assertCompile(
            [
                '<pre>',
                'article'."\n",
                '  p Name',
                '</pre>',
            ],
            [
                'pre.'."\n",
                '  article'."\n",
                '    p Name',
            ]
        );
        $this->assertCompile(
            [
                '<p>article'."\n",
                '  <p>Name</p></p>',
            ],
            [
                'p.'."\n",
                '  article'."\n",
                '    #[p Name]',
            ]
        );
        $this->assertCompile(
            [
                '<ul>'."\n",
                '  <li>foo</li>'."\n",
                '  <li>bar</li>'."\n",
                '  <li>baz</li>'."\n",
                '</ul>',
            ],
            [
                '<ul>'."\n",
                '  <li>foo</li>'."\n",
                '  <li>bar</li>'."\n",
                '  <li>baz</li>'."\n",
                '</ul>',
            ]
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
            'Unexpected Phug\Parser\Node\ElementNode '.
            'given to text compiler.'
        );

        $textCompiler = new TextNodeCompiler(new Compiler());
        $textCompiler->compileNode(new ElementNode());
    }
}
