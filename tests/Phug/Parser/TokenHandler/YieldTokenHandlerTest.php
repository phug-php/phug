<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\YieldTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\YieldTokenHandler
 */
class YieldTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleYieldToken
     */
    public function testHandleToken()
    {
        $this->assertNodes('yield', [
            '[DocumentNode]',
            '  [YieldNode]',
        ]);
    }

    /**
     * @covers                   ::handleWhileToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass yield tokens to YieldTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new YieldTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
