<?php

namespace Phug\Test\Element;

use PHPUnit\Framework\TestCase;
use Phug\Formatter\Element\AssignmentElement;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\Element\TextElement;
use SplObjectStorage;

/**
 * @coversDefaultClass \Phug\Formatter\Element\MarkupElement
 */
class MarkupElementTest extends TestCase
{
    /**
     * @covers \Phug\Formatter\AbstractElement::__construct
     * @covers \Phug\Formatter\AbstractElement::dump
     * @covers ::__construct
     * @covers ::getAttribute
     * @covers ::isAutoClosed
     */
    public function testMarkupElement()
    {
        $attributes = new SplObjectStorage();
        $source = new AttributeElement('src', '/foo/bar.png');
        $attributes->attach($source);
        $img = new MarkupElement('img', false, $attributes);
        $altValue = new CodeElement('$alt');
        $alt = new AttributeElement('alt', $altValue);
        $img->getAttributes()->attach($alt);
        $mysteryCode = new CodeElement('$mystery');
        $mystery = new AttributeElement($mysteryCode, '42');
        $img->getAttributes()->attach($mystery);
        $link = new MarkupElement('link', true);

        self::assertFalse($img->isAutoClosed());
        self::assertTrue($link->isAutoClosed());
        self::assertSame('img', $img->getName());
        self::assertTrue($img->getAttributes()->contains($source));
        self::assertTrue($img->getAttributes()->contains($alt));
        self::assertTrue($img->getAttributes()->contains($mystery));
        self::assertSame('/foo/bar.png', $img->getAttribute('src'));
        self::assertSame($altValue, $img->getAttribute('alt'));
        self::assertSame('42', $img->getAttribute($mysteryCode));
        self::assertNull($img->getAttribute('foo'));

        $link->appendChild($img);
        $img->appendChild(new TextElement('foo'));
        self::assertSame("Markup: link\n  Markup: img\n    Text", $link->dump());
    }

    /**
     * @covers ::belongsTo
     */
    public function testBelongsTo()
    {
        $img = new MarkupElement('img');

        self::assertTrue($img->belongsTo(['input', 'img']));
        self::assertFalse($img->belongsTo(['input', 'link']));

        $img = new MarkupElement(new ExpressionElement('"link"'));

        self::assertFalse($img->belongsTo(['input', 'link']));
    }

    /**
     * @covers ::addAssignment
     * @covers ::removedAssignment
     * @covers ::getAssignments
     * @covers ::getAssignmentsByName
     */
    public function testAssignments()
    {
        $img = new MarkupElement('img');
        $foo = new AssignmentElement('foo', null, $img);
        $bar = new AssignmentElement('bar', null, $img);

        self::assertSame(0, $img->getAssignments()->count());

        $img->addAssignment($foo)->addAssignment($bar);

        self::assertSame(2, $img->getAssignments()->count());
        self::assertSame(1, count($img->getAssignmentsByName('foo')));
        self::assertSame($foo, $img->getAssignmentsByName('foo')[0]);
        self::assertSame(1, count($img->getAssignmentsByName('bar')));
        self::assertSame($bar, $img->getAssignmentsByName('bar')[0]);
        self::assertSame($img, $img->removedAssignment($foo));
        self::assertSame($bar, iterator_to_array($img->getAssignments())[0]);
    }
}
