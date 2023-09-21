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
use Phug\Test\Utils\ExceptionAnnotationReader;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\TagTokenHandler
 */
class TagTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleTagToken
     * @covers \Phug\Parser\Node\ElementNode::isAutoClosed
     * @covers \Phug\Parser\Node\ElementNode::autoClose
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
     * @covers                   ::handleTagToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass tag tokens to TagTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new TagTokenHandler();
        $handler->handleToken(new AttributeToken(), $state);
    }

    /**
     * @covers                   ::handleTagToken
     *
     * @expectedException        \Phug\ParserException
     *
     * @expectedExceptionMessage Failed to parse: Tags can only happen on elements
     */
    public function testHandleTokenElementTagsException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

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
     * @covers                   ::handleTagToken
     *
     * @expectedException        \Phug\ParserException
     *
     * @expectedExceptionMessage Failed to parse: The element already has a tag name
     */
    public function testHandleTokenTagNameException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

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
