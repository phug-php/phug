<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\EachToken;
use Phug\Lexer\Token\ExpansionToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

/**
 * @coversDefaultClass \Phug\Lexer\Scanner\EachScanner
 */
class EachScannerTest extends AbstractLexerTest
{
    public function provideExpressions()
    {
        return [
            ['$someSubject'],
            ['$a ? $b : $c'],
            ['$a ?: $b'],
            ['Foo::$bar'],
            ['Foo::bar()'],
            ['$a ? $b : ($c ? $d : $e)'],
            ['($some ? $ternary : $operator)'],
        ];
    }

    public function provideInvalidSyntaxStyles()
    {
        return [
            ['each #item, #key in $something'],
            ['each #item in $something'],
            ['each #anything'],
            ['each $something'],
            ['each $item, #anything in $something'],
            ['each $item, $key'],
            ['each $item, $key in'],
        ];
    }

    /**
     * @covers ::scan
     * @covers \Phug\Lexer\Scanner\Partial\NamespaceAndTernaryTrait::checkForTernary
     * @covers \Phug\Lexer\Scanner\Partial\NamespaceAndTernaryTrait::checkForNamespaceAndTernary
     * @dataProvider provideExpressions
     */
    public function testWithItemOnly($expr)
    {

        /** @var EachToken $tok */
        list($tok) = $this->assertTokens("each \$item in $expr", [EachToken::class]);

        self::assertSame('item', $tok->getItem());
        self::assertSame($expr, $tok->getSubject());
    }

    /**
     * @covers ::scan
     * @covers \Phug\Lexer\Scanner\Partial\NamespaceAndTernaryTrait::checkForTernary
     * @covers \Phug\Lexer\Scanner\Partial\NamespaceAndTernaryTrait::checkForNamespaceAndTernary
     * @dataProvider provideExpressions
     */
    public function testWithItemAndKey($expr)
    {

        /** @var EachToken $tok */
        list($tok) = $this->assertTokens("each \$someItem, \$someKey in $expr", [EachToken::class]);

        self::assertSame('someItem', $tok->getItem());
        self::assertSame('someKey', $tok->getKey());
        self::assertSame($expr, $tok->getSubject());
    }

    /**
     * @covers ::scan
     * @dataProvider provideExpressions
     */
    public function testExpandedWithItemOnly($expr)
    {

        /** @var EachToken $tok */
        list($tok) = $this->assertTokens("each \$item in $expr: p Some Text", [
            EachToken::class,
            ExpansionToken::class,
            TagToken::class,
            TextToken::class,
        ]);

        self::assertSame('item', $tok->getItem());
        self::assertSame($expr, $tok->getSubject());
    }

    /**
     * @covers ::scan
     * @dataProvider provideExpressions
     */
    public function testExpandedWithItemAndKey($expr)
    {

        /** @var EachToken $tok */
        list($tok) = $this->assertTokens("each \$someItem, \$someKey in $expr: p Some Text", [
            EachToken::class,
            ExpansionToken::class,
            TagToken::class,
            TextToken::class,
        ]);

        self::assertSame('someItem', $tok->getItem());
        self::assertSame('someKey', $tok->getKey());
        self::assertSame($expr, $tok->getSubject());
    }

    /**
     * @dataProvider      provideInvalidSyntaxStyles
     * @covers            ::scan
     * @expectedException \Phug\LexerException
     */
    public function testThatItFailsWithInvalidSyntax($syntax)
    {
        foreach ($this->lexer->lex($syntax) as $token) {
        }
    }

    /**
     * @covers                   ::scan
     * @expectedException        \Phug\LexerException
     * @expectedExceptionMessage `each`-statement has no subject to operate on
     */
    public function testEachWithoutSubject()
    {
        foreach ($this->lexer->lex("each \$i in \n  p") as $token) {
        }
    }
}
