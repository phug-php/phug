<?php

namespace Phug\Test\Element;

use PHPUnit\Framework\TestCase;
use Phug\Formatter;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\KeywordElement;
use Phug\Formatter\Element\MarkupElement;
use SplObjectStorage;

/**
 * @coversDefaultClass \Phug\Formatter\Element\KeywordElement
 */
class KeywordElementTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers \Phug\Formatter\AbstractFormat::formatKeywordElement
     * @covers \Phug\Formatter\Partial\MagicAccessorTrait::getMethod
     * @covers \Phug\Formatter\Partial\MagicAccessorTrait::__get
     * @covers \Phug\Formatter\Partial\MagicAccessorTrait::__set
     * @covers \Phug\Formatter\Partial\MagicAccessorTrait::__isset
     */
    public function testStringReturn()
    {
        $keyword = new KeywordElement('foo', 'bar');
        $attributes = new SplObjectStorage();
        $source = new AttributeElement('src', '/foo/bar.png');
        $attributes->attach($source);
        $img = new MarkupElement('img', false, $attributes);
        $keyword->appendChild($img);
        $formatter = new Formatter([
            'keywords' => [
                'foo' => function ($value, KeywordElement $element, $name) {
                    if (isset($element->nodes)) {
                        foreach ($element->nodes as $node) {
                            if ($node->name === 'img') {
                                $value .= ','.$node->getAttribute('src');
                            }
                        }
                    }
                    $element->nodes = [];

                    return $name.':'.$value;
                },
            ],
        ]);

        $code = $formatter->format($keyword);

        self::assertSame('foo:bar,/foo/bar.png', $code);
    }

    /**
     * @covers ::__construct
     * @covers \Phug\Formatter\AbstractFormat::formatKeywordElement
     * @covers \Phug\Formatter\Partial\MagicAccessorTrait::getMethod
     * @covers \Phug\Formatter\Partial\MagicAccessorTrait::__get
     * @covers \Phug\Formatter\Partial\MagicAccessorTrait::__set
     * @covers \Phug\Formatter\Partial\MagicAccessorTrait::__isset
     */
    public function testWrapReturn()
    {
        $keyword = new KeywordElement('foo', 'bar');
        $img = new MarkupElement('img', false, new SplObjectStorage());
        $keyword->appendChild($img);
        $formatter = new Formatter([
            'keywords' => [
                'foo' => function ($value) {
                    return [
                        'begin' => '<div class="'.$value.'">',
                        'end'   => '</div>',
                    ];
                },
            ],
        ]);

        $code = $formatter->format($keyword);

        self::assertSame('<div class="bar"><img /></div>', $code);
    }

    /**
     * @covers ::__construct
     * @covers \Phug\Formatter\AbstractFormat::formatKeywordElement
     * @covers \Phug\Formatter\Partial\MagicAccessorTrait::getMethod
     * @covers \Phug\Formatter\Partial\MagicAccessorTrait::__get
     * @covers \Phug\Formatter\Partial\MagicAccessorTrait::__set
     * @covers \Phug\Formatter\Partial\MagicAccessorTrait::__isset
     */
    public function testPhpReturn()
    {
        $keyword = new KeywordElement('foo', 'bar');
        $img = new MarkupElement('img', false, new SplObjectStorage());
        $keyword->appendChild($img);
        $formatter = new Formatter([
            'keywords' => [
                'foo' => function ($value) {
                    return [
                        'beginPhp' => 'if ($value === "'.$value.'") {',
                        'endPhp'   => '}',
                    ];
                },
            ],
        ]);

        $code = $formatter->format($keyword);

        self::assertSame("<?php\nif (\$value === \"bar\") {\n?><img /><?php\n}\n?>", $code);
    }

    /**
     * @covers                   \Phug\Formatter\AbstractFormat::formatKeywordElement
     * @expectedException        \Phug\FormatterException
     * @expectedExceptionMessage The keyword foo returned an invalid value type
     */
    public function testBadReturn()
    {
        $keyword = new KeywordElement('foo', 'bar');
        $attributes = new SplObjectStorage();
        $source = new AttributeElement('src', '/foo/bar.png');
        $attributes->attach($source);
        $img = new MarkupElement('img', false, $attributes);
        $keyword->appendChild($img);
        $formatter = new Formatter([
            'keywords' => [
                'foo' => function () {
                    return (object) [];
                },
            ],
        ]);

        $formatter->format($keyword);
    }
}
