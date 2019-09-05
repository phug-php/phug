<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\BlockToken;
use Phug\Test\AbstractLexerTest;

class BlockScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\BlockScanner
     * @covers \Phug\Lexer\Scanner\BlockScanner::scan
     */
    public function testScan()
    {
        /** @var BlockToken $tok */
        list($tok) = $this->assertTokens('block some-block', [
            BlockToken::class,
        ]);

        self::assertSame('some-block', $tok->getName());
        self::assertSame('replace', $tok->getMode());

        /** @var BlockToken $tok */
        list($tok) = $this->assertTokens('block append some-block', [
            BlockToken::class,
        ]);

        self::assertSame('some-block', $tok->getName());
        self::assertSame('append', $tok->getMode());

        /** @var BlockToken $tok */
        list($tok) = $this->assertTokens('block prepend some-block', [
            BlockToken::class,
        ]);

        self::assertSame('some-block', $tok->getName());
        self::assertSame('prepend', $tok->getMode());

        /** @var BlockToken $tok */
        list($tok) = $this->assertTokens('block replace some-block', [
            BlockToken::class,
        ]);

        self::assertSame('some-block', $tok->getName());
        self::assertSame('replace', $tok->getMode());

        /** @var BlockToken $tok */
        list($tok) = $this->assertTokens('append some-block', [
            BlockToken::class,
        ]);

        self::assertSame('some-block', $tok->getName());
        self::assertSame('append', $tok->getMode());

        /** @var BlockToken $tok */
        list($tok) = $this->assertTokens('prepend some-block', [
            BlockToken::class,
        ]);

        self::assertSame('some-block', $tok->getName());
        self::assertSame('prepend', $tok->getMode());

        /** @var BlockToken $tok */
        list($tok) = $this->assertTokens('replace some-block', [
            BlockToken::class,
        ]);

        self::assertSame('some-block', $tok->getName());
        self::assertSame('replace', $tok->getMode());

        /** @var BlockToken $tok */
        list($tok) = $this->assertTokens('block', [
            BlockToken::class,
        ]);

        self::assertNull($tok->getName());
        self::assertSame('replace', $tok->getMode());
    }
}
