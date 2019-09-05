<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\ClassToken;
use Phug\Lexer\Token\ExpansionToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class TagScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\TagScanner
     * @covers \Phug\Lexer\Scanner\TagScanner::scan
     */
    public function testUsualTagName()
    {
        /** @var TagToken $tok */
        list($tok) = $this->assertTokens('some-tag-name', [
            TagToken::class,
        ]);

        self::assertSame('some-tag-name', $tok->getName());
    }

    /**
     * @covers \Phug\Lexer\Scanner\TagScanner
     * @covers \Phug\Lexer\Scanner\TagScanner::scan
     */
    public function testNamespacedTagName()
    {
        /** @var TagToken $tok */
        list($tok) = $this->assertTokens('some-namespace:some-tag-name', [
            TagToken::class,
        ]);

        self::assertSame('some-namespace:some-tag-name', $tok->getName());
    }

    /**
     * @covers \Phug\Lexer\Scanner\TagScanner
     * @covers \Phug\Lexer\Scanner\TagScanner::scan
     */
    public function testIfScannerConfusesExpansionWithNamespacedTagName()
    {
        /**
         * @var TagToken
         * @var TagToken $b
         */
        list($a, , $b) = $this->assertTokens('some-outer-tag: some-inner-tag', [
            TagToken::class,
            ExpansionToken::class,
            TagToken::class,
        ]);

        self::assertSame('some-outer-tag', $a->getName());
        self::assertSame('some-inner-tag', $b->getName());
    }

    /**
     * @covers \Phug\Lexer\Scanner\TagScanner
     * @covers \Phug\Lexer\Scanner\TagScanner::scan
     */
    public function testTagNameAndClassName()
    {
        /* @var TagToken $tok */
        list($tag, $class) = $this->assertTokens('foo:bar.foo-bar', [
            TagToken::class,
            ClassToken::class,
        ]);

        self::assertSame('foo:bar', $tag->getName());
        self::assertSame('foo-bar', $class->getName());
    }

    /**
     * @covers \Phug\Lexer\Scanner\TagScanner
     * @covers \Phug\Lexer\Scanner\TagScanner::scan
     */
    public function testBlockQuoteTag()
    {
        $template = "figure\n".
            "  blockquote\n".
            "    | Try to define yourself by what you do, and you&#8217;ll burnout every time. You are.\n".
            '  figcaption from @thefray at 1:43pm on May 10';

        $this->assertTokens($template, [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            NewLineToken::class,
            OutdentToken::class,
            TagToken::class,
            TextToken::class,
        ]);
    }
}
