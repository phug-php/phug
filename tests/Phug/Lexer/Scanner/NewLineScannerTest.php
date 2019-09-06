<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer;
use Phug\Lexer\Scanner\NewLineScanner;
use Phug\Lexer\State;
use Phug\Lexer\Token\NewLineToken;
use Phug\Test\AbstractLexerTest;

class NewLineScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\NewLineScanner
     * @covers \Phug\Lexer\Scanner\NewLineScanner::scan
     */
    public function testNewLine()
    {
        $this->assertTokens("\n", [
            NewLineToken::class,
        ]);
    }

    /**
     * @covers \Phug\Lexer\Scanner\NewLineScanner
     * @covers \Phug\Lexer\Scanner\NewLineScanner::scan
     */
    public function testNewLineQuit()
    {
        $state = new State(new Lexer(), 'p', []);
        $scanners = [
            'new_line' => NewLineScanner::class,
        ];
        $tokens = [];
        foreach ($state->loopScan($scanners) as $token) {
            $tokens[] = $token;
        }

        self::assertSame(0, count($tokens));
    }
}
