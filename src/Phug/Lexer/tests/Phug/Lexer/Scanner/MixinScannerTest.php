<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\ClassToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\MixinToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Test\AbstractLexerTest;

class MixinScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\MixinScanner
     * @covers \Phug\Lexer\Scanner\MixinScanner::scan
     */
    public function testMixinCall()
    {
        /* @var MixinToken $tok */
        list($tok) = $this->assertTokens('mixin a', [
            MixinToken::class,
        ]);

        self::assertSame('a', $tok->getName());

        $this->assertTokens("mixin comment (title, str)\n  .comment", [
            MixinToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            NewLineToken::class,
            IndentToken::class,
            ClassToken::class,
        ]);
    }
}
