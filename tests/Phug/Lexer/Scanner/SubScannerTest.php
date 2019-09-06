<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\ExpansionToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class SubScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\SubScanner
     * @covers \Phug\Lexer\Scanner\SubScanner::scan
     * @covers \Phug\Lexer\Scanner\TextBlockScanner
     * @covers \Phug\Lexer\Scanner\TextBlockScanner::scan
     */
    public function testScan()
    {
        /* @var TextToken $tok */
        list($tok) = $this->assertTokens('. Hello', [
            TextToken::class,
        ]);
        self::assertSame('Hello', $tok->getValue());

        /* @var TextToken $tok */
        list(, $tok) = $this->assertTokens(".\n  Hello", [
            NewLineToken::class,
            TextToken::class,
        ]);
        self::assertSame('Hello', $tok->getValue());

        /* @var TextToken $tok */
        list(, $tok) = $this->assertTokens(".\n  foo\n  bar\n\n\n  baz", [
            NewLineToken::class,
            TextToken::class,
        ]);
        self::assertSame("foo\nbar\n\n\nbaz", $tok->getValue());

        $this->assertTokens(".\nHello", [
            NewLineToken::class,
            TagToken::class,
        ]);

        $this->assertTokens('p Hello', [
            TagToken::class,
            TextToken::class,
        ]);

        $this->assertTokens('p. Hello', [
            TagToken::class,
            TextToken::class,
        ]);

        $this->assertTokens('p! Hello', [
            TagToken::class,
            TextToken::class,
        ]);

        $this->assertTokens('p!. Hello', [
            TagToken::class,
            TextToken::class,
        ]);

        $this->assertTokens('p:!. Hello', [
            TagToken::class,
            ExpansionToken::class,
            TextToken::class,
        ]);
    }
}
