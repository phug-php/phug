<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\WhileTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\WhileTokenHandler
 */
class WhileTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleWhileToken
     */
    public function testHandleToken()
    {
        $this->assertNodes("while \$i < 3\n  p=\$i", [
            '[DocumentNode]',
            '  [WhileNode]',
            '    [ElementNode]',
            '      [ExpressionNode]',
        ]);
        $pug = "- var x = 1;\n".
            "ul\n".
            "  while x < 10\n".
            "    - x++;\n".
            "    li= x\n";
        $this->assertNodes($pug, [
            '[DocumentNode]',
            '  [CodeNode]',
            '    [TextNode]',
            '  [ElementNode]',
            '    [WhileNode]',
            '      [CodeNode]',
            '        [TextNode]',
            '      [ElementNode]',
            '        [ExpressionNode]',
        ]);
    }

    /**
     * @covers                   ::handleWhileToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass while tokens to WhileTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new WhileTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
