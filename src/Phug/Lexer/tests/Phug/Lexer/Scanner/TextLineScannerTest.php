<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer;
use Phug\Lexer\Scanner\TextLineScanner;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TagInterpolationEndToken;
use Phug\Lexer\Token\TagInterpolationStartToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class TextLineScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\TextLineScanner
     * @covers \Phug\Lexer\Scanner\TextLineScanner::scan
     */
    public function testScan()
    {
        /* @var TextToken $tok */
        list(, , , $tok) = $this->assertTokens("p\n  | Hello", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        self::assertFalse($tok->isEscaped());

        /* @var TextToken $tok */
        list(, , , $tok) = $this->assertTokens("p\n  ! Hello", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        self::assertTrue($tok->isEscaped());

        $template = "p\n  | foo\n  | bar\n  |\n  |\n  | baz\np";

        /* @var TextToken $tok */
        list(, , , , , , , $tok) = $this->assertTokens($template, [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            NewLineToken::class,
            TextToken::class,
            NewLineToken::class,
            TextToken::class,
            NewLineToken::class,
            TextToken::class,
            NewLineToken::class,
            TextToken::class,
            NewLineToken::class,
            OutdentToken::class,
            TagToken::class,
        ]);

        self::assertSame(' ', $tok->getValue());

        $lexer = new Lexer();
        $lexer->prependScanner('text_line', TextLineScanner::class);
        $tokens = iterator_to_array($lexer->lex('!| <foo>'));

        self::assertCount(1, $tokens);
        self::assertInstanceOf(TextToken::class, $tokens[0]);
        /** @var TextToken */
        $tok = $tokens[0];
        self::assertTrue($tok->isEscaped());
        self::assertSame('<foo>', $tok->getValue());

        $tokens = iterator_to_array($lexer->lex('| <foo>'));

        self::assertCount(1, $tokens);
        self::assertInstanceOf(TextToken::class, $tokens[0]);
        /** @var TextToken */
        $tok = $tokens[0];
        self::assertFalse($tok->isEscaped());
        self::assertSame('<foo>', $tok->getValue());
    }

    /**
     * @covers \Phug\Lexer\Scanner\TextLineScanner
     * @covers \Phug\Lexer\Scanner\TextLineScanner::scan
     * @covers \Phug\Lexer\Scanner\TextScanner
     * @covers \Phug\Lexer\Scanner\TextScanner::scan
     * @covers \Phug\Lexer\Scanner\TextScanner::scanInterpolationTokens
     * @covers \Phug\Lexer\Scanner\TextScanner::scanInterpolationToken
     * @covers \Phug\Lexer\Scanner\TextScanner::leftTrimValueIfNotAfterInterpolation
     * @covers \Phug\Lexer::getLastToken
     * @covers \Phug\Lexer\State::getLastToken
     * @covers \Phug\Lexer\Scanner\InterpolationScanner
     * @covers \Phug\Lexer\Scanner\InterpolationScanner::scanInterpolation
     * @covers \Phug\Lexer\Scanner\InterpolationScanner::scan
     */
    public function testScanQuit()
    {
        $this->assertTokens('p Hello', [
            TagToken::class,
            TextToken::class,
        ]);
        $this->assertTokens('p Hello #[strong world]!', [
            TagToken::class,
            TextToken::class,
            TagInterpolationStartToken::class,
            TagToken::class,
            TextToken::class,
            TagInterpolationEndToken::class,
            TextToken::class,
        ]);
    }
}
