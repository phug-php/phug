<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\ClassToken;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\TextNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\ClassTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\ClassTokenHandler
 */
class ClassTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleClassToken
     * @covers \Phug\Parser\TokenHandler\Partial\StaticAttributeTrait::attachStaticAttribute
     */
    public function testHandleToken()
    {
        $this->assertNodes('.foo', [
            '[DocumentNode]',
            '  [ElementNode]',
        ]);
    }

    /**
     * @covers ::handleClassToken
     */
    public function testClassNamePassedToAttribute()
    {
        $document = $this->parser->parse('.foo');
        $element = $document->getChildren()[0];

        self::assertInstanceOf(ElementNode::class, $element);

        $className = null;
        foreach ($element->getAttributes() as $attribute) {
            if ($attribute->getName() === 'class') {
                $className = $attribute->getValue();
            }
        }

        self::assertSame("'foo'", $className);
    }

    /**
     * @covers                   ::handleClassToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass class tokens to ClassTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new ClassTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }

    /**
     * @covers                   ::handleClassToken
     * @covers                   \Phug\Parser\TokenHandler\AbstractTokenHandler::handleToken
     * @covers                   \Phug\Parser\TokenHandler\AbstractTokenHandler::onlyOnElement
     * @covers                   \Phug\Parser\TokenHandler\AbstractTokenHandler::createElementNodeIfMissing
     * @covers                   \Phug\Parser\TokenHandler\AbstractTokenHandler::assertCurrentNodeIs
     * @covers                   \Phug\Parser\TokenHandler\AbstractTokenHandler::getClassLastPart
     * @covers                   \Phug\Parser\TokenHandler\AbstractTokenHandler::getTypeName
     * @covers                   \Phug\Parser\TokenHandler\AbstractTokenHandler::getPluralTypeName
     *
     * @expectedException        \Phug\ParserException
     *
     * @expectedExceptionMessage Classes can only happen on elements and mixin-calls
     */
    public function testHandleClassOnWrongNode()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('| foo'));
        $state->setCurrentNode(new TextNode());
        $handler = new ClassTokenHandler();
        $handler->handleToken(new ClassToken(), $state);
    }
}
