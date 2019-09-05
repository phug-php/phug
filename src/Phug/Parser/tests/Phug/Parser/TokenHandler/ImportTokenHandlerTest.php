<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\ImportTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\TokenHandler\ImportTokenHandler
 */
class ImportTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     * @covers ::isEmptyDocument
     */
    public function testHandleToken()
    {
        $this->assertNodes("extends layout\ninclude header", [
            '[DocumentNode]',
            '  [ImportNode]',
            '  [ImportNode]',
        ]);
        $this->assertNodes("//- invisible comment\n\nmixin bar\n  p bar\nextends layout", [
            '[DocumentNode]',
            '  [CommentNode]',
            '    [TextNode]',
            '  [MixinNode]',
            '    [ElementNode]',
            '      [TextNode]',
            '  [ImportNode]',
        ]);
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You can only pass import tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new ImportTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }

    /**
     * @covers                   ::<public>
     * @covers                   ::isEmptyDocument
     * @expectedException        \Phug\ParserException
     * @expectedExceptionMessage extends should be the very first statement in a document
     */
    public function testDivTagBeforeExtends()
    {
        $this->parser->parse("div\nextends foo");
    }

    /**
     * @covers                   ::<public>
     * @covers                   ::isEmptyDocument
     * @expectedException        \Phug\ParserException
     * @expectedExceptionMessage extends should be the very first statement in a document
     */
    public function testVisibleCommentBeforeExtends()
    {
        $this->parser->parse("// visible comment\nextends foo");
    }

    /**
     * @covers                   ::<public>
     * @covers                   ::isEmptyDocument
     * @expectedException        \Phug\ParserException
     * @expectedExceptionMessage extends should be the very first statement in a document
     */
    public function testMultipleThingsBeforeExtends()
    {
        $this->parser->parse("//- visible comment\nmixin bar()\n  p bar\n.class\nextends foo");
    }
}
