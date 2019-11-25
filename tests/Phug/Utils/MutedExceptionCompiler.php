<?php

namespace Phug\Test\Utils;

use Phug\Compiler;

class MutedExceptionCompiler extends Compiler
{
    public function throwException($message, $node = null, $code = 0, $previous = null)
    {
        // removed
    }
}
