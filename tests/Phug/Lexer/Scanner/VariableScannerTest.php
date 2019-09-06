<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\VariableToken;
use Phug\Test\AbstractLexerTest;

class VariableScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\VariableScanner
     * @covers \Phug\Lexer\Scanner\VariableScanner::scan
     */
    public function testVariable()
    {
        /* @var VariableToken $tok */
        list($tok) = $this->assertTokens('$var = "foo"', [
            VariableToken::class,
            ExpressionToken::class,
        ]);

        self::assertSame('var', $tok->getName());
    }
}
