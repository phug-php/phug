<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\ClassToken;
use Phug\Lexer\Token\IdToken;
use Phug\Lexer\Token\TagToken;
use Phug\Test\AbstractLexerTest;

class IdScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\IdScanner
     * @covers \Phug\Lexer\Scanner\IdScanner::scan
     */
    public function testId()
    {
        /** @var IdToken $tok */
        list(, $tok) = $this->assertTokens('p#some-id', [
            TagToken::class,
            IdToken::class,
        ]);

        self::assertSame('some-id', $tok->getName());

        /** @var IdToken $tok */
        list(, $tok) = $this->assertTokens('p#some-id.foo', [
            TagToken::class,
            IdToken::class,
            ClassToken::class,
        ]);

        self::assertSame('some-id', $tok->getName());
    }
}
