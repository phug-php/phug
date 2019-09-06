<?php

namespace Phug\Test\Format;

use PHPUnit\Framework\TestCase;
use Phug\Formatter;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\DoctypeElement;
use Phug\Formatter\Element\DocumentElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\Element\TextElement;
use Phug\Formatter\ElementInterface;
use Phug\Formatter\Format\HtmlFormat;
use Phug\FormatterException;
use Phug\Lexer\Token\TagToken;
use Phug\Parser\Node\ElementNode;
use Phug\Util\SourceLocation;

/**
 * @coversDefaultClass \Phug\Formatter\Format\HtmlFormat
 */
class HtmlFormatTest extends TestCase
{
    /**
     * @covers ::<public>
     * @covers \Phug\Formatter\Format\XmlFormat::formatMarkupElement
     */
    public function testHtmlFormat()
    {
        $img = new MarkupElement('img');
        $htmlFormat = new HtmlFormat(new Formatter());

        self::assertSame(
            '<img>',
            $htmlFormat($img)
        );

        $img = new MarkupElement('img', true);
        $htmlFormat = new HtmlFormat(new Formatter());

        self::assertSame(
            '<img/>',
            $htmlFormat($img)
        );
    }

    /**
     * @covers \Phug\Formatter\AbstractFormat::escapeHtml
     */
    public function testDependencies()
    {
        $formatter = new HtmlFormat();

        self::assertSame('htmlspecialchars("<")', $formatter->escapeHtml('"<"'));
    }

    /**
     * @covers ::<public>
     */
    public function testCustomFormatHandler()
    {
        $img = new MarkupElement('img');
        $htmlFormat = new HtmlFormat(new Formatter());
        $htmlFormat->setElementHandler(MarkupElement::class, function (ElementInterface $element) {
            return strtoupper($element->getName());
        });

        self::assertSame(
            'IMG',
            $htmlFormat($img)
        );
    }

    /**
     * @covers ::<public>
     */
    public function testMissingFormatHandler()
    {
        $img = new MarkupElement('img');
        $htmlFormat = new HtmlFormat(new Formatter());
        $htmlFormat->removeElementHandler(MarkupElement::class);

        self::assertSame(
            '',
            $htmlFormat($img)
        );
    }

    /**
     * @covers ::<public>
     */
    public function testFormatSingleTagWithAttributes()
    {
        $img = new MarkupElement('img');
        $img->getAttributes()->attach(new AttributeElement('src', 'foo.png'));
        $formatter = new Formatter();
        $htmlFormat = new HtmlFormat($formatter);

        ob_start();
        $php = $htmlFormat($img);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame('<img src="foo.png">', $actual);
    }

    /**
     * @covers ::<public>
     */
    public function testFormatBooleanTrueAttribute()
    {
        $input = new MarkupElement('input');
        $input->getAttributes()->attach(new AttributeElement('type', 'checkbox'));
        $input->getAttributes()->attach(new AttributeElement('checked', new ExpressionElement('true')));
        $formatter = new Formatter();
        $htmlFormat = new HtmlFormat($formatter);

        ob_start();
        $php = $htmlFormat($input);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame('<input type="checkbox" checked>', $actual);
    }

    /**
     * @covers ::<public>
     */
    public function testFormatBooleanNullAttribute()
    {
        $input = new MarkupElement('input');
        $input->getAttributes()->attach(new AttributeElement('type', 'checkbox'));
        $input->getAttributes()->attach(new AttributeElement('checked', new ExpressionElement('null')));
        $formatter = new Formatter();
        $htmlFormat = new HtmlFormat($formatter);

        ob_start();
        $php = $htmlFormat($input);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame('<input type="checkbox">', $actual);
    }

    /**
     * @covers ::<public>
     */
    public function testFormatCodeAttribute()
    {
        $input = new MarkupElement('input');
        $input->getAttributes()->attach(new AttributeElement('type', 'text'));
        $input->getAttributes()->attach(new AttributeElement('value', new ExpressionElement('array_sum([24, 18])')));
        $formatter = new Formatter();
        $htmlFormat = new HtmlFormat($formatter);

        ob_start();
        $php = $htmlFormat($input);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame('<input type="text" value="42">', $actual);
    }

