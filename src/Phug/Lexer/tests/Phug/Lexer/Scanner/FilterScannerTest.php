<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\CodeToken;
use Phug\Lexer\Token\CommentToken;
use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\FilterToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class FilterScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\FilterScanner
     * @covers \Phug\Lexer\Scanner\FilterScanner::scan
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::<public>
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::recordLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     */
    public function testFilter()
    {
        list($tok) = $this->assertTokens(':foo bar', [
            FilterToken::class,
            TextToken::class,
        ]);

        self::assertSame('foo', $tok->getName());

        list($tok) = $this->assertTokens(":foo:bar\n  bar", [
            FilterToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        self::assertSame('foo:bar', $tok->getName());

        $this->assertTokens(":foo:bar\n  bar\n:foo:bar\n  bar", [
            FilterToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            NewLineToken::class,
            OutdentToken::class,
            FilterToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        $this->assertTokens(':foo(opt1=1 opt2=2) bar', [
            FilterToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            TextToken::class,
        ]);

        $template = "html\n".
            "  head\n".
            "    style(type=\"text/css\")\n".
            "      :stylus\n".
            "        body\n".
            "          padding: 50px\n".
            "  body\n";

        $this->assertTokens($template, [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            NewLineToken::class,
            IndentToken::class,
            FilterToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            NewLineToken::class,
            OutdentToken::class,
            OutdentToken::class,
            OutdentToken::class,
            TagToken::class,
            NewLineToken::class,
        ]);
    }

    /**
     * @covers \Phug\Lexer\Scanner\FilterScanner
     * @covers \Phug\Lexer\Scanner\FilterScanner::scan
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::<public>
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::recordLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::getLine
     */
    public function testStylusFilter()
    {
        $template = "//- set from php controller\n".
            "- prev = color\n".
            "\n".
            "//- set in the jade template\n".
            "- color = 'red'\n".
            "\n".
            "head\n".
            "  :stylus\n".
            "    prev = yellow\n".
            "    p\n".
            "      width 200px\n".
            "      color #{color}\n".
            "      a\n".
            "        color #{prev}\n".
            "      em\n".
            "        color prev\n".
            "body\n".
            "  p\n".
            "    | I'm\n".
            "    =\" \"\n".
            "    =color\n".
            "    =\" \"\n".
            "    | but my links are\n".
            "    =\" \"\n".
            "    a=prev\n".
            "    =\" \"\n".
            "    | and my quotes are\n".
            "    =\" \"\n".
            "    em=prev\n";

        $this->assertTokens($template, [
            CommentToken::class,
            TextToken::class,
            NewLineToken::class,
            CodeToken::class,
            TextToken::class,
            NewLineToken::class,
            NewLineToken::class,
            CommentToken::class,
            TextToken::class,
            NewLineToken::class,
            CodeToken::class,
            TextToken::class,
            NewLineToken::class,
            NewLineToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            FilterToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            NewLineToken::class,
            OutdentToken::class,
            OutdentToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            NewLineToken::class,
            ExpressionToken::class,
            NewLineToken::class,
            ExpressionToken::class,
            NewLineToken::class,
            ExpressionToken::class,
            NewLineToken::class,
            TextToken::class,
            NewLineToken::class,
            ExpressionToken::class,
            NewLineToken::class,
            TagToken::class,
            ExpressionToken::class,
            NewLineToken::class,
            ExpressionToken::class,
            NewLineToken::class,
            TextToken::class,
            NewLineToken::class,
            ExpressionToken::class,
            NewLineToken::class,
            TagToken::class,
            ExpressionToken::class,
            NewLineToken::class,
        ]);
    }
}
