<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\CommentToken;
use Phug\Lexer\Token\FilterToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class TextScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\TextScanner
     * @covers \Phug\Lexer\Scanner\TextScanner::scan
     * @covers \Phug\Lexer\Scanner\TextScanner::scanInterpolationTokens
     * @covers \Phug\Lexer\Scanner\TextScanner::scanInterpolationToken
     * @covers \Phug\Lexer\Scanner\TextScanner::leftTrimValueIfNotAfterInterpolation
     */
    public function testText()
    {
        /* @var TextToken $text */
        list($text) = $this->assertTokens('| foo', [
            TextToken::class,
        ]);

        self::assertSame('foo', $text->getValue());
        self::assertFalse($text->isEscaped());
    }

    /**
     * @covers \Phug\Lexer\AbstractToken::getIndentation
     */
    public function testTextIndent()
    {
        /* @var TextToken $tok */
        list(, , , $tok) = $this->assertTokens('p'."\n".'  | foo', [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        self::assertSame('  ', $tok->getIndentation());
        self::assertSame('foo', $tok->getValue());
        self::assertFalse($tok->isEscaped());

        /* @var TextToken $tok */
        list(, , , $tok) = $this->assertTokens('p'."\n".'  ! foo', [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        self::assertSame('  ', $tok->getIndentation());
        self::assertSame('foo', $tok->getValue());
        self::assertTrue($tok->isEscaped());
    }

    /**
     * @covers \Phug\Lexer\Scanner\TextScanner
     * @covers \Phug\Lexer\Scanner\TextScanner::scan
     */
    public function testTextQuit()
    {
        $this->assertTokens('|', []);
    }

    /**
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::<public>
     */
    public function testStartingWhitespace()
    {
        $template = implode("\n", [
            'pre',
            '  code.',
            '    foo',
            '    bar',
            '    baz',
        ]);

        $tokens = $this->assertTokens($template, [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        /** @var TextToken $text */
        $text = end($tokens);

        self::assertSame("foo\nbar\nbaz", $text->getValue());

        $template = implode("\n", [
            'pre',
            '  code.',
            '    foo',
            '  ',
            '    bar',
            '    baz',
        ]);

        $tokens = $this->assertTokens($template, [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        /** @var TextToken $text */
        $text = end($tokens);

        self::assertSame("foo\n\nbar\nbaz", $text->getValue());

        $template = implode("\n", [
            'pre',
            '  code.',
            '    foo',
            '      ',
            '    bar',
            '    baz',
        ]);

        $tokens = $this->assertTokens($template, [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        /** @var TextToken $text */
        $text = end($tokens);

        self::assertSame("foo\n  \nbar\nbaz", $text->getValue());

        $template = implode("\n", [
            'pre',
            '  //',
            '    foo',
            ' ',
            '    bar',
            '    baz',
        ]);

        $tokens = $this->assertTokens($template, [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            CommentToken::class,
            TextToken::class,
        ]);

        /** @var TextToken $text */
        $text = end($tokens);

        self::assertSame("\n  foo\n\n  bar\n  baz", $text->getValue());

        $template = implode("\n", [
            'pre',
            '  //',
            '    foo',
            '   ',
            '    bar',
            '    baz',
        ]);

        $tokens = $this->assertTokens($template, [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            CommentToken::class,
            TextToken::class,
        ]);

        /** @var TextToken $text */
        $text = end($tokens);

        self::assertSame("\n  foo\n \n  bar\n  baz", $text->getValue());

        $template = implode("\n", [
            'pre',
            '  //',
            '    foo',
            '   x',
            '    bar',
            '    baz',
        ]);

        $tokens = $this->assertTokens($template, [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            CommentToken::class,
            TextToken::class,
        ]);

        /** @var TextToken $text */
        $text = end($tokens);

        self::assertSame("\n  foo\n x\n  bar\n  baz", $text->getValue());
    }

    /**
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::<public>
     */
    public function testWhitespaceWithFilter()
    {
        $template = implode("\n", [
            'div',
            '  :pre',
            '    foo',
            ' ',
            '    bar',
            '    baz',
        ]);

        $tokens = $this->assertTokens($template, [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            FilterToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        /** @var TextToken $text */
        $text = end($tokens);

        self::assertSame("\nfoo\n\nbar\nbaz", $text->getValue());

        $template = implode("\n", [
            'div',
            '  :pre',
            '    foo',
            '   ',
            '    bar',
            '    baz',
        ]);

        $tokens = $this->assertTokens($template, [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            FilterToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        /** @var TextToken $text */
        $text = end($tokens);

        self::assertSame("\nfoo\n\nbar\nbaz", $text->getValue());

        $template = implode("\n", [
            'div',
            '  :pre',
            '    foo',
            '   x',
            '    bar',
            '    baz',
        ]);

        $tokens = $this->assertTokens($template, [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            FilterToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        /** @var TextToken $text */
        $text = end($tokens);

        self::assertSame("\n foo\nx\n bar\n baz", $text->getValue());
    }

    /**
     * @covers \Phug\Lexer\Analyzer\LineAnalyzer::<public>
     */
    public function testWhitespaceWithMarkup()
    {
        $template = implode("\n", [
            'div',
            '  <pre>',
            '    foo',
            ' ',
            '    bar',
            '    baz',
            '  </pre>',
        ]);

        $tokens = $this->assertTokens($template, [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        /** @var TextToken $text */
        $text = end($tokens);

        self::assertSame("<pre>\n  foo\n\n  bar\n  baz\n</pre>", $text->getValue());
    }
}
