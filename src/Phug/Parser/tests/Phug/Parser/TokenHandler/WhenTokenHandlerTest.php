<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\WhenTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\TokenHandler\WhenTokenHandler
 */
class WhenTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     */
    public function testHandleToken()
    {
        $this->assertNodes("when 42\n  p", [
            '[DocumentNode]',
            '  [WhenNode]',
            '    [ElementNode]',
        ]);
        $template = "case friends\n".
            "  when 0\n".
            "  when 1\n".
            "    p you have very few friends\n".
            "  when 2\n".
            "    p you have #{friends} friends\n\n".
            "- var friend = 'Tim:G'";
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [CaseNode]',
            '    [WhenNode]',
            '    [WhenNode]',
            '      [ElementNode]',
            '        [TextNode]',
            '    [WhenNode]',
            '      [ElementNode]',
            '        [TextNode]',
            '        [ExpressionNode]',
            '        [TextNode]',
            '  [CodeNode]',
            '    [TextNode]',
        ]);
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You can only pass when tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new WhenTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
