<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\CaseTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\CaseTokenHandler
 */
class CaseTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleCaseToken
     */
    public function testHandleToken()
    {
        $this->assertNodes('case 42', [
            '[DocumentNode]',
            '  [CaseNode]',
        ]);
    }

    /**
     * @covers                   ::handleCaseToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass case tokens to CaseTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new CaseTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
