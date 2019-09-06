<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\ClassToken;
use Phug\Test\AbstractLexerTest;

class ClassScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\ClassScanner
     * @covers \Phug\Lexer\Scanner\ClassScanner::scan
     */
    public function testSingleClass()
    {

        /** @var ClassToken $tok */
        list($tok) = $this->assertTokens('.some-class', [
            ClassToken::class,
        ]);

        self::assertSame('some-class', $tok->getName());
    }

    /**
     * @covers \Phug\Lexer\Scanner\ClassScanner
     * @covers \Phug\Lexer\Scanner\ClassScanner::scan
     */
    public function testMultipleClasses()
    {

        /**
         * @var ClassToken
         * @var ClassToken $b
         * @var ClassToken $c
         * @var ClassToken $d
         */
        list($a, $b, $c, $d) = $this->assertTokens('.a-class.b-class.c-class.d-class', [
            ClassToken::class,
            ClassToken::class,
            ClassToken::class,
            ClassToken::class,
        ]);

        self::assertSame('a-class', $a->getName());
        self::assertSame('b-class', $b->getName());
        self::assertSame('c-class', $c->getName());
        self::assertSame('d-class', $d->getName());
    }

    public function testCommonNamingPatterns()
    {

        /** @var ClassToken $tok */
        list($tok) = $this->assertTokens('.--some-class', [
            ClassToken::class,
        ]);

        self::assertSame('--some-class', $tok->getName());

        /** @var ClassToken $tok */
        list($tok) = $this->assertTokens('.some--class__sub-element', [
            ClassToken::class,
        ]);

        self::assertSame('some--class__sub-element', $tok->getName());
    }
}
