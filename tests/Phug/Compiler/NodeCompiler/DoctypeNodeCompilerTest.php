<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\DoctypeNodeCompiler;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\DoctypeNodeCompiler
 */
class DoctypeNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     */
    public function testCompile()
    {
        $this->assertCompile(
            [
                '<!DOCTYPE html>',
            ],
            [
                'doctype html',
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
            'given to doctype compiler.'
        );

        $doctypeCompiler = new DoctypeNodeCompiler(new Compiler());
        $doctypeCompiler->compileNode(new ElementNode());
    }
}
