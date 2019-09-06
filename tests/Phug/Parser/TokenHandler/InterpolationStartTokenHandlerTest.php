<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\AttributeToken;
use Phug\Parser;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\InterpolationStartTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\TokenHandler\InterpolationStartTokenHandler
 */
class InterpolationStartTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     * @covers \Phug\Parser\TokenHandler\InterpolationEndTokenHandler::<public>
     */
    public function testHandleToken()
    {
        $template = "p\n  |#{\$var} foo\n  | bar";
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [TextNode]',
            '    [ExpressionNode]',
            '    [TextNode]',
            '    [TextNode]',
        ]);
        $document = $this->parser->parse($template);
        /** @var ExpressionNode $expression */
        $expression = $document->getChildAt(0)->getChildAt(1);
        self::assertSame('$var', $expression->getValue());

        $template = 'p: #{$var} foo';
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ElementNode]',
            '      [TextNode]',
        ]);
        $document = $this->parser->parse($template);
        /** @var ElementNode $element */
        $element = $document->getChildAt(0)->getChildAt(0);
        /** @var ExpressionNode $expression */
        $expression = $element->getName();
        self::assertSame('$var', $expression->getValue());

        $template = "p.\n  foo\n  #{'hi'}";
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [TextNode]',
            '    [ExpressionNode]',
            '    [TextNode]',
        ]);
        $document = $this->parser->parse($template);
        /** @var ExpressionNode $expression */
        $expression = $document->getChildAt(0)->getChildAt(1);
        self::assertSame("'hi'", $expression->getValue());

        $template = "p #{'hi'}";
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [TextNode]',
            '    [ExpressionNode]',
            '    [TextNode]',
        ]);
        $document = $this->parser->parse($template);
        $element = $document->getChildAt(0);
        self::assertSame('', $element->getChildAt(0)->getValue());
        self::assertSame("'hi'", $element->getChildAt(1)->getValue());

        $template = "p  #{'hi'}";
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [TextNode]',
            '    [ExpressionNode]',
            '    [TextNode]',
        ]);
        $document = $this->parser->parse($template);
        $element = $document->getChildAt(0);
        self::assertSame(' ', $element->getChildAt(0)->getValue());
        self::assertSame("'hi'", $element->getChildAt(1)->getValue());
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Parser\TokenHandler\InterpolationEndTokenHandler::<public>
     */
    public function testInterpolationInNestedBlock()
    {
        $template = "html\n".
            "  body\n".
            "    - var friends = 1\n".
            "    case friends\n".
            "      when 0\n".
            "        p you have no friends\n".
            "      when 1\n".
            "        p you have a friend\n".
            "      default\n".
            '        p you have #{friends} friends';
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ElementNode]',
            '      [CodeNode]',
            '        [TextNode]',
            '      [CaseNode]',
            '        [WhenNode]',
            '          [ElementNode]',
            '            [TextNode]',
            '        [WhenNode]',
            '          [ElementNode]',
            '            [TextNode]',
            '        [WhenNode]',
            '          [ElementNode]',
            '            [TextNode]',
            '            [ExpressionNode]',
            '            [TextNode]',
        ]);
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You can only pass interpolation start tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new InterpolationStartTokenHandler();
        $handler->handleToken(new AttributeToken(), $state);
    }

    private function getBadEndingExceptionTokens()
    {
        $tokens = [
            new Lexer\Token\InterpolationStartToken(),
            new Lexer\Token\ExpressionToken(),
            new Lexer\Token\TagToken(),
        ];

        foreach ($tokens as $token) {
            yield $token;
        }
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \Phug\ParserException
     * @expectedExceptionMessage Interpolation not properly closed
     */
    public function testBadEndingException()
    {
        $tokens = $this->getBadEndingExceptionTokens();
        $state = new State(new Parser(), $tokens);
        $handler = new InterpolationStartTokenHandler();
        foreach ($tokens as $token) {
            $handler->handleToken($token, $state);
        }
    }
}
