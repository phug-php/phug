<?php

namespace Phug\Test\Format;

use PHPUnit\Framework\TestCase;
use Phug\Formatter;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\ExpressionElement;

/**
 * @coversDefaultClass \Phug\Formatter\Format\XmlFormat
 */
class FormatExtendTest extends TestCase
{
    /**
     * @covers ::formatAttributeElement
     * @covers \Phug\Formatter\AbstractFormat::formatAttributeValueAccordingToName
     */
    public function testExtendedFormat()
    {
        include_once __DIR__.'/FakeXmlFormat.php';

        $formatter = new Formatter();
        $format = new FakeXmlFormat($formatter);

        ob_start();
        eval('?>'.$format->callFormatAttributeElement(
            new AttributeElement(new ExpressionElement('"foo" . "bar"'), 'abc')
        ));
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            ' foobar="abc"',
            $actual
        );

        ob_start();
        eval('?>'.$format->callFormatAttributeElement(
            new AttributeElement(new ExpressionElement('"foo" . "bar"'), new ExpressionElement('true'))
        ));
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            ' foobar="foobar"',
            $actual
        );

        ob_start();
        $php = $format->callFormatAttributeValueAccordingToName(
            '["a", "b"]',
            new ExpressionElement('"cla" . "ss"')
        );
        eval('?>'.$formatter->formatDependencies().'<?= '.$php.' ?>');
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            'a b',
            $actual
        );
    }
}
