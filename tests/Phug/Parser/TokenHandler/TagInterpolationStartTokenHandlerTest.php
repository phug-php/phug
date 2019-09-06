<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\AttributeToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\TagInterpolationStartTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\TokenHandler\TagInterpolationStartTokenHandler
 */
class TagInterpolationStartTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     * @covers \Phug\Parser\State::getInterpolationStack
     * @covers \Phug\Parser\TokenHandler\TagInterpolationEndTokenHandler::<public>
     */
    public function testHandleToken()
    {
        $template = "p\n  |#[.i i] foo\n  | bar\n| biz";
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [TextNode]',
            '    [ElementNode]',
            '      [TextNode]',
            '    [TextNode]',
            '    [TextNode]',
            '  [TextNode]',
        ]);
        $document = $this->parser->parse($template);
        self::assertSame('i', $document->getChildAt(0)->getChildAt(1)->getChildAt(0)->getValue());

        $template = "p.\n  foo\n  #[a]";
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [TextNode]',
            '    [ElementNode]',
            '    [TextNode]',
        ]);
        $document = $this->parser->parse($template);
        self::assertSame('a', $document->getChildAt(0)->getChildAt(1)->getName());

        $template =
            "p bing #[strong foo]#[strong bar] bong\n\n".
            "p.\n  bing\n  #[strong foo]\n  bong #[strong bar]\nfooter";
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [TextNode]',
            '    [ElementNode]',
            '      [TextNode]',
            '    [ElementNode]',
            '      [TextNode]',
            '    [TextNode]',
            '  [ElementNode]',
            '    [TextNode]',
            '    [ElementNode]',
            '      [TextNode]',
            '    [TextNode]',
            '    [ElementNode]',
            '      [TextNode]',
            '    [TextNode]',
            '  [ElementNode]',
        ]);
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Parser\State::getInterpolationNode
     * @covers \Phug\Parser\State::popInterpolationNode
     * @covers \Phug\Parser\State::pushInterpolationNode
     * @covers \Phug\Parser\TokenHandler\ExpansionTokenHandler::<public>
     * @covers \Phug\Parser\TokenHandler\TagInterpolationEndTokenHandler::<public>
     */
    public function testExpansionWithInterpolations()
    {
        $template = implode("\n", [
            '#foo: article#bar #[blockquote#baz: p#foz #]',
            '  | This is an #[emph example] paragraph. For more information on #[span.foo whatever],',
            '  | click #[a(href="#") here]!',
            '  | This line has no tag.',
        ]);
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ElementNode]',
            '      [TextNode]',
            '      [ElementNode]',
            '        [ElementNode]',
            '          [TextNode]',
            '      [TextNode]',
            '      [TextNode]',
            '      [ElementNode]',
            '        [TextNode]',
            '      [TextNode]',
            '      [ElementNode]',
            '        [TextNode]',
            '      [TextNode]',
            '      [TextNode]',
            '      [ElementNode]',
            '        [TextNode]',
            '      [TextNode]',
            '      [TextNode]',
        ]);
        $document = $this->parser->parse($template);
        /** @var ElementNode $element */
        $element = $document->getChildAt(0);
        self::assertSame("'foo'", $element->getAttribute('id'));
        /** @var ElementNode $element */
        $element = $element->getChildAt(0);
        self::assertSame("'bar'", $element->getAttribute('id'));
        self::assertSame('article', $element->getName());
        /** @var ElementNode $element */
        $element = $element->getChildAt(1);
        self::assertSame("'baz'", $element->getAttribute('id'));
        self::assertSame('blockquote', $element->getName());
        /** @var ElementNode $element */
        $element = $element->getChildAt(0);
        self::assertSame("'foz'", $element->getAttribute('id'));
        self::assertSame('p', $element->getName());
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You can only pass tag interpolation start tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new TagInterpolationStartTokenHandler();
        $handler->handleToken(new AttributeToken(), $state);
    }
}
