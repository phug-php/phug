<?php

namespace Phug\Test\Element;

use Phug\Formatter\Element\DocumentElement;
use Phug\Util\TestCase;

/**
 * @coversDefaultClass \Phug\Formatter\Element\DocumentElement
 */
class DocumentElementTest extends TestCase
{
    /**
     * @covers ::getName
     * @covers ::isAutoClosed
     */
    public function testMarkupElement()
    {
        $document = new DocumentElement();

        self::assertFalse($document->isAutoClosed());
        self::assertSame('document', $document->getName());
    }
}
