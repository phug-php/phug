<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\CaseNodeCompiler;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\CaseNodeCompiler
 */
class CaseNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     * @covers \Phug\Compiler\NodeCompiler\WhenNodeCompiler::<public>
     * @covers \Phug\Compiler\NodeCompiler\AbstractStatementNodeCompiler::<public>
     */
    public function testCompile()
    {
        $this->assertCompile(
            [
                '<?php switch ($foo) { ?>',
                '<?php case 0: ?>',
                '<?php case 1: ?>',
                '<p>Hello</p>',
                '<?php break; ?>',
                '<?php default: ?>',
                '<p>Bye</p>',
                '<?php } ?>',
            ],
            [
                'case $foo'."\n",
                '  when 0'."\n",
                '  when 1'."\n",
                '    p Hello'."\n",
                '  default'."\n",
                '    p Bye',
            ]
        );
        $this->assertCompile(
            [
                '<!DOCTYPE html>',
                '<html>',
                '<body>',
                '<?php $s = "this" ?>',
                '<?php switch ($s) { ?>',
                '<?php case "this": ?>',
                '<p>It\'s this!</p>',
                '<?php break; ?>',
                '<?php case "that": ?>',
                '<p>It\'s that!</p>',
                '<?php break; ?>',
                '<?php } ?>',
                '</body>',
                '</html>',
            ],
            [
                'doctype html'."\n",
                'html'."\n",
                '  body'."\n",
                '   - $s = "this"'."\n".
                '   case $s'."\n".
                '     //- Comment'."\n",
                '     when "this"'."\n",
                '       p It\'s this!'."\n",
                '     when "that"'."\n",
                '       p It\'s that!'."\n",
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
            'given to case compiler.'
        );

        $caseCompiler = new CaseNodeCompiler(new Compiler());
        $caseCompiler->compileNode(new ElementNode());
    }
}
