<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\IdTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\IdTokenHandler
 */
class IdTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleIdToken
     * @covers \Phug\Parser\TokenHandler\Partial\StaticAttributeTrait::attachStaticAttribute
     */
    public function testHandleToken()
    {
        $this->assertNodes('#foo', [
            '[DocumentNode]',
            '  [ElementNode]',
        ]);

        $element = $this->parser->parse('#foo')->getChildren()[0];

        self::assertNull($element->getName());
        self::assertSame("'foo'", $element->getAttribute('id'));

        $element = $this->parser->parse('p#bar-baz')->getChildren()[0];

        self::assertSame('p', $element->getName());
        self::assertSame("'bar-baz'", $element->getAttribute('id'));
    }

    /**
     * @covers                   ::handleIdToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass id tokens to IdTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new IdTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }

    /**
     * @covers                   ::handleIdToken
     *
     * @expectedException        \Phug\ParserException
     *
     * @expectedExceptionMessage Ids can only happen on elements and mixin-calls
     */
    public function testHandleTokenElementException()
    {
        $this->parser->parse('mixin foo#id');
    }
}
