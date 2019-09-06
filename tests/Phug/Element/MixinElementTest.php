<?php

namespace Phug\Test\Element;

use PHPUnit\Framework\TestCase;
use Phug\Formatter;
use Phug\Formatter\Element\AnonymousBlockElement;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\DocumentElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\Element\MixinElement;
use Phug\Formatter\Element\TextElement;

/**
 * @coversDefaultClass \Phug\Formatter\Element\MixinElement
 */
class MixinElementTest extends TestCase
{
    /**
     * @covers \Phug\Formatter::getMixins
     * @covers \Phug\Formatter::requireMixin
     * @covers \Phug\Formatter::formatDependencies
     * @covers \Phug\Formatter\Util\PhpUnwrap::<public>
     * @covers \Phug\Formatter\AbstractFormat::formatMixinAttributeValue
     * @covers \Phug\Formatter\AbstractFormat::getMixinAttributes
     * @covers \Phug\Formatter\AbstractFormat::formatMixinElement
     * @covers ::<public>
     */
    public function testMixinElement()
    {
        $mixin = new MixinElement();
        $mixin->setName('tabs');
        $tabs = new AttributeElement('tabs', null);
        $tabs->setIsVariadic(true);
        $mixin->getAttributes()->attach($tabs);
        $div = new MarkupElement('div');
        $div->appendChild(new AnonymousBlockElement());
        $mixin->appendChild($div);

        $formatter = new Formatter();

        $php = $formatter->format($mixin);
        $formatter->requireMixin('tabs');
        $php = $formatter->formatDependencies().$php;
        $call = '<?php $__pug_mixins["tabs"]('.
            'true, [], [[false, "a"], [false, "b"]], [], '.
            'function () { echo "block"; }'.
            '); ?>';

        ob_start();
        eval('?>'.$php.$call);
        $html = ob_get_contents();
        ob_end_clean();

        self::assertSame('<div>block</div>', $html);
    }

    /**
     * @covers \Phug\Formatter::getMixins
     * @covers \Phug\Formatter::requireMixin
     * @covers \Phug\Formatter::formatDependencies
     * @covers \Phug\Formatter\Util\PhpUnwrap::<public>
     * @covers \Phug\Formatter\AbstractFormat::formatMixinAttributeValue
     * @covers \Phug\Formatter\AbstractFormat::getMixinAttributes
     * @covers \Phug\Formatter\AbstractFormat::formatMixinElement
     * @covers ::<public>
     */
    public function testLazyLoad()
    {
        $mixin = new MixinElement();
        $mixin->setName('tabs');
        $tabs = new AttributeElement('tabs', null);
        $tabs->setIsVariadic(true);
        $mixin->getAttributes()->attach($tabs);
        $div = new MarkupElement('div');
        $div->appendChild(new AnonymousBlockElement());
        $mixin->appendChild($div);

        $formatter = new Formatter();
        $php = $formatter->format($mixin);

        self::assertSame('', $php);
        self::assertSame('', $formatter->formatDependencies());
    }

    /**
     * @covers \Phug\Formatter::getMixins
     * @covers \Phug\Formatter::requireAllMixins
     * @covers \Phug\Formatter::formatDependencies
     * @covers \Phug\Formatter\Util\PhpUnwrap::<public>
     * @covers \Phug\Formatter\AbstractFormat::formatMixinAttributeValue
     * @covers \Phug\Formatter\AbstractFormat::getMixinAttributes
     * @covers \Phug\Formatter\AbstractFormat::formatMixinElement
     * @covers \Phug\Formatter\Element\AnonymousBlockElement::<public>
     * @covers ::<public>
     */
    public function testRequireAllMixins()
    {
        $mixin = new MixinElement();
        $mixin->setName('tabs');
        $tabs = new AttributeElement('tabs', null);
        $tabs->setIsVariadic(true);
        $mixin->getAttributes()->attach($tabs);
        $div = new MarkupElement('div');
        $div->appendChild(new AnonymousBlockElement());
        $mixin->appendChild($div);

        $formatter = new Formatter();

        $php = $formatter->format($mixin);
        $formatter->requireAllMixins();
        $php = $formatter->formatDependencies().$php;
        $call = '<?php $__pug_mixins["tabs"]('.
            'true, [], [[false, "a"], [false, "b"]], [], '.
            'function () { echo "block"; }'.
            '); ?>';

        ob_start();
        eval('?>'.$php.$call);
        $html = ob_get_contents();
        ob_end_clean();

        self::assertSame('<div>block</div>', $html);
    }

    /**
     * @covers \Phug\Formatter::getMixins
     * @covers \Phug\Formatter::requireMixin
     * @covers \Phug\Formatter::formatDependencies
     * @covers \Phug\Formatter\Util\PhpUnwrap::<public>
     * @covers \Phug\Formatter\AbstractFormat::formatMixinAttributeValue
     * @covers \Phug\Formatter\AbstractFormat::getMixinAttributes
     * @covers \Phug\Formatter\AbstractFormat::formatMixinElement
     * @covers ::<public>
     */
    public function testMixinElementReplace()
    {
        $formatter = new Formatter();

        self::assertSame('replace', $formatter->getFormatInstance()->getOption('mixin_merge_mode'));

        $document = new DocumentElement();
        for ($i = 1; $i <= 2; $i++) {
            $mixin = new MixinElement();
            $mixin->setName('foo');
            $p = new MarkupElement('p');
            $p->appendChild(new TextElement('n°'.$i));
            $mixin->appendChild($p);
            $document->appendChild($mixin);
        }
        $document->appendChild(new CodeElement(
            '$__pug_mixins["foo"]('.
                'true, [], [], [], '.
                'function () { echo "block"; }'.
            ')'
        ));
        $php = $formatter->format($document);
        $formatter->requireMixin('foo');
        $php = $formatter->formatDependencies().$php;

        ob_start();
        eval('?>'.$php);
        $html = ob_get_contents();
        ob_end_clean();

        self::assertSame('<p>n°2</p>', $html);
    }

    /**
     * @covers \Phug\Formatter::getMixins
     * @covers \Phug\Formatter::requireMixin
     * @covers \Phug\Formatter::formatDependencies
     * @covers \Phug\Formatter\Util\PhpUnwrap::<public>
     * @covers \Phug\Formatter\AbstractFormat::formatMixinAttributeValue
     * @covers \Phug\Formatter\AbstractFormat::getMixinAttributes
     * @covers \Phug\Formatter\AbstractFormat::formatMixinElement
     * @covers ::<public>
     */
    public function testMixinElementIgnore()
    {
        $document = new DocumentElement();
        for ($i = 1; $i <= 2; $i++) {
            $mixin = new MixinElement();
            $mixin->setName('foo');
            $div = new MarkupElement('p');
            $div->appendChild(new TextElement('n°'.$i));
            $mixin->appendChild($div);
            $document->appendChild($mixin);
        }
        $document->appendChild(new CodeElement(
            '$__pug_mixins["foo"]('.
            'true, [], [], [], '.
            'function () { echo "block"; }'.
            ')'
        ));
        $formatter = new Formatter([
            'mixin_merge_mode' => 'ignore',
        ]);
        $php = $formatter->format($document);
        $formatter->requireMixin('foo');
        $php = $formatter->formatDependencies().$php;

        ob_start();
        eval('?>'.$php);
        $html = ob_get_contents();
        ob_end_clean();

        self::assertSame('<p>n°1</p>', $html);
    }
}
