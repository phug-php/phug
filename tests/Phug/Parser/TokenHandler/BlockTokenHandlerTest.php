<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\BlockTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\BlockTokenHandler
 */
class BlockTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleBlockToken
     */
    public function testHandleToken()
    {
        $this->assertNodes('block bar', [
            '[DocumentNode]',
            '  [BlockNode]',
        ]);
    }

    /**
     * @covers                   ::handleBlockToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass block tokens to BlockTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new BlockTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
