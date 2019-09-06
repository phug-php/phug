<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer;
use Phug\Lexer\Scanner\IndentationScanner;
use Phug\Lexer\Scanner\TagScanner;
use Phug\Lexer\State;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TagToken;
use Phug\Test\AbstractLexerTest;

class IndentationScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\State::indent
     * @covers \Phug\Lexer\State::outdent
     * @covers \Phug\Lexer\State::getIndentLevel
     * @covers \Phug\Lexer\State::nextOutdent
     * @covers \Phug\Lexer\Scanner\IndentationScanner
     * @covers \Phug\Lexer\Scanner\IndentationScanner::scan
     * @covers \Phug\Lexer\Scanner\IndentationScanner::getIndentChar
     */
    public function testIndentation()
    {
        $this->assertTokens('  ', [
            IndentToken::class,
        ]);

        $this->assertTokens("  \n", [
            NewLineToken::class,
        ]);

        $this->assertTokens("div\n  p\n    a\nfooter", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            OutdentToken::class,
            OutdentToken::class,
            TagToken::class,
        ]);
    }

    /**
     * @covers \Phug\Lexer\State::indent
     * @covers \Phug\Lexer\State::outdent
     * @covers \Phug\Lexer\State::getIndentLevel
     * @covers \Phug\Lexer\State::nextOutdent
     * @covers \Phug\Lexer\Scanner\IndentationScanner
     * @covers \Phug\Lexer\Scanner\IndentationScanner::scan
     * @covers \Phug\Lexer\Scanner\IndentationScanner::getIndentChar
     */
    public function testIndentationQuit()
    {
        $state = new State(new Lexer(), 'p', []);
        $scanners = [
            'indent' => IndentationScanner::class,
        ];
        $tokens = [];
        foreach ($state->loopScan($scanners) as $token) {
            $tokens[] = $token;
        }

        self::assertSame(0, count($tokens));

        $state = new State(new Lexer(), "p\t\t", []);
        $scanners = [
            'tag'    => TagScanner::class,
            'indent' => IndentationScanner::class,
        ];
        $tokens = [];
        foreach ($state->loopScan($scanners) as $token) {
            $tokens[] = $token;
        }

        self::assertSame(1, count($tokens));
        self::assertInstanceOf(TagToken::class, $tokens[0]);
    }

    /**
     * @covers \Phug\Lexer\State::indent
     * @covers \Phug\Lexer\State::outdent
     * @covers \Phug\Lexer\State::getIndentLevel
     * @covers \Phug\Lexer\State::nextOutdent
     * @covers \Phug\Lexer\Scanner\IndentationScanner
     * @covers \Phug\Lexer\Scanner\IndentationScanner::scan
     * @covers \Phug\Lexer\Scanner\IndentationScanner::getIndentChar
     */
    public function testJumpIndentation()
    {
        $this->assertTokens("div\n  p\n      a\nfooter", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            OutdentToken::class,
            OutdentToken::class,
            TagToken::class,
        ]);
    }

    /**
     * @covers \Phug\Lexer\State::indent
     * @covers \Phug\Lexer\State::outdent
     * @covers \Phug\Lexer\State::getIndentLevel
     * @covers \Phug\Lexer\State::nextOutdent
     * @covers \Phug\Lexer\Scanner\IndentationScanner
     * @covers \Phug\Lexer\Scanner\IndentationScanner::scan
     * @covers \Phug\Lexer\Scanner\IndentationScanner::formatIndentChar
     * @covers \Phug\Lexer\Scanner\IndentationScanner::getIndentChar
     */
    public function testMixedIndentation()
    {
        $this->assertTokens("div\n    \tp\n\t    a\nfooter", [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            TagToken::class,
            NewLineToken::class,
            OutdentToken::class,
            TagToken::class,
        ]);

        $lexer = new Lexer([
            'indent_style' => Lexer::INDENT_TAB,
            'indent_width' => 1,
        ]);
        $gen = $lexer->lex("div\n\t  p\n\t  a\nfooter");
        $tokensClasses = [];
        foreach ($gen as $token) {
            $tokensClasses[] = get_class($token);
        }

        self::assertSame([
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            TagToken::class,
            NewLineToken::class,
            OutdentToken::class,
            TagToken::class,
        ], $tokensClasses);
    }

    /**
     * @covers            \Phug\Lexer\State::indent
     * @covers            \Phug\Lexer\State::outdent
     * @covers            \Phug\Lexer\State::getIndentLevel
     * @covers            \Phug\Lexer\State::nextOutdent
     * @covers            \Phug\Lexer\Scanner\IndentationScanner::scan
     * @covers            \Phug\Lexer\Scanner\IndentationScanner::formatIndentChar
     * @covers            \Phug\Lexer\Scanner\IndentationScanner::getIndentChar
     * @expectedException \Phug\LexerException
     */
    public function testInconsistentIndent()
    {
        $this->expectMessageToBeThrown(
            'Inconsistent indentation. '.
            'Expecting either 2 or 6 spaces/tabs.'
        );

        $lexer = new Lexer();
        $gen = $lexer->lex("div\n  div\n      a\n    footer");
        $tokens = [];
        foreach ($gen as $token) {
            $tokens[] = $token;
        }
    }

    /**
     * @covers            \Phug\Lexer\Scanner\IndentationScanner::formatIndentChar
     * @covers            \Phug\Lexer\Scanner\IndentationScanner::getIndentLevel
     * @expectedException \Phug\LexerException
     */
    public function testNotAllowedMixedIndent()
    {
        $this->expectMessageToBeThrown(
            'Invalid indentation, '.
            'you can use tabs or spaces but not both'
        );

        $lexer = new Lexer([
            'allow_mixed_indent' => false,
        ]);
        $gen = $lexer->lex("div\n\t  div");
        $tokens = [];
        foreach ($gen as $token) {
            $tokens[] = $token;
        }
    }
}
