<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Lexer\Token\InterpolationStartToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TagInterpolationEndToken;
use Phug\Lexer\Token\TagInterpolationStartToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class TextBlockScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\State::nextOutdent
     * @covers \Phug\Lexer\Scanner\IndentationScanner::scan
     * @covers \Phug\Lexer\Scanner\IndentationScanner::formatIndentChar
     * @covers \Phug\Lexer\Scanner\IndentationScanner::getIndentChar
     * @covers \Phug\Lexer\Scanner\IndentationScanner::getIndentLevel
     * @covers \Phug\Lexer\Scanner\TextBlockScanner
     * @covers \Phug\Lexer\Scanner\TextBlockScanner::scan
     * @covers \Phug\Lexer\Scanner\MultilineScanner
     * @covers \Phug\Lexer\Scanner\MultilineScanner::unEscapedToken
     * @covers \Phug\Lexer\Scanner\MultilineScanner::yieldLines
     * @covers \Phug\Lexer\Scanner\MultilineScanner::getUnescapedLineValue
     * @covers \Phug\Lexer\Scanner\MultilineScanner::getUnescapedLines
     * @covers \Phug\Lexer\Scanner\MultilineScanner::scan
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::<public>
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::recordLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     * @covers \Phug\Lexer\Scanner\InterpolationScanner
     * @covers \Phug\Lexer\Scanner\InterpolationScanner::scanInterpolation
     * @covers \Phug\Lexer\Scanner\InterpolationScanner::scan
     */
    public function testScan()
    {
        /** @var TextToken $text */
        list(, $text) = $this->assertTokens('p. Hello', [
            TagToken::class,
            TextToken::class,
        ]);
        self::assertFalse($text->isEscaped());

        $this->assertTokens("p.\n  Hello", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        $this->assertTokens("p.\n  Hello\n  text #[strong foo] bar\n  bar", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            TagInterpolationStartToken::class,
            TagToken::class,
            TextToken::class,
            TagInterpolationEndToken::class,
            TextToken::class,
        ]);

        $this->assertTokens("p.\n  Hello\n    #[strong foo] bar\n  bar", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            TagInterpolationStartToken::class,
            TagToken::class,
            TextToken::class,
            TagInterpolationEndToken::class,
            TextToken::class,
        ]);

        $tokens = $this->assertTokens("p.\n  Hello\n    #{\$foo} bar\n  bar", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            TextToken::class,
        ]);
        /** @var ExpressionToken $token */
        $token = $tokens[5];
        self::assertTrue($token->isEscaped());

        $tokens = $this->assertTokens("p.\n  Hello\n    !{\$foo} bar\n  bar", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            TextToken::class,
        ]);
        /** @var ExpressionToken $token */
        $token = $tokens[5];
        self::assertFalse($token->isEscaped());

        $this->assertTokens("section\n  div\n    p.\n      Hello\n  article", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            NewLineToken::class,
            OutdentToken::class,
            OutdentToken::class,
            TagToken::class,
        ]);

        $this->assertTokens("p.\n\n\n  Hello", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        $this->assertTokens("p.\ndiv", [
            TagToken::class,
            NewLineToken::class,
            TagToken::class,
        ]);

        $this->assertTokens("section\n  p.\ndiv", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            OutdentToken::class,
            TagToken::class,
        ]);

        /** TextToken $tok */
        list(, , , $tok) = $this->assertTokens("pre.\n  foo\n    bar\n  biz\ndiv", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            NewLineToken::class,
            OutdentToken::class,
            TagToken::class,
        ]);

        self::assertSame("foo\n  bar\nbiz", $tok->getValue());
    }

    /**
     * @covers \Phug\Lexer\State::nextOutdent
     * @covers \Phug\Lexer\Scanner\IndentationScanner::scan
     * @covers \Phug\Lexer\Scanner\IndentationScanner::getIndentChar
     * @covers \Phug\Lexer\Scanner\IndentationScanner::getIndentLevel
     * @covers \Phug\Lexer\Scanner\TextBlockScanner
     * @covers \Phug\Lexer\Scanner\TextBlockScanner::scan
     * @covers \Phug\Lexer\Scanner\MultilineScanner
     * @covers \Phug\Lexer\Scanner\MultilineScanner::unEscapedToken
     * @covers \Phug\Lexer\Scanner\MultilineScanner::yieldLines
     * @covers \Phug\Lexer\Scanner\MultilineScanner::getUnescapedLineValue
     * @covers \Phug\Lexer\Scanner\MultilineScanner::getUnescapedLines
     * @covers \Phug\Lexer\Scanner\MultilineScanner::scan
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::<public>
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::recordLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     */
    public function testScanWhiteSpaces()
    {
        $tokens = $this->assertTokens("p.\n  Hello\n    world\n  bye\ndiv", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            NewLineToken::class,
            OutdentToken::class,
            TagToken::class,
        ]);

        $tokens = array_filter($tokens, function ($token) {
            return $token instanceof TextToken;
        });
        $token = reset($tokens);

        self::assertSame("Hello\n  world\nbye", $token->getValue());

        $tokens = $this->assertTokens("p.\n  Hello\n    world\n\n       \n   bye\n    \n\ndiv", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            NewLineToken::class,
            OutdentToken::class,
            TagToken::class,
        ]);

        $tokens = array_filter($tokens, function ($token) {
            return $token instanceof TextToken;
        });
        $token = reset($tokens);

        self::assertSame("Hello\n  world\n\n     \n bye\n  \n", $token->getValue());
    }
}
