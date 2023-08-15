<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\AssignmentListNodeCompiler;
use Phug\Parser\Node\AssignmentListNode;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\AssignmentListNodeCompiler
 */
class AssignmentListNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     */
    public function testCompileNode()
    {
        $assignmentListCompiler = new AssignmentListNodeCompiler(new Compiler());

        self::assertNull($assignmentListCompiler->compileNode(new AssignmentListNode()));
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
            'given to assignment list compiler.'
        );

        $assignmentListCompiler = new AssignmentListNodeCompiler(new Compiler());
        $assignmentListCompiler->compileNode(new ElementNode());
    }
}