    /**
     * @covers \Phug\Formatter\AbstractFormat::formatCode
     */
    public function testFormatVariable()
    {
        $input = new MarkupElement('input');
        $input->getAttributes()->attach(new AttributeElement('type', 'text'));
        $input->getAttributes()->attach(new AttributeElement('value', new ExpressionElement('$foo')));
        $formatter = new Formatter();
        $htmlFormat = new HtmlFormat($formatter);

        ob_start();
        $foo = 'bar';
        $php = $htmlFormat($input);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame('<input type="text" value="bar">', $actual);

        ob_start();
        $foo = '';
        $php = $htmlFormat($input);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame('<input type="text" value="">', $actual);

        ob_start();
        $foo = null;
        $php = $htmlFormat($input);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame('<input type="text">', $actual);
    }

    /**
     * @covers                   \Phug\Formatter\AbstractFormat::throwException
     * @covers                   \Phug\Formatter\Format\XmlFormat::isSelfClosingTag
     * @expectedException        \Phug\FormatterException
     * @expectedExceptionMessage input is a self closing element: <input/> but contains nested content.
     */
    public function testChildrenInSelfClosingTag()
    {
        $input = new MarkupElement('input');
        $input->appendChild(new MarkupElement('i'));
        $htmlFormat = new HtmlFormat(new Formatter());

        self::assertSame('expected', $htmlFormat($input));
    }

    /**
     * @covers \Phug\Formatter\Format\XmlFormat::isSelfClosingTag
     */
    public function testEmptyTextInSelfClosingTag()
    {
        $input = new MarkupElement('input');
        $input->appendChild(new TextElement(''));
        $htmlFormat = new HtmlFormat(new Formatter());
        $htmlFormat($input);

        self::assertSame('<input>', $htmlFormat($input));
    }

    /**
     * @covers                   \Phug\Formatter\AbstractFormat::throwException
     * @covers                   \Phug\Formatter\Format\XmlFormat::isSelfClosingTag
     * @expectedException        \Phug\FormatterException
     * @expectedExceptionMessage input is a self closing element: <input/> but contains nested content.
     */
    public function testTextInSelfClosingTag()
    {
        $input = new MarkupElement('input');
        $input->appendChild(new TextElement('foo'));
        $htmlFormat = new HtmlFormat(new Formatter());
        $htmlFormat($input);
    }

    /**
     * @covers \Phug\Formatter\AbstractFormat::throwException
     * @covers \Phug\Formatter\Format\XmlFormat::isSelfClosingTag
     */
    public function testChildrenInSelfClosingTagLocation()
    {
        $input = new MarkupElement(
            'input',
            false,
            null,
            new ElementNode(new TagToken(), new SourceLocation('foo', 1, 2))
        );
        $input->appendChild(new MarkupElement('i'));
        $htmlFormat = new HtmlFormat(new Formatter());
        $location = null;

        try {
            $htmlFormat($input);
        } catch (FormatterException $exception) {
            $location = $exception->getLocation();
        }

        self::assertSame('foo', $location->getPath());
        self::assertSame(1, $location->getLine());
        self::assertSame(2, $location->getOffset());
    }

    /**
     * @covers \Phug\Formatter\AbstractFormat::formatDoctypeElement
     * @covers \Phug\Formatter\AbstractFormat::formatDocumentElement
     */
    public function testCustomDoctype()
    {
        $document = new DocumentElement();
        $document->appendChild(new DoctypeElement('html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN"'));
        $document->appendChild(new MarkupElement('html'));
        $htmlFormat = new HtmlFormat(new Formatter());

        self::assertSame(
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN"><html></html>',
            $htmlFormat($document)
        );
    }

    /**
     * @covers ::isBlockTag
     * @covers ::formatPairTagChildren
     */
    public function testIsBlockTag()
    {
        $document = new DocumentElement();
        $document->appendChild(new DoctypeElement('html'));
        $p = new MarkupElement('p');
        $document->appendChild($p);
        $span = new MarkupElement('span');
        $p->appendChild($span);
        $div = new MarkupElement('div');
        $span->appendChild($div);
        $formatter = new Formatter([
            'pretty' => '  ',
        ]);

        self::assertSame(
            '<!DOCTYPE html>'.PHP_EOL.
            '<p><span><div></div></span></p>',
            trim($formatter->format($document))
        );

        $document = new DocumentElement();
        $document->appendChild(new DoctypeElement('html'));
        $p = new MarkupElement('p');
        $document->appendChild($p);
        $span = new MarkupElement('span');
        $p->appendChild($span);
        $code2 = new CodeElement('if ($condition)');
        $span->appendChild($code2);
        $code3 = new CodeElement('foreach ($items as $item)');
        $code2->appendChild($code3);
        $div = new MarkupElement('div');
        $code3->appendChild($div);
        $formatter = new Formatter([
            'pretty' => '  ',
        ]);

        self::assertSame(
            '<!DOCTYPE html>'.PHP_EOL.
            '<p><span><?php if ($condition) { ?>'.
            '<?php foreach ($items as $item) { ?><div></div><?php } ?>'.
            '<?php } ?></span></p>',
            trim($formatter->format($document))
        );
    }

