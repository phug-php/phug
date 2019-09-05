<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\YieldToken;
use Phug\Test\AbstractLexerTest;

class YieldScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\YieldScanner
     * @covers \Phug\Lexer\Scanner\YieldScanner::scan
     */
    public function testScan()
    {
        $this->assertTokens('yield', [
            YieldToken::class,
        ]);
    }
}
