<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\AutoCloseToken;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\Node\TextNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\AutoCloseTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\TokenHandler\AutoCloseTokenHandler
 */
class AutoCloseTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     */
    public function testHandleToken()
    {
        $this->assertNodes('tag', [
            '[DocumentNode]',
            '  [ElementNode]',
        ]);
        $this->assertNodes('tag/', [
            '[DocumentNode]',
            '  [ElementNode]',
        ]);

        $document = $this->parser->parse('tag');
        $element = $document->getChildren()[0];

        self::assertFalse($element->isAutoClosed());

        $document = $this->parser->parse('tag/');
        $element = $document->getChildren()[0];

        self::assertTrue($element->isAutoClosed());

        $template = "body\n".
            "  foo\n".
            "  foo(bar='baz')\n".
            "  foo/\n".
            "  foo(bar='baz')/\n".
            "  foo /\n".
            "  foo(bar='baz') /\n".
            "  #{'foo'}/\n".
            "  #{'foo'}(bar='baz')/\n".
            "  #{'foo'} /\n".
            "  #{'foo'}(bar='baz') /\n".
            "  //- can have a single space after them\n".
            "  img \n".
            "  //- can have lots of white space after them\n".
            "  img    \n".
            "  #{\n".
            "    'foo'\n".
            "  }/\n";
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ElementNode]',
            '    [ElementNode]',
            '    [ElementNode]',
            '    [ElementNode]',
            '    [ElementNode]',
            '      [TextNode]',
            '    [ElementNode]',
            '      [TextNode]',
            '    [ElementNode]',
            '    [ElementNode]',
            '    [ElementNode]',
            '      [TextNode]',
            '    [ElementNode]',
            '      [TextNode]',
            '    [CommentNode]',
            '      [TextNode]',
            '    [ElementNode]',
            '      [TextNode]',
            '    [CommentNode]',
            '      [TextNode]',
            '    [ElementNode]',
            '      [TextNode]',
            '    [ElementNode]',
        ]);
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You can only pass auto-close tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div/'));
        $handler = new AutoCloseTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \Phug\ParserException
     * @expectedExceptionMessage Auto-close operators can only be used on elements
     */
    public function testHandleClassOnWrongNode()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('| foo'));
        $state->setCurrentNode(new TextNode());
        $handler = new AutoCloseTokenHandler();
        $handler->handleToken(new AutoCloseToken(), $state);
    }
}
