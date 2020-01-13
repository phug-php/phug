<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\BlockToken;
use Phug\Lexer\Token\ClassToken;
use Phug\Lexer\Token\ExpansionToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\MixinCallToken;
use Phug\Lexer\Token\MixinToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class MixinCallScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\MixinCallScanner
     * @covers \Phug\Lexer\Scanner\MixinCallScanner::scan
     */
    public function testMixinCall()
    {
        /* @var MixinCallToken $tok */
        list($tok) = $this->assertTokens('+a', [
            MixinCallToken::class,
        ]);

        self::assertSame('a', $tok->getName());

        /* @var MixinCallToken $tok */
        list($mixin, $class) = $this->assertTokens('+foo.bar', [
            MixinCallToken::class,
            ClassToken::class,
        ]);

        self::assertSame('foo', $mixin->getName());
        self::assertSame('bar', $class->getName());

        /* @var MixinCallToken $tok */
        list($mixin, $class) = $this->assertTokens('+#{\'foo\'}.bar', [
            MixinCallToken::class,
            ClassToken::class,
        ]);

        self::assertSame('#{\'foo\'}', $mixin->getName());
        self::assertSame('bar', $class->getName());

        /* @var MixinCallToken $mixin */
        /* @var AttributeToken $argument */
        /* @var AttributeToken $fill */
        /* @var AttributeToken $name */
        list($mixin, , $argument, , , $fill, $name) = $this->assertTokens('+field(0)(fill=0, name="bar")', [
            MixinCallToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        self::assertSame('field', $mixin->getName());
        self::assertSame('0', $argument->getName());
        self::assertSame('fill', $fill->getName());
        self::assertSame('0', $fill->getValue());
        self::assertSame('name', $name->getName());
        self::assertSame('"bar"', $name->getValue());

        $code = implode("\n", [
            'div: mixin bar',
            '  p bar',
            '  block',
            'footer',
            '  footer: +#{$foo}: span i',
        ]);
        $tokens = [];

        foreach ($this->lexer->lex($code) as $token) {
            $tokens[] = get_class($token);
        }

        self::assertSame([
            TagToken::class,
            ExpansionToken::class,
            MixinToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            TextToken::class,
            NewLineToken::class,
            BlockToken::class,
            NewLineToken::class,
            OutdentToken::class,
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            ExpansionToken::class,
            MixinCallToken::class,
            ExpansionToken::class,
            TagToken::class,
            TextToken::class,
        ], $tokens);
    }

    /**
     * @covers \Phug\Lexer\Scanner\MixinCallScanner
     * @covers \Phug\Lexer\Scanner\MixinCallScanner::scan
     * @covers \Phug\Lexer\State::loadScanner
     * @covers \Phug\Lexer::getRegExpOption
     *
     * @throws \Exception
     */
    public function testMixinCallOptions()
    {
        $this->assertTokens('@a', [
            TextToken::class,
        ]);

        /* @var MixinCallToken $tok */
        list($tok) = $this->assertTokens('@ab', [
            MixinCallToken::class,
        ], new Lexer(['mixin_call_keyword' => ['\\+', '@']]));

        self::assertSame('ab', $tok->getName());
    }
}
