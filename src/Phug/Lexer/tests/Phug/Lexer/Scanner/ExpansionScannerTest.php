<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer;
use Phug\Lexer\Scanner\ExpansionScanner;
use Phug\Lexer\State;
use Phug\Lexer\Token\ExpansionToken;
use Phug\Lexer\Token\FilterToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class ExpansionScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\ExpansionScanner
     * @covers \Phug\Lexer\Scanner\ExpansionScanner::scan
     */
    public function testStandaloneExpansion()
    {
        $this->assertTokens(':', [
            ExpansionToken::class,
        ]);
    }

    /**
     * @covers \Phug\Lexer\Scanner\ExpansionScanner
     * @covers \Phug\Lexer\Scanner\ExpansionScanner::scan
     */
    public function testTagExpansion()
    {
        /** @var TagToken $tok */
        list($tok) = $this->assertTokens('some-tag:', [
            TagToken::class,
            ExpansionToken::class,
        ]);
        self::assertSame('some-tag', $tok->getName());

        list($tok) = $this->assertTokens('some:namespaced:tag:', [
            TagToken::class,
            ExpansionToken::class,
        ]);
        self::assertSame('some:namespaced:tag', $tok->getName());
    }

    /**
     * @covers \Phug\Lexer\Scanner\ExpansionScanner
     * @covers \Phug\Lexer\Scanner\ExpansionScanner::scan
     */
    public function testFilterExpansion()
    {
        /** @var FilterToken $tok */
        list($tok) = $this->assertTokens(':some-filter a', [
            FilterToken::class,
            TextToken::class,
        ]);
        self::assertSame('some-filter', $tok->getName());

        list($tok) = $this->assertTokens(':some:namespaced:filter a', [
            FilterToken::class,
            TextToken::class,
        ]);
        self::assertSame('some:namespaced:filter', $tok->getName());

        $this->assertTokens(':some-filter: a', [
            ExpansionToken::class,
            TagToken::class,
            ExpansionToken::class,
            TagToken::class,
        ]);
        $this->assertTokens(':some:namespaced:filter: a', [
            ExpansionToken::class,
            TagToken::class,
            ExpansionToken::class,
            TagToken::class,
        ]);
    }

    /**
     * @covers \Phug\Lexer\Scanner\ExpansionScanner
     * @covers \Phug\Lexer\Scanner\ExpansionScanner::scan
     */
    public function testExpansionQuit()
    {
        $state = new State(new Lexer(), 'p', []);
        $scanners = [
            'tag' => ExpansionScanner::class,
        ];
        $tokens = [];
        foreach ($state->loopScan($scanners) as $token) {
            $tokens[] = $token;
        }

        self::assertSame(0, count($tokens));
    }
}
