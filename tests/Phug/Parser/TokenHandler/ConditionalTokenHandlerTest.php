<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\ConditionalTokenHandler;
use Phug\Test\AbstractParserTest;
use Phug\Test\Utils\ExceptionAnnotationReader;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\ConditionalTokenHandler
 */
class ConditionalTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleConditionalToken
     */
    public function testHandleToken()
    {
        $this->assertNodes("if 1\n  p", [
            '[DocumentNode]',
            '  [ConditionalNode]',
            '    [ElementNode]',
        ]);
    }

    /**
     * @covers                   ::handleConditionalToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass conditional tokens to ConditionalTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new ConditionalTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
