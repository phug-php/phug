<?php

namespace Phug\Test\Format;

use PHPUnit\Framework\TestCase;
use Phug\Formatter;
use Phug\Formatter\Element\AssignmentElement;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\DoctypeElement;
use Phug\Formatter\Element\DocumentElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\Element\TextElement;
use Phug\Formatter\Element\VariableElement;
use Phug\Formatter\ElementInterface;
use Phug\Formatter\Format\BasicFormat;
use Phug\Formatter\Format\XmlFormat;
use Phug\Parser\Node\TextNode;
use SplObjectStorage;

/**
 * @coversDefaultClass \Phug\Formatter\Format\XmlFormat
 */
class XmlFormatTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers \Phug\Formatter\AbstractFormat::__construct
     * @covers ::__invoke
     * @covers \Phug\Formatter\AbstractFormat::formatDoctypeElement
     */
    public function testXmlFormat()
    {
        $document = new DocumentElement();
        $document->appendChild(new DoctypeElement());
        $document->appendChild(new MarkupElement('img', true));
        $xmlFormat = new XmlFormat(new Formatter([
            'default_format' => XmlFormat::class,
        ]));

        $php = $xmlFormat($document);
        ob_start();
        eval('?>'.$php);
        $xml = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<?xml version="1.0" encoding="utf-8" ?><img />',
            $xml
        );

        $document = new DocumentElement();
        $document->appendChild(new DoctypeElement());
        $document->appendChild(new MarkupElement('img'));
        $xmlFormat = new XmlFormat(new Formatter([
            'default_format' => XmlFormat::class,
        ]));

        $php = $xmlFormat($document);
        ob_start();
        eval('?>'.$php);
        $xml = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<?xml version="1.0" encoding="utf-8" ?><img></img>',
            $xml
        );
    }

    /**
     * @covers ::isSelfClosingTag
     * @covers ::isBlockTag
     * @covers ::isWhiteSpaceSensitive
     * @covers ::formatMarkupElement
     * @covers ::formatElementChildren
     * @covers ::formatPairTag
     */
    public function testCustomFormatHandler()
    {
        $img = new MarkupElement('img');
        $xmlFormat = new XmlFormat(new Formatter([
            'default_format' => XmlFormat::class,
        ]));
        $xmlFormat->setElementHandler(MarkupElement::class, function (ElementInterface $element) {
            return strtoupper($element->getName());
        });

        self::assertSame(
            'IMG',
            $xmlFormat($img)
        );
    }

    /**
     * @covers ::formatElementChildren
     * @covers \Phug\Formatter\Element\CodeElement::getValueTokens
     * @covers \Phug\Formatter\Element\CodeElement::<public>
     */
    public function testConditionals()
    {
        $call = new CodeElement('call_something()');
        $if = new CodeElement('if (true)');
        $if->appendChild(new MarkupElement('img', true));
        $else = new CodeElement('else');
        $else->appendChild(new MarkupElement('img', true));
        $document = new DocumentElement();
        $document->appendChild($call);
        $document->appendChild($if);
        $document->appendChild(new TextNode()); // Should be ignored
        $document->appendChild($else);
        $xmlFormat = new XmlFormat(new Formatter([
            'default_format' => XmlFormat::class,
        ]));

        self::assertSame(
            '<?php call_something() ?><?php if (true) { ?><img /><?php } else { ?><img /><?php } ?>',
            $xmlFormat($document)
        );

        $do = new CodeElement('do');
        $do->appendChild(new MarkupElement('img', true));
        $while = new CodeElement('while ($i < 2);');
        $document = new DocumentElement();
        $document->appendChild($do);
        $document->appendChild($while);
        $xmlFormat = new XmlFormat(new Formatter([
            'default_format' => XmlFormat::class,
        ]));

        self::assertSame(
            '<?php do { ?><img /><?php } while ($i < 2); ?>',
            $xmlFormat($document)
        );

        $if = new CodeElement('if (true)');
        $if->appendChild(new VariableElement(
            new CodeElement('$a'),
            new ExpressionElement('2')
        ));
        $else = new CodeElement('else');
        $else->appendChild(new VariableElement(
            new CodeElement('$a'),
            new ExpressionElement('4')
        ));
        $document = new DocumentElement();
        $document->appendChild($if);
        $document->appendChild($else);
        $xmlFormat = new XmlFormat(new Formatter([
            'default_format' => XmlFormat::class,
        ]));

        self::assertSame(
            2,
            eval('?>'.$xmlFormat($document).'<?php return $a;')
        );

        $formatter = new Formatter([
            'default_format' => XmlFormat::class,
        ]);
        $if = new CodeElement('if ($doesNotExist)');
        $if->check()->appendChild(new MarkupElement('if', true));
        $else = new CodeElement('else');
        $else->check()->appendChild(new MarkupElement('else', true));
        $document = new DocumentElement();
        $document->appendChild($if);
        $document->appendChild($else);
        $xmlFormat = new XmlFormat($formatter);

        ob_start();
        $php = $xmlFormat($document);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<else />',
            $actual
        );
    }

    /**
     * @covers ::<public>
     */
    public function testMissingFormatHandler()
    {
        $img = new MarkupElement('img', true);
        $xmlFormat = new XmlFormat(new Formatter([
            'default_format' => XmlFormat::class,
        ]));
        $xmlFormat->removeElementHandler(MarkupElement::class);

        self::assertSame(
            '',
            $xmlFormat($img)
        );
    }

    /**
     * @covers ::hasDuplicateAttributeNames
     * @covers ::hasNonStaticAttributes
     * @covers ::formatAttributeElement
     * @covers ::formatMarkupElement
     * @covers ::formatAttributeElement
     * @covers \Phug\Formatter\AbstractFormat::formatAttributeValueAccordingToName
     * @covers ::formatElementChildren
     * @covers ::formatPairTag
     */
    public function testFormatSingleTagWithAttributes()
    {
        $img = new MarkupElement('img', true);
        $img->getAttributes()->attach(new AttributeElement('src', 'foo.png'));
        $formatter = new Formatter([
            'default_format' => XmlFormat::class,
        ]);
        $xmlFormat = new XmlFormat($formatter);

        ob_start();
        $php = $xmlFormat($img);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<img src="foo.png" />',
            $actual
        );

        $img = new MarkupElement('img', true);
        $img->getAttributes()->attach(new AttributeElement('src', ''));
        $img->getAttributes()->attach(new AttributeElement('class', ''));
        $formatter = new Formatter([
            'default_format' => XmlFormat::class,
        ]);
        $xmlFormat = new XmlFormat($formatter);

        ob_start();
        $php = $xmlFormat($img);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<img src="" />',
            $actual
        );

        $img = new MarkupElement('img', true);
        $img->getAttributes()->attach(new AttributeElement('src', ''));
        $img->getAttributes()->attach(new AttributeElement('class', new TextElement('')));
        $formatter = new Formatter([
            'default_format' => XmlFormat::class,
        ]);
        $xmlFormat = new XmlFormat($formatter);

        ob_start();
        $php = $xmlFormat($img);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<img src="" />',
            $actual
        );

        $img = new MarkupElement('img', true);
        $img->getAttributes()->attach(new AttributeElement('src', ''));
        $img->getAttributes()->attach(new AttributeElement('class', new ExpressionElement('""')));
        $formatter = new Formatter([
            'default_format' => XmlFormat::class,
        ]);
        $xmlFormat = new XmlFormat($formatter);

        ob_start();
        $php = $xmlFormat($img);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<img src="" />',
            $actual
        );
    }

    /**
     * @covers ::formatMarkupElement
     * @covers ::formatAttributeElement
     * @covers ::formatExpressionElement
     * @covers ::formatAttributeValueAccordingToName
     * @covers ::formatElementChildren
     * @covers ::formatPairTag
     * @covers \Phug\Formatter\Element\ExpressionElement::<public>
     * @covers \Phug\Formatter\Partial\HandleVariable::isInKeywordParams
     * @covers \Phug\Formatter\Partial\TransformableTrait::preventFromTransformation
     * @covers \Phug\Formatter\Partial\TransformableTrait::isTransformationAllowed
     */
    public function testFormatBooleanTrueAttribute()
    {
        $input = new MarkupElement('input', true);
        $input->getAttributes()->attach(new AttributeElement('type', 'checkbox'));
        $input->getAttributes()->attach(new AttributeElement('checked', new ExpressionElement('true')));
        $formatter = new Formatter([
            'default_format' => XmlFormat::class,
        ]);
        $xmlFormat = new XmlFormat($formatter);
        $document = new DocumentElement();
        $document->appendChild($input);

        ob_start();
        $php = $xmlFormat($document);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<input type="checkbox" checked="checked" />',
            $actual
        );

        $input = new MarkupElement('input', true);
        $input->getAttributes()->attach(new AttributeElement('type', 'checkbox'));
        $input->getAttributes()->attach(new AttributeElement(new ExpressionElement('$foo'), 'checked'));
        $document = new DocumentElement();
        $document->appendChild($input);

        ob_start();
        $foo = null;
        $php = $xmlFormat($document);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<input type="checkbox" />',
            $actual
        );

        $formatter = new Formatter([
            'default_format' => XmlFormat::class,
        ]);
        $xmlFormat = new XmlFormat($formatter);

        $input = new MarkupElement('input', true);
        $input->getAttributes()->attach(new AttributeElement('type', 'checkbox'));
        $input->getAttributes()->attach(new AttributeElement(new ExpressionElement('$foo'), 'checked'));
        $document = new DocumentElement();
        $document->appendChild($input);

        ob_start();
        $foo = 'checked';
        $php = $xmlFormat($document);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<input type="checkbox" checked="checked" />',
            $actual
        );

        $input = new MarkupElement('input', true);
        $input->getAttributes()->attach(
            new AttributeElement(
                new ExpressionElement('"(name)"'),
                new ExpressionElement('"user"')
            )
        );
        $document = new DocumentElement();
        $document->appendChild($input);

        self::assertSame(
            '<input (name)="user" />',
            $xmlFormat($document)
        );

        $input = new MarkupElement('input', true);
        $input->getAttributes()->attach(new AttributeElement('type', 'checkbox'));
        $input->getAttributes()->attach(new AttributeElement(
            new ExpressionElement('$foo'),
            new ExpressionElement('$bar')
        ));
        $document = new DocumentElement();
        $document->appendChild($input);

        ob_start();
        $bar = 'bar';
        $foo = 'class';
        $php = $xmlFormat($document);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<input type="checkbox" class="bar" />',
            $actual
        );

        $input = new MarkupElement('input', true);
        $getter = 'call_user_func('.
            'function ($foo) { foreach ($foo as $k => $v) { $foo[$k] = $v."a"; } return $foo; }, '.
            '[false, "b"])';
        $input->getAttributes()->attach(new AttributeElement('class', new ExpressionElement($getter)));
        $document = new DocumentElement();
        $document->appendChild($input);

        ob_start();
        $php = $xmlFormat($document);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<input class="a ba" />',
            $actual
        );

        $input = new MarkupElement('input', true);
        $input->getAttributes()->attach(new AttributeElement('type', 'checkbox'));
        $input->getAttributes()->attach(new AttributeElement(
            new ExpressionElement('$foo'),
            new ExpressionElement('true')
        ));
        $document = new DocumentElement();
        $document->appendChild($input);

        ob_start();
        $foo = null;
        $php = $xmlFormat($document);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<input type="checkbox" />',
            $actual
        );

        $input = new MarkupElement('input', true);
        $input->getAttributes()->attach(new AttributeElement('type', 'checkbox'));
        $input->getAttributes()->attach(new AttributeElement(
            new ExpressionElement('$foo'),
            new ExpressionElement('true')
        ));
        $document = new DocumentElement();
        $document->appendChild($input);

        ob_start();
        $foo = 'checked';
        $php = $xmlFormat($document);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<input type="checkbox" checked="checked" />',
            $actual
        );
    }

    /**
     * @covers ::formatMarkupElement
     * @covers ::formatAttributeElement
     * @covers \Phug\Formatter\AbstractFormat::formatAttributeValueAccordingToName
     * @covers ::formatElementChildren
     * @covers ::formatPairTag
     */
    public function testFormatBooleanFalseAttribute()
    {
        $input = new MarkupElement('input', true);
        $input->getAttributes()->attach(new AttributeElement('type', 'checkbox'));
        $input->getAttributes()->attach(new AttributeElement('checked', new ExpressionElement('false')));
        $formatter = new Formatter([
            'default_format' => XmlFormat::class,
        ]);
        $xmlFormat = new XmlFormat($formatter);

        ob_start();
        $php = $xmlFormat($input);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<input type="checkbox" />',
            $actual
        );
    }

    /**
     * @covers ::isSelfClosingTag
     * @covers ::isBlockTag
     * @covers ::isWhiteSpaceSensitive
     * @covers ::formatElementChildren
     * @covers ::formatPairTag
     * @covers ::formatMarkupElement
     */
    public function testChildrenInATag()
    {
        $input = new MarkupElement('input', false);
        $input->appendChild(new MarkupElement('i', true));
        $formatter = new Formatter([
            'default_format' => XmlFormat::class,
        ]);
        $xmlFormat = new XmlFormat($formatter);

        ob_start();
        $php = $xmlFormat($input);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<input><i /></input>',
            $actual
        );
    }

    /**
     * @covers \Phug\Formatter\AbstractFormat::helperName
     * @covers \Phug\Formatter\AbstractFormat::requireHelper
     */
    public function testHelperName()
    {
        $formatter = new Formatter([
            'default_format' => XmlFormat::class,
        ]);
        $xmlFormat = new XmlFormat($formatter);
        $xmlFormat->provideHelper('foo', function () {
            return function () {
                return 1;
            };
        });

        $states = $formatter->getDependencies()->getRequirementsStates();

        self::assertTrue(isset($states[XmlFormat::class.'::foo']));
        self::assertFalse($states[XmlFormat::class.'::foo']);

        $xmlFormat->requireHelper('foo');
        $states = $formatter->getDependencies()->getRequirementsStates();

        self::assertTrue($states[XmlFormat::class.'::foo']);
    }

    /**
     * @covers \Phug\Formatter\Partial\PatternTrait::patternName
     * @covers \Phug\Formatter\Partial\PatternTrait::addPattern
     * @covers \Phug\Formatter\Partial\PatternTrait::setPattern
     * @covers \Phug\Formatter\Partial\PatternTrait::exportHelper
     */
    public function testAddPattern()
    {
        $formatter = new Formatter([
            'default_format' => BasicFormat::class,
        ]);
        $xmlFormat = new BasicFormat($formatter);
        $xmlFormat->addPattern('foo', [function () {
            return function () {
                return 1;
            };
        }]);

        $states = $formatter->getDependencies()->getRequirementsStates();

        self::assertTrue(isset($states[BasicFormat::class.'::pattern.foo']));
        self::assertFalse($states[BasicFormat::class.'::pattern.foo']);
        self::assertSame(
            '$pugModule[\''.addslashes(BasicFormat::class).'::pattern.foo\']',
            $xmlFormat->exportHelper('pattern.foo')
        );

        $states = $formatter->getDependencies()->getRequirementsStates();

        self::assertTrue($states[BasicFormat::class.'::pattern.foo']);
    }

    /**
     * @covers \Phug\Formatter\AbstractFormat::setFormatter
     */
    public function testGetDynamicHelper()
    {
        $formatter = new Formatter([
            'default_format' => BasicFormat::class,
        ]);
        $basicFormat = new BasicFormat($formatter);
        $basicFormat->provideHelper('foobar', [function () {
            return function () {
                return 123;
            };
        }]);
        $basicFormat->provideHelper('test', ['get_helper', function ($getHelper) {
            return function ($name) use ($getHelper) {
                return call_user_func($getHelper($name)) + 5;
            };
        }]);
        $function = $basicFormat->exportHelper('foobar');
        $actual = eval('?>'.$formatter->formatDependencies().'<?php return '.$function.'();');

        self::assertSame(123, $actual);

        $function = $basicFormat->exportHelper('test');
        $actual = eval('?>'.$formatter->formatDependencies().'<?php return '.$function.'("foobar");');

        self::assertSame(128, $actual);
    }

    /**
     * @covers \Phug\Formatter\AbstractFormat::__construct
     */
    public function testNativePhpHelperCall()
    {
        $formatter = new Formatter([
            'default_format' => BasicFormat::class,
        ]);
        $basicFormat = new BasicFormat($formatter);
        $function = $basicFormat->exportHelper('pattern.html_text_escape');
        $actual = eval('?>'.$formatter->formatDependencies().'<?php return '.$function.'("<>");');

        self::assertSame('&lt;&gt;', $actual);

        $text = new TextElement('<>');
        $text->escape();

        self::assertSame('&lt;&gt;', $formatter->format($text));
    }

    /**
     * @covers ::__construct
     * @covers ::formatAssignmentValue
     * @covers ::formatAssignmentElement
     * @covers ::yieldAssignmentElement
     * @covers ::formatAttributes
     */
    public function testAttributeAssignmentsOption()
    {
        $formatter = new Formatter([
            'default_format'        => XmlFormat::class,
            'attribute_assignments' => [
                'data-user' => function (&$attributes, $value) {
                    $data = isset($attributes['data-user']) ? json_decode($attributes['data-user']) : [];
                    $value = is_string($value) ? json_decode($value) : $value;

                    return json_encode(array_merge_recursive((array) $data, (array) $value));
                },
            ],
        ]);
        $link = new MarkupElement('a');
        $attributes = new AttributeElement('data-user', new ExpressionElement('"{\"name\":{\"first\":\"Linus\"}}"'));
        $link->getAttributes()->attach($attributes);
        $data = new SplObjectStorage();
        $data->attach(new ExpressionElement('["data-user" => ["name" => ["last" => "Trosvald"]]]'));
        $link->addAssignment(new AssignmentElement('attributes', $data, $link));

        ob_start();
        $php = $formatter->format($link);
        eval('?>'.$formatter->formatDependencies().$php);
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            '<a data-user="{"name":{"last":"Trosvald","first":"Linus"}}"></a>',
            $actual
        );
    }

    /**
     * @covers \Phug\Formatter\AbstractFormat::setFormatter
     */
    public function testSetFormatter()
    {
        include_once __DIR__.'/FakeFormat.php';

        $format = new FakeFormat();

        self::assertSame(FakeFormat::class.'::existing_helper', $format(new MarkupElement('existing_helper')));
    }

    /**
     * @covers \Phug\Formatter\AbstractFormat::setFormatter
     */
    public function testMissingHelper()
    {
        method_exists($this, 'setExpectedException')
            ? self::setExpectedException(
                \Exception::class,
                '\'non_existing_helper\' dependency not found in the namespace: '.
                '\'Phug\\\\Test\\\\Format\\\\FakeFormat::\''
            )
            : self::expectExceptionMessage(
                '\'non_existing_helper\' dependency not found in the namespace: '.
                '\'Phug\\\\Test\\\\Format\\\\FakeFormat::\''
            );

        include_once __DIR__.'/FakeFormat.php';

        $format = new FakeFormat();

        $format(new MarkupElement('non_existing_helper'));
    }

    /**
     * @covers \Phug\Formatter\Partial\HandleVariable::handleVariable
     */
    public function testCheckedVariableExceptionsOption()
    {
        $formatter = new Formatter([
            'default_format'              => XmlFormat::class,
            'checked_variable_exceptions' => [
                'js-phpize' => function ($variable, $index, $tokens) {
                    return $index > 2 &&
                        $tokens[$index - 1] === '(' &&
                        $tokens[$index - 2] === ']' &&
                        is_array($tokens[$index - 3]) &&
                        $tokens[$index - 3][0] === T_CONSTANT_ENCAPSED_STRING &&
                        preg_match('/_with_ref\'$/', $tokens[$index - 3][1]);
                },
            ],
        ]);

        self::assertSame(
            '$a[\'foo_with_ref\']($b) + $a[\'foo_without_ref\']((isset($b) ? $b : null))',
            $formatter->formatCode('$a[\'foo_with_ref\']($b) + $a[\'foo_without_ref\']($b)', true)
        );
    }
}
