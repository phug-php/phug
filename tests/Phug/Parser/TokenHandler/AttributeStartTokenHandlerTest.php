<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Parser;
use Phug\Parser\Node\AttributeNode;
use Phug\Parser\Node\DocumentNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\AttributeEndTokenHandler;
use Phug\Parser\TokenHandler\AttributeStartTokenHandler;
use Phug\Test\AbstractParserTest;
use Phug\Test\Utils\ExceptionAnnotationReader;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\AttributeTokenHandler
 */
class AttributeStartTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers \Phug\Parser\TokenHandler\AttributeEndTokenHandler::handleAttributeEndToken
     * @covers \Phug\Parser\TokenHandler\AttributeStartTokenHandler::handleAttributeStartToken
     * @covers ::handleAttributeToken
     */
    public function testHandleTokenEmpty()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('()'), [
            'token_handlers' => [
                AttributeStartToken::class => AttributeStartTokenHandler::class,
                AttributeEndToken::class   => AttributeEndTokenHandler::class,
            ],
        ]);
        $handler = new AttributeStartTokenHandler();
        $handler->handleToken(new AttributeStartToken(), $state);

        self::assertInstanceof(ElementNode::class, $state->getCurrentNode());
    }

    /**
     * @covers \Phug\Parser\TokenHandler\AttributeEndTokenHandler::handleAttributeEndToken
     * @covers \Phug\Parser\TokenHandler\AttributeStartTokenHandler::handleAttributeStartToken
     * @covers ::handleAttributeToken
     */
    public function testHandleTokenFull()
    {
        $this->assertNodes('(a b)', [
            '[DocumentNode]',
            '  [ElementNode]',
        ]);
        $document = $this->parser->parse('(a b)');
        $attributes = [];
        $storage = $document->getChildren()[0]->getAttributes();
        foreach ($storage as $attribute) {
            self::assertInstanceOf(AttributeNode::class, $attribute);
            $attributes[] = $attribute->getName();
        }
        self::assertSame(['a', 'b'], $attributes);
    }

    /**
     * @covers                   ::handleAttributeToken
     * @covers                   \Phug\Parser\TokenHandler\AttributeStartTokenHandler::handleAttributeStartToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass attribute-start tokens to AttributeStartTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new AttributeStartTokenHandler();
        $handler->handleToken(new AttributeToken(), $state);
    }

    /**
     * @covers                   ::handleAttributeToken
     * @covers                   \Phug\Parser\TokenHandler\AttributeStartTokenHandler::handleAttributeStartToken
     *
     * @expectedException        \Phug\ParserException
     *
     * @expectedExceptionMessage Failed to parse: Attribute-starts can only happen on elements, assignments, imports, variables, mixins, mixin-calls and filters
     */
    public function testHandleTokenElementTagsException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('('), [
            'token_handlers' => [
                AttributeStartToken::class      => AttributeStartTokenHandler::class,
                AttributeEndTokenHandler::class => AttributeEndTokenHandler::class,
            ],
        ]);

        $start = new AttributeStartToken();
        $state->setCurrentNode(new DocumentNode());
        $state->handleToken($start);
    }

    /**
     * @covers                   ::handleAttributeToken
     * @covers                   \Phug\Parser\TokenHandler\AttributeStartTokenHandler::handleAttributeStartToken
     *
     * @expectedException        \Phug\ParserException
     *
     * @expectedExceptionMessage Attribute list not closed
     */
    public function testHandleTokenListNotClosedException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''), [
            'token_handlers' => [
                AttributeStartToken::class => AttributeStartTokenHandler::class,
            ],
        ]);

        $state->handleToken(new AttributeStartToken());
    }
}
