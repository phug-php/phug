<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\ExpansionTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\ExpansionTokenHandler
 */
class ExpansionTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleExpansionToken
     */
    public function testHandleToken()
    {
        $this->assertNodes('p: p', [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ElementNode]',
        ]);
        $this->assertNodes('p: p: i Hello', [
            '[DocumentNode]',
            '  [ElementNode outer=ElementNode]',
            '    [ElementNode]',
            '      [TextNode]',
        ]);
        $this->assertNodes("ul\n  li.list-item: .foo: #bar baz", [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ElementNode outer=ElementNode]',
            '      [ElementNode]',
            '        [TextNode]',
        ]);
        $this->assertNodes("mixin c\n  div\n    block\n+c(): +c()", [
            '[DocumentNode]',
            '  [MixinNode]',
            '    [ElementNode]',
            '      [BlockNode]',
            '  [MixinCallNode]',
            '    [MixinCallNode]',
        ]);
        $template = "- var friends = 1\n".
            "case friends\n".
            "  when 0: p you have no friends\n".
            "  when 1: p you have a friend\n".
            "  default: p you have #{friends} friends\n".
            '- var friends = 0';
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [CodeNode]',
            '    [TextNode]',
            '  [CaseNode]',
            '    [WhenNode]',
            '      [ElementNode]',
            '        [TextNode]',
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
     * @covers                   ::handleExpansionToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass expansion tokens to ExpansionTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new ExpansionTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }

    /**
     * @covers ::handleExpansionToken
     * @covers \Phug\Parser\State::throwException
     */
    public function testHandleTokenElementException()
    {
        $message = null;

        try {
            $this->parser->parse(':', 'my-path');
        } catch (\Phug\ParserException $exp) {
            $message = $exp->getMessage();
        }

        self::assertStringContains('Expansion needs an element to work on', $message);
        self::assertStringContains('Path: my-path', $message);
    }
}
