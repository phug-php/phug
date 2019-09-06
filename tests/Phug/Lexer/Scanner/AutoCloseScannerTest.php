<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\AssignmentToken;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\AutoCloseToken;
use Phug\Lexer\Token\ClassToken;
use Phug\Lexer\Token\IdToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class AutoCloseScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\AutoCloseScanner
     * @covers \Phug\Lexer\Scanner\AutoCloseScanner::scan
     * @covers \Phug\Lexer\Scanner\AttributeScanner
     * @covers \Phug\Lexer\Scanner\AttributeScanner::scan
     * @covers \Phug\Lexer\Scanner\ClassScanner
     * @covers \Phug\Lexer\Scanner\ClassScanner::scan
     * @covers \Phug\Lexer\Scanner\IdScanner
     * @covers \Phug\Lexer\Scanner\IdScanner::scan
     * @covers \Phug\Lexer\Scanner\TagScanner
     * @covers \Phug\Lexer\Scanner\TagScanner::scan
     * @covers \Phug\Lexer\Scanner\ElementScanner
     * @covers \Phug\Lexer\Scanner\ElementScanner::scan
     */
    public function testScan()
    {
        $this->assertTokens('link/', [
            TagToken::class,
            AutoCloseToken::class,
        ]);
        $this->assertTokens('div()/', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeEndToken::class,
            AutoCloseToken::class,
        ]);
        $this->assertTokens('div(foo="bar")/', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            AutoCloseToken::class,
        ]);
        $this->assertTokens('div&attributes($foo)/', [
            TagToken::class,
            AssignmentToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            AutoCloseToken::class,
        ]);
        $this->assertTokens('div.foo/', [
            TagToken::class,
            ClassToken::class,
            AutoCloseToken::class,
        ]);
        $this->assertTokens('div(foo="bar").foo#bar/', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            ClassToken::class,
            IdToken::class,
            AutoCloseToken::class,
        ]);
        $this->assertTokens('div#bar.foo(foo="bar")/', [
            TagToken::class,
            IdToken::class,
            ClassToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            AutoCloseToken::class,
        ]);

        $this->assertTokens('link /', [
            TagToken::class,
            TextToken::class,
        ]);
        $this->assertTokens('div() /', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeEndToken::class,
            TextToken::class,
        ]);
        $this->assertTokens('div(foo="bar") /', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            TextToken::class,
        ]);
        $this->assertTokens('div&attributes($foo) /', [
            TagToken::class,
            AssignmentToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            TextToken::class,
        ]);
        $this->assertTokens('div.foo /', [
            TagToken::class,
            ClassToken::class,
            TextToken::class,
        ]);
        $this->assertTokens('div(foo="bar").foo#bar /', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            ClassToken::class,
            IdToken::class,
            TextToken::class,
        ]);
    }
}