    /**
     * @covers \Phug\Formatter\Format\XmlFormat::formatAttributes
     */
    public function testClassMerge()
    {
        $formatter = new Formatter([
            'pretty' => '  ',
        ]);

        $link = new MarkupElement('a');
        $link->getAttributes()->attach(new AttributeElement('class', 'tag-class'));
        $link->getAttributes()->attach(new AttributeElement(
            'class',
            new ExpressionElement("['class1', 'class2']")
        ));
        $document = new DocumentElement();
        $document->appendChild($link);

        ob_start();
        $php = $formatter->format($document);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<a class="tag-class class1 class2"></a>',
            trim($actual)
        );
    }

    /**
     * @covers \Phug\Formatter\Format\XmlFormat::formatAttributes
     */
    public function testClassAssociativeObject()
    {
        $formatter = new Formatter([
            'pretty' => '  ',
        ]);

        $link = new MarkupElement('a');
        $link->getAttributes()->attach(new AttributeElement(
            'class',
            new ExpressionElement("array('foo' => true, 'bar' => false, 'baz' => true)")
        ));
        $document = new DocumentElement();
        $document->appendChild($link);

        ob_start();
        $php = $formatter->format($document);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<a class="foo baz"></a>',
            trim($actual)
        );
    }

    /**
     * @covers ::isBlockTag
     * @covers ::isWhiteSpaceSensitive
     * @covers ::formatPairTagChildren
     */
    public function testIsWhiteSpaceSensitive()
    {
        $document = new DocumentElement();
        $document->appendChild(new DoctypeElement('html'));
        $div1 = new MarkupElement('div');
        $div4 = new MarkupElement('div');
        $div4->appendChild(new TextElement('foo'));
        $div1->appendChild($div4);
        $document->appendChild($div1);
        $div2 = new MarkupElement('div');
        $div3 = new MarkupElement('div');
        $div3->appendChild(new MarkupElement('span'));
        $div3->appendChild(new MarkupElement('i'));
        $div2->appendChild($div3);
        $document->appendChild($div2);
        $textarea = new MarkupElement('textarea');
        $textarea->appendChild(new MarkupElement('div'));
        $document->appendChild($textarea);
        $section = new MarkupElement('section');
        $section->appendChild($divEl = new MarkupElement('div'));
        $divEl->appendChild(new MarkupElement('div'));
        $document->appendChild($section);
        $formatter = new Formatter([
            'pretty' => '  ',
        ]);

        self::assertSame(
            '<!DOCTYPE html>'.PHP_EOL.
            '<div>'.PHP_EOL.
            '  <div>foo</div>'.PHP_EOL.
            '</div>'.PHP_EOL.
            '<div>'.PHP_EOL.
            '  <div><span></span><i></i></div>'.PHP_EOL.
            '</div>'.PHP_EOL.
            '<textarea><div></div></textarea>'.PHP_EOL.
            '<section>'.PHP_EOL.
            '  <div>'.PHP_EOL.
            '    <div></div>'.PHP_EOL.
            '  </div>'.PHP_EOL.
            '</section>',
            trim($formatter->format($document))
        );
    }

    /**
     * @covers ::isBlockTag
     * @covers ::isWhiteSpaceSensitive
     * @covers ::formatPairTagChildren
     */
    public function testIndentInCode()
    {
        $document = new DocumentElement();
        $div1 = new MarkupElement('div');
        $code = new CodeElement('if (true)');
        $div2 = new MarkupElement('div');
        $code->appendChild($div2);
        $div1->appendChild($code);
        $document->appendChild($div1);
        $formatter = new Formatter([
            'pretty' => true,
        ]);

        ob_start();
        eval('?>'.$formatter->format($document));
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<div>'.PHP_EOL.
            '  <div></div>'.PHP_EOL.
            '</div>',
            trim($actual)
        );
    }
}
