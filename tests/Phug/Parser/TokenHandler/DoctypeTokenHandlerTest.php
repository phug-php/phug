<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\DoctypeTokenHandler;
use Phug\Test\AbstractParserTest;
use Phug\Test\Utils\ExceptionAnnotationReader;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\DoctypeTokenHandler
 */
class DoctypeTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleDoctypeToken
     */
    public function testHandleToken()
    {
        $this->assertNodes('doctype html', [
            '[DocumentNode]',
            '  [DoctypeNode]',
        ]);
    }

    /**
     * @covers                   ::handleDoctypeToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass doctype tokens to DoctypeTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new DoctypeTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
