<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\Node\DocumentNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\TagTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\TokenHandler\TagTokenHandler
 */
class TagTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     * @covers \Phug\Parser\Node\ElementNode::<public>
     */
    public function testHandleToken()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'), [
            'token_handlers' => [
                TagToken::class => TagTokenHandler::class,
            ],
        ]);

        $tag = new TagToken();
        $tag->setName('foo');
        $state->handleToken($tag);

        self::assertSame('foo', $state->getCurrentNode()->getName());

        $elements = $this->parser->parse("foo:bar\nfoo-bar\nA:B")->getChildren();

        self::assertSame('foo:bar', $elements[0]->getName());
        self::assertSame('foo-bar', $elements[1]->getName());
        self::assertSame('A:B', $elements[2]->getName());

        $document = $this->parser->parse('img');
        self::assertFalse($document->getChildAt(0)->isAutoClosed());

        $document = $this->parser->parse('img/');
        self::assertTrue($document->getChildAt(0)->isAutoClosed());
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You can only pass tag tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new TagTokenHandler();
        $handler->handleToken(new AttributeToken(), $state);
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \Phug\ParserException
     * @expectedExceptionMessage Failed to parse: Tags can only be used on elements
     */
    public function testHandleTokenElementTagsException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'), [
            'token_handlers' => [
                TagToken::class => TagTokenHandler::class,
            ],
        ]);

        $tag = new TagToken();
        $tag->setName('foo');
        $state->setCurrentNode(new DocumentNode());
        $state->handleToken($tag);
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \Phug\ParserException
     * @expectedExceptionMessage Failed to parse: The element already has a tag name
     */
    public function testHandleTokenTagNameException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'), [
            'token_handlers' => [
                TagToken::class => TagTokenHandler::class,
            ],
        ]);

        $tag = new TagToken();
        $tag->setName('foo');
        $state->handleToken($tag);

        $tag = new TagToken();
        $tag->setName('foo');
        $state->handleToken($tag);
    }
}
