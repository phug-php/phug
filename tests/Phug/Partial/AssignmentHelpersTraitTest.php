<?php

namespace Phug\Test\Partial;

use PHPUnit\Framework\TestCase;
use Phug\Formatter;
use Phug\Formatter\Format\XmlFormat;

/**
 * @coversDefaultClass \Phug\Formatter\Partial\AssignmentHelpersTrait
 */
class AssignmentHelpersTraitTest extends TestCase
{
    /**
     * @covers ::provideAttributeAssignments
     */
    public function testAttributeAssignments()
    {
        $format = new XmlFormat();
        $attributes = ['class' => 'foo', 'foobar' => '11'];
        $helper = $format->getHelper('attribute_assignments');

        self::assertSame(
            'foo bar',
            $helper($attributes, 'class', 'bar')
        );

        self::assertSame(
            '22',
            $helper($attributes, 'foobar', '22')
        );
    }

    /**
     * @covers ::provideAttributeAssignment
     * @covers \Phug\Formatter\AbstractFormat::setFormatter
     */
    public function testAttributeAssignment()
    {
        $format = new XmlFormat();
        $attributes = ['class' => 'foo zoo'];
        $helper = $format->getHelper('attribute_assignment');
        $helper($attributes, 'class', 'bar zoo');

        self::assertSame(
            ['class' => 'foo zoo bar'],
            $attributes
        );
    }

    /**
     * @covers ::provideAttributesAssignment
     * @covers ::provideClassAttributeAssignment
     * @covers ::provideStyleAttributeAssignment
     */
    public function testAttributesAssignment()
    {
        $format = new XmlFormat();
        $helper = $format->getHelper('attributes_assignment');

        $code = $helper([
            'a' => 'b',
        ], [
            'c' => 'a',
        ], [
            'class' => ['foo zoo', 'foo bar'],
        ], [
            'data-user' => ['name' => 'Bob'],
        ], [
            'style' => ['min-width' => 'calc(100% - 50px)'],
        ]);

        self::assertSame(
            ' a="b" c="a" class="foo zoo bar" '.
            'data-user="{"name":"Bob"}" '.
            'style="min-width:calc(100% - 50px)"',
            $code
        );

        $code = $helper([
            'a'     => 'b',
            'c'     => 'a',
            'class' => ['foo zoo', 'foo bar'],
            'style' => ['min-width' => 'calc(100% - 50px)'],
        ], [
            'a'     => 'c',
            'd'     => true,
            'class' => 'foo zoo',
            'style' => 'content: "a"; background: rgb(2, 12, 255)',
        ]);

        self::assertSame(
            ' a="c" c="a" class="foo zoo bar" '.
            'style="min-width:calc(100% - 50px);'.
            'content: "a"; background: rgb(2, 12, 255)" d="d"',
            $code
        );

        $code = $helper([], [
            'class' => ['a' => true, 'b' => false, 'c' => true],
        ]);

        self::assertSame(
            ' class="a c"',
            $code
        );
    }

    /**
     * @covers ::provideArrayEscape
     */
    public function testArrayEscape()
    {
        $formatter = new Formatter();
        $format = new XmlFormat($formatter);
        $escape = $format->getHelper('array_escape');

        self::assertSame('{&quot;&lt;foo&gt;&quot;:&quot;&lt;strong&gt;&quot;}', $escape('data-user', [
            '<foo>' => '<strong>',
        ]));

        self::assertSame('foo', $escape('data-user', 'foo'));
        self::assertSame('&lt;foo&gt;', $escape('data-user', '<foo>'));

        $expected = '{&quot;&lt;foo&gt;&quot;:true}';
        self::assertSame($expected, $escape('data-user', [
            '<foo>' => true,
        ]));

        $expected = [
            '&lt;foo&gt;' => true,
        ];
        self::assertSame($expected, $escape('class', [
            '<foo>' => true,
        ]));
    }

    /**
     * @covers ::provideAttributesAssignment
     * @covers ::provideClassAttributeAssignment
     */
    public function testAttributesMapping()
    {
        $format = new XmlFormat(new Formatter([
            'attributes_mapping' => [
                'class' => 'className',
            ],
        ]));
        $helper = $format->getHelper('attributes_assignment');

        $code = $helper([
            'class' => ['foo zoo', 'foo bar'],
        ]);

        self::assertSame(' className="foo zoo bar"', $code);
    }

    /**
     * @covers \Phug\Formatter\AbstractFormat::formatAttributeValueAccordingToName
     * @covers ::provideStandAloneAttributeAssignment
     * @covers ::provideStandAloneClassAttributeAssignment
     * @covers ::provideStandAloneStyleAttributeAssignment
     */
    public function testStandAloneAttributesAssignment()
    {
        $format = new XmlFormat();
        $helper = $format->getHelper('stand_alone_attribute_assignment');

        self::assertSame(
            'a b',
            $helper('class', ['a', 'b'])
        );

        $helper = $format->getHelper('stand_alone_class_attribute_assignment');

        self::assertSame(
            'a b',
            $helper(['a', 'b'])
        );

        $helper = $format->getHelper('stand_alone_class_attribute_assignment');

        self::assertSame(
            'a c',
            $helper(['a' => true, 'b' => false, 'c' => true])
        );

        $helper = $format->getHelper('stand_alone_style_attribute_assignment');

        self::assertSame(
            'a:b',
            $helper(['a' => 'b'])
        );
    }
}
