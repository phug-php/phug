<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\CommentTokenHandler;
use Phug\Test\AbstractParserTest;
use Phug\Test\Utils\ExceptionAnnotationReader;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\CommentTokenHandler
 */
class CommentTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleCommentToken
     */
    public function testHandleToken()
    {
        $this->assertNodes('// foo', [
            '[DocumentNode]',
            '  [CommentNode]',
            '    [TextNode]',
        ]);

        $this->assertNodes("//-\n  foo", [
            '[DocumentNode]',
            '  [CommentNode]',
            '    [TextNode]',
        ]);

        $documentNodes = $this->parser->parse('// foo')->getChildren();
        self::assertSame(' foo', $documentNodes[0]->getChildren()[0]->getValue());

        $documentNodes = $this->parser->parse("//-\n  foo")->getChildren();
        self::assertSame("\n  foo", $documentNodes[0]->getChildren()[0]->getValue());
    }

    /**
     * @covers                   ::handleCommentToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass comment tokens to CommentTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new CommentTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
