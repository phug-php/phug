<?php

namespace Phug\Test\Element;

use Phug\Formatter;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\DocumentElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\Element\VariableElement;
use Phug\Formatter\Format\HtmlFormat;
use Phug\Util\TestCase;

/**
 * @coversDefaultClass \Phug\Formatter\Element\VariableElement
 */
class VariableElementTest extends TestCase
{
    /**
     * @covers \Phug\Formatter\AbstractFormat::formatVariableElement
     * @covers ::<public>
     */
    public function testVariableElement()
    {
        $variable = new VariableElement(
            new CodeElement('$foo'),
            new ExpressionElement('42')
        );
        $formatter = new Formatter([
            'default_format' => HtmlFormat::class,
        ]);
        $document = new DocumentElement();
        $document->appendChild($variable);

        self::assertSame('<?php $foo=42 ?>', $formatter->format($document));

        $value = new ExpressionElement('$bar');
        $value->escape();
        $value->check();
        $variable = new VariableElement(
            new CodeElement('$foo'),
            $value
        );
        $formatter = new Formatter([
            'default_format' => HtmlFormat::class,
        ]);
        $document = new DocumentElement();
        $document->appendChild($variable);

        self::assertSame(
            '<?php $foo=htmlspecialchars((string) ((isset($bar) ? $bar : null))) ?>',
            $formatter->format($document)
        );
    }
}
