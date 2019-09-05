<?php

namespace Phug\Test;

use Phug\Lexer;
use Phug\Parser;
use Phug\Parser\Node\DocumentNode;

/**
 * @coversDefaultClass Phug\Parser
 */
class ParserTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     */
    public function testParseAssignment()
    {
        self::assertInstanceOf(DocumentNode::class, $this->parser->parse('&some-assignment'));
        self::assertInstanceOf(Lexer::class, $this->parser->getLexer());
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Passed lexer class ErrorException is not a valid Phug\Lexer
     */
    public function testWrongLexerClassNameOption()
    {
        new Parser([
            'lexer_class_name' => \ErrorException::class,
        ]);
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Passed token handler needs to implement Phug\Parser\TokenHandlerInterface
     */
    public function testWrongTokenHandler()
    {
        $this->parser->setTokenHandler('error', \ErrorException::class);
    }

    /**
     * @covers                   ::<public>
     * @covers                   ::dumpNode
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage parser_state_class_name needs to be a valid Phug\Parser\State sub class
     */
    public function testWrongStateClassNameOption()
    {
        $parser = new Parser([
            'parser_state_class_name' => \ErrorException::class,
        ]);
        $parser->parse('');
    }

    /**
     * @covers ::<public>
     * @covers ::dumpNode
     * @covers ::getNodeName
     */
    public function testDump()
    {
        $this->assertNodes("div\n  section: p Hello\nfooter", [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ElementNode]',
            '      [ElementNode]',
            '        [TextNode]',
            '  [ElementNode]',
        ]);
        $this->assertNodes("div: div\n  section: p: span i", [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ElementNode]',
            '      [ElementNode outer=ElementNode]',
            '        [ElementNode]',
            '          [TextNode]',
        ]);
        $parser = new Parser([
            'detailed_dump' => true,
        ]);
        self::assertSame(implode("\n", [
            '[Phug\Parser\Node\DocumentNode]',
            '  [Phug\Parser\Node\ElementNode:div outer=Phug\Parser\Node\ElementNode:section#\'foo\'(bar="9")]',
            '    [Phug\Parser\Node\MixinCallNode]',
            '      [Phug\Parser\Node\ElementNode:.\'biz\']',
            '        [Phug\Parser\Node\ElementNode:a(href="#")]',
            '          [Phug\Parser\Node\TextNode]',
        ]), $parser->dump(implode("\n", [
            'section#foo(bar="9"): div: +mixin-call()',
            '  .biz: a(href="#") Go',
        ])));
    }
}
