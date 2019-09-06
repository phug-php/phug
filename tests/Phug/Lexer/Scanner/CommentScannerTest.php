<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer;
use Phug\Lexer\Scanner\CommentScanner;
use Phug\Lexer\State;
use Phug\Lexer\Token\CommentToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class CommentScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\CommentScanner
     * @covers \Phug\Lexer\Scanner\CommentScanner::scan
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::<public>
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::recordLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     */
    public function testVisibleSingleLineComment()
    {
        /**
         * @var CommentToken $c
         * @var TextToken    $t
         */
        list($c, $t) = $this->assertTokens('// This is some comment text', [
            CommentToken::class,
            TextToken::class,
        ]);

        self::assertTrue($c->isVisible());
        self::assertSame(' This is some comment text', $t->getValue());
    }

    /**
     * @covers \Phug\Lexer\Scanner\CommentScanner
     * @covers \Phug\Lexer\Scanner\CommentScanner::scan
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::<public>
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::recordLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     */
    public function testInvisibleSingleLineComment()
    {
        /**
         * @var CommentToken $c
         * @var TextToken    $t
         */
        list($c, $t) = $this->assertTokens('//- This is some comment text', [
            CommentToken::class,
            TextToken::class,
        ]);

        self::assertFalse($c->isVisible());
        self::assertSame(' This is some comment text', $t->getValue());
    }

    /**
     * @covers \Phug\Lexer\Scanner\CommentScanner
     * @covers \Phug\Lexer\Scanner\CommentScanner::scan
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::<public>
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::recordLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     */
    public function testVisibleMultiLineComment()
    {
        /**
         * @var CommentToken $c
         * @var TextToken    $t
         */
        list($c, $t) = $this->assertTokens("//\n\tFirst line\n\tSecond line\n\tThird line", [
            CommentToken::class,
            TextToken::class,
        ]);

        self::assertTrue($c->isVisible());
        self::assertSame(
            "\n\tFirst line\n\tSecond line\n\tThird line",
            $t->getValue()
        );
    }

    /**
     * @covers \Phug\Lexer\Scanner\CommentScanner
     * @covers \Phug\Lexer\Scanner\CommentScanner::scan
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::<public>
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::recordLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     */
    public function testInvisibleMultiLineComment()
    {
        /**
         * @var CommentToken $c
         * @var TextToken    $t
         */
        list($c, $t) = $this->assertTokens("//-\n\tFirst line\n\tSecond line\n\tThird line", [
            CommentToken::class,
            TextToken::class,
        ]);

        self::assertFalse($c->isVisible());
        self::assertSame(
            "\n\tFirst line\n\tSecond line\n\tThird line",
            $t->getValue()
        );
    }

    /**
     * @covers \Phug\Lexer\Scanner\CommentScanner
     * @covers \Phug\Lexer\Scanner\CommentScanner::scan
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::<public>
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::recordLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     */
    public function testCommentInIndent()
    {
        $this->assertTokens("div\n  div\n    //- lorem\n  ul\n    li item", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            CommentToken::class,
            TextToken::class,
            NewLineToken::class,
            OutdentToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            TextToken::class,
        ]);
    }

    /**
     * @covers \Phug\Lexer\Scanner\CommentScanner
     * @covers \Phug\Lexer\Scanner\CommentScanner::scan
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::<public>
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::recordLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     */
    public function testCommentQuit()
    {
        $state = new State(new Lexer(), 'p', []);
        $scanners = [
            'comment' => CommentScanner::class,
        ];
        $tokens = [];
        foreach ($state->loopScan($scanners) as $token) {
            $tokens[] = $token;
        }

        self::assertSame(0, count($tokens));
    }
}
