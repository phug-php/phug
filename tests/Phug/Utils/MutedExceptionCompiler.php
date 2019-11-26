<?php

namespace Phug\Test\Utils;

use Phug\Compiler;
use Phug\Formatter\ElementInterface;
use Phug\Parser\NodeInterface;

class MutedExceptionCompiler extends Compiler
{
    public $forcedReturn = null;

    public function throwException($message, $node = null, $code = 0, $previous = null)
    {
        // removed
    }

    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        return $this->forcedReturn ?: parent::compileNode($node, $parent);
    }
}
