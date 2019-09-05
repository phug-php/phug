<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\TextTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\TokenHandler\TextTokenHandler
 */
class TextTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     */
    public function testHandleToken()
    {
        $this->assertNodes('p foo', [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [TextNode]',
        ]);
        $this->assertNodes("p\n  | foo", [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [TextNode]',
        ]);
        $document = $this->parser->parse("p\n  | foo");
        self::assertSame('  ', $document->getChildAt(0)->getChildAt(0)->getIndent());
    }

    /**
     * @covers ::<public>
     */
    public function testHandleMarkup()
    {
        $this->assertNodes(implode("\n", [
            'body',
            '  if (test == true)',
            '    h1 Phug',
            '  else',
            '    <!---->',
            '  div test',
        ]), [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ConditionalNode]',
            '      [ElementNode]',
            '        [TextNode]',
            '    [ConditionalNode]',
            '      [TextNode]',
            '    [ElementNode]',
            '      [TextNode]',
        ]);
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You can only pass text tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new TextTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
