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

/**
 * @coversDefaultClass Phug\Parser\TokenHandler\AttributeTokenHandler
 */
class AttributeStartTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers Phug\Parser\TokenHandler\AttributeEndTokenHandler::<public>
     * @covers Phug\Parser\TokenHandler\AttributeStartTokenHandler::<public>
     * @covers ::<public>
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
     * @covers Phug\Parser\TokenHandler\AttributeEndTokenHandler::<public>
     * @covers Phug\Parser\TokenHandler\AttributeStartTokenHandler::<public>
     * @covers ::<public>
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
     * @covers                   ::<public>
     * @covers                   Phug\Parser\TokenHandler\AttributeStartTokenHandler::<public>
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You can only pass attribute start tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new AttributeStartTokenHandler();
        $handler->handleToken(new AttributeToken(), $state);
    }

    /**
     * @covers                   ::<public>
     * @covers                   \Phug\Parser\TokenHandler\AttributeStartTokenHandler::<public>
     * @expectedException        \Phug\ParserException
     * @expectedExceptionMessage Failed to parse: Attributes can only be placed on
     * @expectedExceptionMessage element, assignment, import, variable,
     * @expectedExceptionMessage mixin and mixinCall
     */
    public function testHandleTokenElementTagsException()
    {
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
     * @covers                   ::<public>
     * @covers                   \Phug\Parser\TokenHandler\AttributeStartTokenHandler::<public>
     * @expectedException        \Phug\ParserException
     * @expectedExceptionMessage Attribute list not closed
     */
    public function testHandleTokenListNotClosedException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''), [
            'token_handlers' => [
                AttributeStartToken::class => AttributeStartTokenHandler::class,
            ],
        ]);

        $state->handleToken(new AttributeStartToken());
    }
}
