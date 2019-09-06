<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\WhileNodeCompiler;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\WhileNodeCompiler
 */
class WhileNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     * @covers \Phug\Compiler\NodeCompiler\AbstractStatementNodeCompiler::<public>
     * @covers \Phug\Compiler\NodeCompiler\WhileNodeCompiler::<public>
     */
    public function testCompile()
    {
        $this->assertCompile(
            [
                '<?php do { ?>',
                '<p>foo</p>',
                '<?php } while ($foo > 20); ?>',
            ],
            [
                'do'."\n",
                '  p foo'."\n",
                'while $foo > 20',
            ]
        );
        $this->assertCompile(
            [
                '<?php while ($foo > 20) { ?>',
                '<p>foo</p>',
                '<?php } ?>',
            ],
            [
                'while $foo > 20'."\n",
                '  p foo',
            ]
        );
        $this->assertCompile(
            [
                '<?php var x = 1; ?>',
                '<ul>',
                '<?php while (x < 10) { ?>',
                '<?php x++; ?>',
                '<li><?= htmlspecialchars(x) ?></li>',
                '<?php } ?>',
                '</ul>',
            ],
            [
                "- var x = 1;\n",
                "ul\n",
                "  while x < 10\n",
                "    - x++;\n",
                "    li= x\n",
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
            'given to while compiler.'
        );

        $whileCompiler = new WhileNodeCompiler(new Compiler());
        $whileCompiler->compileNode(new ElementNode());
    }

    /**
     * @covers            ::<public>
     * @covers            \Phug\Compiler\NodeCompiler\DoNodeCompiler::<public>
     * @expectedException \Phug\CompilerException
     */
    public function testDoAndWhileSeededException()
    {
        $this->expectMessageToBeThrown(
            'While statement cannot have children and come after a do statement.'
        );

        $compiler = new Compiler();
        $compiler->compile(
            'do'."\n".
            '  div A'."\n".
            'while a()'."\n".
            '  div B'
        );
    }
}
