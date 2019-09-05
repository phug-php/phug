<?php

namespace Phug\Test;

use PHPUnit\Framework\TestCase;
use Phug\AbstractFormatterModule;
use Phug\Formatter;
use Phug\Formatter\Element\AssignmentElement;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\Element\TextElement;
use Phug\Formatter\Event\DependencyStorageEvent;
use Phug\Formatter\Event\FormatEvent;
use Phug\Formatter\Event\NewFormatEvent;
use Phug\Formatter\Event\StringifyEvent;
use Phug\Formatter\Format\HtmlFormat;
use Phug\Formatter\Format\XmlFormat;
use Phug\FormatterEvent;

//@codingStandardsIgnoreStart
class TestModule extends AbstractFormatterModule
{
    public function getEventListeners()
    {
        return [
            FormatterEvent::FORMAT => function (FormatEvent $event) {
                $element = $event->getElement();
                if ($element instanceof MarkupElement && $element->getName() === 'some-element') {
                    $wrapper = new MarkupElement('wrapper');
                    $wrapper->appendChild($element);

                    $element->setName('renamed-element'); //Notice that we'd create an endless loop if we wouldn't rename it
                    $element->appendChild(new CodeElement('$a + 1'));

                    $event->setElement($wrapper);
                }
            },
        ];
    }
}

/**
 * @coversDefaultClass Phug\AbstractFormatterModule
 */
class FormatterModuleTest extends TestCase
{
    /**
     * @covers ::<public>
     * @covers \Phug\Formatter\Event\FormatEvent::__construct
     * @covers \Phug\Formatter\Event\FormatEvent::getElement
     * @covers \Phug\Formatter\Event\FormatEvent::setElement
     */
    public function testModule()
    {
        $formatter = new Formatter();

        $el = new MarkupElement('some-element');

        self::assertSame('<some-element></some-element>', $formatter->format($el, HtmlFormat::class));

        $formatter = new Formatter(['formatter_modules' => [TestModule::class]]);
        self::assertSame('<wrapper><renamed-element><?php $a + 1 ?></renamed-element></wrapper>', $formatter->format($el, HtmlFormat::class));
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Formatter::__construct
     * @covers \Phug\Formatter::format
     * @covers \Phug\Formatter\Event\FormatEvent::__construct
     * @covers \Phug\Formatter\Event\FormatEvent::getFormat
     * @covers \Phug\Formatter\Event\FormatEvent::setFormat
     */
    public function testFormatEvent()
    {
        $formatter = new Formatter([
            'on_format' => function (FormatEvent $event) {
                if ($event->getFormat() instanceof HtmlFormat) {
                    $event->setFormat(new XmlFormat());
                }
            },
        ]);

        $el = new MarkupElement('input');

        self::assertSame('<input></input>', $formatter->format($el, HtmlFormat::class));

        $formatter = new Formatter();

        $el = new MarkupElement('input');

        self::assertSame('<input>', $formatter->format($el, HtmlFormat::class));

        $formatter = new Formatter([
            'on_format' => function (FormatEvent $event) {
                $event->setElement(null);
            },
        ]);
        $format = new HtmlFormat($formatter);

        self::assertSame(
            '',
            $formatter->format(new MarkupElement('input'), $format)
        );
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Formatter::__construct
     * @covers \Phug\Formatter::format
     * @covers \Phug\Formatter\Event\StringifyEvent::__construct
     * @covers \Phug\Formatter\Event\StringifyEvent::getFormatEvent
     * @covers \Phug\Formatter\Event\StringifyEvent::getOutput
     * @covers \Phug\Formatter\Event\StringifyEvent::setOutput
     */
    public function testStringifyEvent()
    {
        $formatter = new Formatter([
            'on_stringify' => function (StringifyEvent $event) {
                $element = $event->getFormatEvent()->getElement();
                if ($element instanceof MarkupElement && $element->getName() === 'em') {
                    $event->setOutput('*'.$event->getOutput().'*');
                }
            },
        ]);

        $el = new MarkupElement('div', false, null, null, null, [
            new MarkupElement('span', false, null, null, null, [
                new TextElement('foo'),
            ]),
            new MarkupElement('em', false, null, null, null, [
                new TextElement('bar'),
            ]),
            new TextElement('biz'),
        ]);

        self::assertSame(
            '<div><span>foo</span>*<em>bar</em>*biz</div>',
            $formatter->format($el, HtmlFormat::class)
        );
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Formatter::__construct
     * @covers \Phug\Formatter\Event\DependencyStorageEvent::<public>
     */
    public function testDependencyStorageEvent()
    {
        $formatter = new Formatter([
            'on_dependency_storage' => function (DependencyStorageEvent $event) {
                $event->setDependencyStorage(str_replace(
                    'foo',
                    'bar',
                    $event->getDependencyStorage()
                ));
            },
        ]);

        self::assertSame('$pugModule[\'bar\']', $formatter->getDependencyStorage('foo'));
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Formatter\AbstractFormat::__construct
     * @covers \Phug\Formatter::__construct
     * @covers \Phug\Formatter\Event\NewFormatEvent::<public>
     */
    public function testNewFormatEvent()
    {
        $copyFormatter = null;
        $formatter = new Formatter([
            'on_new_format' => function (NewFormatEvent $event) use (&$copyFormatter) {
                $copyFormatter = $event->getFormatter();
                $newFormat = clone $event->getFormat();
                $newFormat
                    ->registerHelper('class_attribute_name', 'className')
                    ->provideHelper('attributes_assignment', [
                        'merge_attributes',
                        'class_attribute_name',
                        'pattern',
                        'pattern.attribute_pattern',
                        'pattern.boolean_attribute_pattern',
                        function ($mergeAttributes, $classAttribute, $pattern, $attributePattern, $booleanPattern) {
                            return function () use ($mergeAttributes, $classAttribute, $pattern, $attributePattern, $booleanPattern) {
                                $attributes = call_user_func_array($mergeAttributes, func_get_args());
                                $code = '';
                                foreach ($attributes as $name => $value) {
                                    if ($value !== null && $value !== false && ($value !== '' || $name !== 'class')) {
                                        if ($name === 'class') {
                                            $name = $classAttribute;
                                        }
                                        $code .= $value === true
                                            ? $pattern($booleanPattern, $name, $name)
                                            : $pattern($attributePattern, $name, $value);
                                    }
                                }

                                return $code;
                            };
                        },
                    ]);
                $event->setFormat($newFormat);
            },
        ]);
        $div = new MarkupElement('div');
        $div->getAttributes()->attach(new AttributeElement('class', 'foo'));
        $div->getAssignments()->attach(new AssignmentElement('attributes', new \SplObjectStorage(), $div, null));
        $php = $formatter->format($div);
        $php = $formatter->formatDependencies().$php;
        ob_start();
        eval('?>'.$php);
        $html = ob_get_contents();
        ob_end_clean();

        self::assertSame('<div className="foo"></div>', $html);
        self::assertSame($formatter, $copyFormatter);
    }
}
//@codingStandardsIgnoreEnd
