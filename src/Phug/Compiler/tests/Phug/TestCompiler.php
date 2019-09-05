<?php

namespace Phug\Test;

use Phug\Compiler;
use Phug\Formatter\ElementInterface;
use Phug\Parser\NodeInterface;

class TestCompiler extends Compiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        return 'foo';
    }
}
