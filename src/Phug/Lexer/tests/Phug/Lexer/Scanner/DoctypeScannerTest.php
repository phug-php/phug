<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\DoctypeToken;
use Phug\Test\AbstractLexerTest;

class DoctypeScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\DoctypeScanner
     * @covers \Phug\Lexer\Scanner\DoctypeScanner::scan
     */
    public function testCommonDoctypes()
    {

        /** @var DoctypeToken $tok */
        list($tok) = $this->assertTokens(
            'doctype 5',
            [DoctypeToken::class]
        );

        self::assertSame('5', $tok->getName());

        list($tok) = $this->assertTokens(
            '!!! 5',
            [DoctypeToken::class]
        );
        self::assertSame('5', $tok->getName());
    }
}
