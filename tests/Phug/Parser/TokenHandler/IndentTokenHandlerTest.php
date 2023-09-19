<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\IndentTokenHandler;
use Phug\Test\AbstractParserTest;
use Phug\Test\Utils\ExceptionAnnotationReader;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\IndentTokenHandler
 */
class IndentTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleIndentToken
     */
    public function testHandleSingleLine()
    {
        $this->assertNodes("p\n  p\n\t\t\t\tdiv", [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ElementNode]',
            '      [ElementNode]',
        ]);
    }

    /**
     * @covers                   ::handleIndentToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass indent tokens to IndentTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new IndentTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
