<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\EachNodeCompiler;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\EachNodeCompiler
 */
class EachNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     * @covers ::compileLoop
     * @covers ::getScopeVariablesDump
     * @covers ::getScopeVariablesRestore
     * @covers ::scopeEachVariables
     * @covers \Phug\Compiler\NodeCompiler\AbstractStatementNodeCompiler::wrapStatement
     */
    public function testCompile()
    {
        $this->assertCompile(
            [
                '<?php foreach ($items as $item) { ?>',
                '<p><?= $item ?></p>',
                '<?php } ?>',
            ],
            [
                'each $item in $items'."\n",
                '  p?!=$item',
            ],
            [
                'scope_each_variables' => false,
            ]
        );
        $this->assertCompile(
            [
                '<?php $__pug_temp_empty = true; foreach ($items as $item) { ?>',
                '<?php $__pug_temp_empty = false ?>',
                '<p><?= $item ?></p>',
                '<?php } ?>',
                '<?php if ((isset($__pug_temp_empty) ? $__pug_temp_empty : null)) { ?>',
                '<p>no items</p>',
                '<?php } ?>',
            ],
            [
                'each $item in $items'."\n",
                '  p?!=$item'."\n",
                'else'."\n",
                '  p no items',
            ],
            [
                'scope_each_variables' => false,
            ]
        );
        $this->assertCompile(
            [
                '<?php foreach ($items as $key => $item) { ?>',
                '<p><?= $item ?></p>',
                '<?php } ?>',
            ],
            [
                'each $item, $key in $items'."\n",
                '  p?!=$item',
            ],
            [
                'scope_each_variables' => false,
            ]
        );
    }

    /**
     * @covers ::<public>
     * @covers ::compileLoop
     * @covers ::getScopeVariablesDump
     * @covers ::getScopeVariablesRestore
     * @covers ::scopeEachVariables
     * @covers \Phug\Compiler\NodeCompiler\AbstractStatementNodeCompiler::wrapStatement
     */
    public function testCompileVariablesScope()
    {
        $this->assertRender(
            [
                '<p>42</p>',
                '<ul>',
                '<li>1</li>',
                '<li>2</li>',
                '<li>3</li>',
                '</ul>',
                '<p>42</p>',
            ],
            [
                '- $val = 42'."\n",
                'p= $val'."\n",
                'ul'."\n",
                '  each $val in [1, 2, 3]'."\n",
                '    li= $val'."\n",
                'p= $val',
            ]
        );
        $this->assertRender(
            [
                '<p>x 42</p>',
                '<ul>',
                '<li>0 1</li>',
                '<li>1 2</li>',
                '<li>2 3</li>',
                '</ul>',
                '<p>x 42</p>',
            ],
            [
                '- $val = 42'."\n",
                '- $index = "x"'."\n",
                'p #{$index} #{$val}'."\n",
                'ul'."\n",
                '  each $val, $index in [1, 2, 3]'."\n",
                '    li #{$index} #{$val}'."\n",
                'p #{$index} #{$val}',
            ]
        );
        $this->assertRender(
            [
                '<p>42</p>',
                '<ul>',
                '<li>1</li>',
                '<li>2</li>',
                '<li>3</li>',
                '</ul>',
                '<p>3</p>',
            ],
            [
                '- $val = 42'."\n",
                'p= $val'."\n",
                'ul'."\n",
                '  each $val in [1, 2, 3]'."\n",
                '    li= $val'."\n",
                'p= $val',
            ],
            [
                'scope_each_variables' => false,
            ]
        );
        $this->assertRender(
            [
                '<p>x 42</p>',
                '<ul>',
                '<li>0 1</li>',
                '<li>1 2</li>',
                '<li>2 3</li>',
                '</ul>',
                '<p>2 3</p>',
            ],
            [
                '- $val = 42'."\n",
                '- $index = "x"'."\n",
                'p #{$index} #{$val}'."\n",
                'ul'."\n",
                '  each $val, $index in [1, 2, 3]'."\n",
                '    li #{$index} #{$val}'."\n",
                'p #{$index} #{$val}',
            ],
            [
                'scope_each_variables' => false,
            ]
        );
        $this->assertRender(
            [
                '<p>42</p>',
                '<ul>',
                '<li>1</li>',
                '<li>2</li>',
                '<li>3</li>',
                '</ul>',
                '<p>42</p>',
            ],
            [
                '- $val = 42'."\n",
                'p= $val'."\n",
                'ul'."\n",
                '  each $val in [1, 2, 3]'."\n",
                '    li= $val'."\n",
                'p= $val',
            ],
            [
                'scope_each_variables' => '__anyName',
            ]
        );
        $this->assertRender(
            [
                '<p></p>',
                '<ul>',
                '<li>1</li>',
                '<li>2</li>',
                '<li>3</li>',
                '</ul>',
                '<p></p>',
            ],
            [
                'p= $val'."\n",
                'ul'."\n",
                '  each $val in [1, 2, 3]'."\n",
                '    li= $val'."\n",
                'p= $val',
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
            'given to each compiler.'
        );

        $eachCompiler = new EachNodeCompiler(new Compiler());
        $eachCompiler->compileNode(new ElementNode());
    }
}
