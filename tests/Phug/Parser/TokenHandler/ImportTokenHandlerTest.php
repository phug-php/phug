<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\ImportTokenHandler;
use Phug\Test\AbstractParserTest;
use Phug\Test\Utils\ExceptionAnnotationReader;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\ImportTokenHandler
 */
class ImportTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleImportToken
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
     * @covers                   ::handleImportToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass import tokens to ImportTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new ImportTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }

    /**
     * @covers                   ::handleImportToken
     * @covers                   ::isEmptyDocument
     *
     * @expectedException        \Phug\ParserException
     *
     * @expectedExceptionMessage extends should be the very first statement in a document
     */
    public function testDivTagBeforeExtends()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $this->parser->parse("div\nextends foo");
    }

    /**
     * @covers                   ::handleImportToken
     * @covers                   ::isEmptyDocument
     *
     * @expectedException        \Phug\ParserException
     *
     * @expectedExceptionMessage extends should be the very first statement in a document
     */
    public function testVisibleCommentBeforeExtends()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $this->parser->parse("// visible comment\nextends foo");
    }

    /**
     * @covers                   ::handleImportToken
     * @covers                   ::isEmptyDocument
     *
     * @expectedException        \Phug\ParserException
     *
     * @expectedExceptionMessage extends should be the very first statement in a document
     */
    public function testMultipleThingsBeforeExtends()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $this->parser->parse("//- visible comment\nmixin bar()\n  p bar\n.class\nextends foo");
    }
}
