<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\VariableNodeCompiler;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\VariableNodeCompiler
 */
class VariableNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     */
    public function testCompile()
    {
        $this->assertCompile('<?php $answer=42 ?>', '$answer != 42');
        $this->assertCompile(
            '<?php $answer=htmlspecialchars($foo) ?>',
            '$answer ?= $foo'
        );
    }

    /**
     * @covers            ::<public>
     * @expectedException \Phug\CompilerException
     */
    public function testExpressionException()
    {
        $this->expectMessageToBeThrown(
            'Variable should be followed by exactly 1 expression.'
        );

        $this->assertCompile('', '$answer');
    }

    /**
     * @covers            ::<public>
     * @expectedException \Phug\CompilerException
     */
    public function testException()
    {
        $this->expectMessageToBeThrown(
            'Unexpected Phug\Parser\Node\ElementNode '.
            'given to variable compiler.'
        );

        $variableCompiler = new VariableNodeCompiler(new Compiler());
        $variableCompiler->compileNode(new ElementNode());
    }
}
