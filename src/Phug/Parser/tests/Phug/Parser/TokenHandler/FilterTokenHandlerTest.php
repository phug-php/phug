<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Formatter\Element\AttributeElement;
use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\Node\FilterNode;
use Phug\Parser\Node\ImportNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\FilterTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\TokenHandler\FilterTokenHandler
 */
class FilterTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     * @covers \Phug\Parser\TokenHandler\ImportTokenHandler::<public>
     */
    public function testHandleToken()
    {
        $this->assertNodes(':foo', [
            '[DocumentNode]',
            '  [FilterNode]',
            '    [TextNode]',
        ]);
        $template = ':foo(baz="bar") Bla';
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [FilterNode]',
            '    [TextNode]',
        ]);
        $document = $this->parser->parse($template);
        /** @var FilterNode $filter */
        $filter = $document->getChildAt(0);
        /** @var AttributeElement $attribute */
        $attribute = null;
        foreach ($filter->getAttributes() as $item) {
            $attribute = $item;
        }
        self::assertSame('"bar"', $attribute->getValue());
        self::assertSame('baz', $attribute->getName());

        $template = 'include:coffee(foo=1 bar biz=9) file.coffee';
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [ImportNode]',
            '  [FilterNode]',
        ]);
        $document = $this->parser->parse($template);
        /** @var ImportNode $import */
        $import = $document->getChildAt(0);
        /** @var FilterNode $filter1 */
        $filter1 = $import->getFilter();
        /** @var FilterNode $filter2 */
        $filter2 = $document->getChildAt(1);
        $attribute = null;
        foreach ($filter1->getAttributes() as $item) {
            if ($item->getName() === 'biz') {
                $attribute = $item->getValue();
            }
        }
        self::assertSame('file.coffee', $import->getPath());
        self::assertSame('9', $attribute);
        self::assertSame($filter1, $filter2);

        $template = "html\n".
            "  head\n".
            "    style(type=\"text/css\")\n".
            "      :stylus\n".
            "        body\n".
            "          padding: 50px\n".
            "  body\n";

        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ElementNode]',
            '      [ElementNode]',
            '        [FilterNode]',
            '          [TextNode]',
            '    [ElementNode]',
        ]);
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You can only pass filter tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new FilterTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
