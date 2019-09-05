<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\ClassToken;
use Phug\Lexer\Token\MixinCallToken;
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
    }
}
