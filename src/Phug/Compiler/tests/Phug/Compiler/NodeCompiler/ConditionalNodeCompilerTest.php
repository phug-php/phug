<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\ConditionalNodeCompiler;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass Phug\Compiler\NodeCompiler\ConditionalNodeCompiler
 */
class ConditionalNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     * @covers \Phug\Compiler\NodeCompiler\AbstractStatementNodeCompiler::<public>
     */
    public function testCompile()
    {
        $this->assertCompile(
            [
                '<?php if ((isset($foo) ? $foo : null) > 50) { ?>',
                '<p>Huge foo</p>',
                '<?php } elseif ((isset($foo) ? $foo : null) > 20) { ?>',
                '<p>Big foo</p>',
                '<?php } elseif ((isset($foo) ? $foo : null) > 10) { ?>',
                '<p>Medium foo</p>',
                '<?php } else { ?>',
                '<p>Small foo</p>',
                '<?php } ?>',
            ],
            [
                'if $foo > 50'."\n",
                '  p Huge foo'."\n",
                'else if $foo > 20'."\n",
                '  p Big foo'."\n",
                'elseif $foo > 10'."\n",
                '  p Medium foo'."\n",
                'else'."\n",
                '  p Small foo',
            ]
        );
        $this->assertCompile(
            [
                '<?php if (!((isset($foo) ? $foo : null) % 1)) { ?>',
                '<p>Even foo</p>',
                '<?php } else { ?>',
                '<p>Odd foo</p>',
                '<?php } ?>',
            ],
            [
                'unless $foo % 1'."\n",
                '  p Even foo'."\n",
                'else'."\n",
                '  p Odd foo',
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
            'given to conditional compiler.'
        );

        $conditionalCompiler = new ConditionalNodeCompiler(new Compiler());
        $conditionalCompiler->compileNode(new ElementNode());
    }
}
