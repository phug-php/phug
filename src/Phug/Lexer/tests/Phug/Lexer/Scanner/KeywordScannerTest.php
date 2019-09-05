<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\KeywordToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class KeywordScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\KeywordScanner
     * @covers \Phug\Lexer\Scanner\KeywordScanner::scan
     */
    public function testKeywords()
    {
        $lexer = new Lexer([
            'keywords' => [
                'foo'     => 'FOO',
                'bar:baz' => 'BAR',
            ],
        ]);
        $tokens = [];
        foreach ($lexer->lex(implode("\n", [
            'foo foo',
            '  div foo',
            '  bar:baz baz',
        ])) as $token) {
            $tokens[] = $token;
        }
        self::assertSame([
            KeywordToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            TextToken::class,
            NewLineToken::class,
            KeywordToken::class,
        ], array_map('get_class', $tokens));
        /** @var KeywordToken $foo */
        $foo = $tokens[0];
        /** @var KeywordToken $bar */
        $bar = $tokens[6];

        self::assertSame('foo', $foo->getName());
        self::assertSame('foo', $foo->getValue());
        self::assertSame('bar:baz', $bar->getName());
        self::assertSame('baz', $bar->getValue());
    }
}
