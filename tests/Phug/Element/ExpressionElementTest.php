<?php

namespace Phug\Test\Element;

use PHPUnit\Framework\TestCase;
use Phug\Formatter;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\Format\XmlFormat;

/**
 * @coversDefaultClass \Phug\Formatter\Element\AbstractValueElement
 */
class ExpressionElementTest extends TestCase
{
    /**
     * @covers ::<public>
     * @covers \Phug\Formatter\AbstractFormat::formatDynamicValue
     */
    public function testExpressionElement()
    {
        $expression = new ExpressionElement('0');
        $formatter = new Formatter([
            'default_format' => XmlFormat::class,
        ]);

        self::assertSame(
            '0',
            $formatter->format($expression)
        );

        $paragraph = new MarkupElement('p');
        $paragraph->getAttributes()->attach(
            $attrEl = new AttributeElement('foo', new ExpressionElement('true'))
        );
        $paragraph->appendChild(new ExpressionElement('true'));
        ob_start();
        $php = $formatter->format($paragraph);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<p foo="foo">true</p>',
            $actual
        );

        $paragraph = new MarkupElement('p');
        $paragraph->getAttributes()->attach(
            new AttributeElement('foo', new ExpressionElement('false'))
        );
        $paragraph->appendChild(new ExpressionElement('false'));
        ob_start();
        $php = $formatter->format($paragraph);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<p>false</p>',
            $actual
        );

        $paragraph = new MarkupElement('p');
        $paragraph->getAttributes()->attach(
            new AttributeElement('foo', new ExpressionElement('undefined'))
        );
        $paragraph->appendChild(new ExpressionElement('null'));
        ob_start();
        $php = $formatter->format($paragraph);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<p></p>',
            $actual
        );
    }

    /**
     * @covers ::<public>
     */
    public function testTrueDynamicValue()
    {
        $formatter = new Formatter([
            'default_format' => XmlFormat::class,
        ]);

        $paragraph = new MarkupElement('p');
        $paragraph->getAttributes()->attach(
            $attrEl = new AttributeElement('foo', new ExpressionElement('$foo'))
        );
        $paragraph->appendChild(new ExpressionElement('$foo'));
        ob_start();
        $foo = true;
        $php = $formatter->format($paragraph);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<p foo="foo">true</p>',
            $actual
        );
    }
}
