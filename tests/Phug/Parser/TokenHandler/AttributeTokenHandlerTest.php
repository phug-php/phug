<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\Node\AssignmentNode;
use Phug\Parser\Node\AttributeNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\AttributeEndTokenHandler;
use Phug\Parser\TokenHandler\AttributeStartTokenHandler;
use Phug\Parser\TokenHandler\AttributeTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\AttributeTokenHandler
 */
class AttributeTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     */
    public function testNoCurrentNode()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('(a)'), [
            'token_handlers' => [
                AttributeStartToken::class => AttributeStartTokenHandler::class,
                AttributeEndToken::class   => AttributeEndTokenHandler::class,
                AttributeToken::class      => AttributeTokenHandler::class,
            ],
        ]);
        $handler = new AttributeStartTokenHandler();
        $handler->handleToken(new AttributeStartToken(), $state);
        $state->setCurrentNode(null);
        $handler = new AttributeTokenHandler();
        $handler->handleToken(new AttributeToken(), $state);
        /** @var ElementNode $element */
        $element = $state->getCurrentNode();

        self::assertInstanceof(ElementNode::class, $element);
    }

    /**
     * @covers                   ::<public>
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass attribute tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new AttributeTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }

    /**
     * @covers ::<public>
     */
    public function testHandleTokenKeepOrder()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('(b="b")(a="a" c="c")'));
        $handler = new AttributeTokenHandler();
        $assignmentNode = new AssignmentNode();
        $assignmentNode->setOrder(5);
        $state->setCurrentNode($assignmentNode);
        $token = new AttributeToken();
        $token->setName('b');
        $token->setValue('b');
        $handler->handleToken(new AttributeToken(), $state);

        /** @var AssignmentNode $currentNode */
        $currentNode = $state->getCurrentNode();

        self::assertSame($currentNode, $assignmentNode);

        $attribute = iterator_to_array($currentNode->getAttributes())[0];

        self::assertInstanceOf(AttributeNode::class, $attribute);
        self::assertSame(5, $attribute->getOrder());
    }

    /**
     * @covers \Phug\Parser\TokenHandler\AttributeEndTokenHandler::<public>
     * @covers \Phug\Parser\TokenHandler\AttributeStartTokenHandler::<public>
     * @covers ::<public>
     */
    public function testHandleTokenFull()
    {
        $code = '+a(a, b, ...c)';
        $this->assertNodes($code, [
            '[DocumentNode]',
            '  [MixinCallNode]',
        ]);
        $document = $this->parser->parse($code);
        $attributes = [];
        $variadicStatuses = [];
        /** @var ElementNode $element */
        $element = $document->getChildren()[0];
        $storage = $element->getAttributes();
        foreach ($storage as $attribute) {
            /* @var AttributeNode $attribute */
            self::assertInstanceOf(AttributeNode::class, $attribute);
            $attributes[] = $attribute->getValue();
            $variadicStatuses[] = $attribute->isVariadic();
        }

        self::assertSame(['a', 'b', 'c'], $attributes);
        self::assertSame([false, false, true], $variadicStatuses);
    }

    /**
     * @covers \Phug\Parser\TokenHandler\AttributeEndTokenHandler::<public>
     * @covers \Phug\Parser\TokenHandler\AttributeStartTokenHandler::<public>
     * @covers ::<public>
     */
    public function testAttributePhpConcat()
    {
        $code = 'a(a=b . c)';
        $this->assertNodes($code, [
            '[DocumentNode]',
            '  [ElementNode]',
        ]);
        $document = $this->parser->parse($code);
        $attributes = [];
        /** @var ElementNode $element */
        $element = $document->getChildren()[0];
        $storage = $element->getAttributes();
        foreach ($storage as $attribute) {
            /* @var AttributeNode $attribute */
            self::assertInstanceOf(AttributeNode::class, $attribute);
            $attributes[] = $attribute->getValue();
        }

        self::assertSame(['b . c'], $attributes);
    }
}
