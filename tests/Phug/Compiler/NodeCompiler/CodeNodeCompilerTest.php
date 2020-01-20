<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\CodeNodeCompiler;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\CodeNodeCompiler
 */
class CodeNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     * @covers ::getCodeElement
     * @covers \Phug\Compiler\AbstractNodeCompiler::getTextChildren
     */
    public function testCompile()
    {
        $this->assertCompile(
            '<?php $foo = 4 ?>',
            '- $foo = 4'
        );
        $this->assertCompile(
            [
                '<?php if ($foo) { ?>',
                'Foo is true',
                '<div>Foo is true</div>',
                '<?php } else { ?>',
                'Foo is false',
                '<div>Foo is false</div>',
                '<?php } ?>',
            ],
            [
                '- if ($foo)'."\n",
                '  //- Foo is true'."\n",
                '  | Foo is true'."\n",
                '  div Foo is true'."\n",
                '- else'."\n",
                '  //- Foo is false'."\n",
                '  | Foo is false'."\n",
                '  div Foo is false',
            ]
        );
    }

    /**
     * @covers            ::<public>
     * @covers            ::getCodeElement
     * @expectedException \Phug\CompilerException
     */
    public function testException()
    {
        $this->expectMessageToBeThrown(
            'Unexpected Phug\Parser\Node\ElementNode '.
            'given to code compiler.'
        );

        $expressionCompiler = new CodeNodeCompiler(new Compiler());
        $expressionCompiler->compileNode(new ElementNode());
    }
}
