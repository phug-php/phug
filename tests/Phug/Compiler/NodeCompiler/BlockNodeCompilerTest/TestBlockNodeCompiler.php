<?php

namespace Phug\Test\Compiler\NodeCompiler\BlockNodeCompilerTest;

use Phug\Compiler\Element\BlockElement;
use Phug\Compiler\NodeCompiler\BlockNodeCompiler;
use Phug\Formatter\ElementInterface;
use Phug\Parser\NodeInterface;

class TestBlockNodeCompiler extends BlockNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $blocks = &$this->getCompiler()->getBlocksByName('foo');
        $blocks[] = 'bar';

        return new BlockElement($this->getCompiler(), 'bar', null, $parent);
    }
}
