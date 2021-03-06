<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Lexer\Token\InterpolationStartToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\TagInterpolationEndToken;
use Phug\Lexer\Token\TagInterpolationStartToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class InterpolationScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\InterpolationScanner
     * @covers \Phug\Lexer\Scanner\TextScanner::scan
     * @covers \Phug\Lexer\Scanner\TextScanner::scanInterpolationTokens
     * @covers \Phug\Lexer\Scanner\TextScanner::scanInterpolationToken
     * @covers \Phug\Lexer\Scanner\TextScanner::leftTrimValueIfNotAfterInterpolation
     */
    public function testScan()
    {
        $tokens = $this->assertTokens('p a #[strong b] c', [
            TagToken::class,
            TextToken::class,
            TagInterpolationStartToken::class,
            TagToken::class,
            TextToken::class,
            TagInterpolationEndToken::class,
            TextToken::class,
        ]);

        self::assertSame('a ', $tokens[1]->getValue());
        self::assertSame('b', $tokens[4]->getValue());
        self::assertSame(' c', $tokens[6]->getValue());

        $tokens = $this->assertTokens('p  a#[strong  b ]c ', [
            TagToken::class,
            TextToken::class,
            TagInterpolationStartToken::class,
            TagToken::class,
            TextToken::class,
            TagInterpolationEndToken::class,
            TextToken::class,
        ]);

        self::assertSame(' a', $tokens[1]->getValue());
        self::assertSame(' b ', $tokens[4]->getValue());
        self::assertSame('c ', $tokens[6]->getValue());

        $tokens = $this->assertTokens('p  a#{b}c ', [
            TagToken::class,
            TextToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            TextToken::class,
        ]);

        self::assertSame(' a', $tokens[1]->getValue());
        self::assertSame('b', $tokens[3]->getValue());
        self::assertSame('c ', $tokens[5]->getValue());

        $tokens = $this->assertTokens('#{b} c', [
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            TextToken::class,
        ]);

        self::assertSame('b', $tokens[1]->getValue());
        self::assertSame(' c', $tokens[3]->getValue());

        $tokens = $this->assertTokens('p a#[strong #{b}]c', [
            TagToken::class,
            TextToken::class,
            TagInterpolationStartToken::class,
            TagToken::class,
            TextToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            TagInterpolationEndToken::class,
            TextToken::class,
        ]);

        self::assertSame([
            null,
            'a',
            null,
            null,
            '',
            null,
            'b',
            null,
            null,
            'c',
        ], array_map(function ($token) {
            if (method_exists($token, 'getValue')) {
                return $token->getValue();
            }
        }, $tokens));

        $this->assertTokens("p\n  |#{\$var} foo\n  | bar", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            TextToken::class,
            NewLineToken::class,
            TextToken::class,
        ]);

        $this->assertTokens("p\n  | #{\$var}\n  | biz", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            TextToken::class,
            NewLineToken::class,
            TextToken::class,
        ]);

        $this->assertTokens("| #{\$var}\n| biz", [
            TextToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            TextToken::class,
            NewLineToken::class,
            TextToken::class,
        ]);

        $this->assertTokens("| #{\$var}\nbiz", [
            TextToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            NewLineToken::class,
            TagToken::class,
        ]);

        $this->assertTokens("p.\n  foo\n  #{'hi'}\n", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            TextToken::class,
        ]);
    }

    /**
     * @covers \Phug\Lexer\Scanner\InterpolationScanner::scanTagInterpolation
     * @covers \Phug\Lexer\Scanner\InterpolationScanner::throwEndOfLineExceptionIf
     * @covers \Phug\Lexer\Scanner\InterpolationScanner::scanInterpolation
     */
    public function testNewLineInTagInterpolation()
    {
        $this->expectMessageToBeThrown('End of line was reached with no closing bracket for interpolation.');

        $input = "p #[em\n]";
        iterator_to_array($this->lexer->lex($input));
    }

    /**
     * @covers \Phug\Lexer\Scanner\InterpolationScanner::scanExpressionInterpolation
     * @covers \Phug\Lexer\Scanner\InterpolationScanner::throwEndOfLineExceptionIf
     * @covers \Phug\Lexer\Scanner\InterpolationScanner::scanInterpolation
     */
    public function testNewLineInInterpolation()
    {
        $this->expectMessageToBeThrown('End of line was reached with no closing bracket for interpolation.');

        $this->lexer->setOption('multiline_interpolation', false);
        $input = "p #{em\n}";
        iterator_to_array($this->lexer->lex($input));
    }
}
