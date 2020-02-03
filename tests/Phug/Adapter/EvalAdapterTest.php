<?php

namespace Phug\Test\Adapter;

use DateTime;
use Phug\Renderer;
use Phug\Renderer\Adapter\EvalAdapter;
use Phug\Test\AbstractRendererTest;

/**
 * @coversDefaultClass \Phug\Renderer\Adapter\EvalAdapter
 */
class EvalAdapterTest extends AbstractRendererTest
{
    /**
     * @covers ::display
     * @covers \Phug\Renderer\Partial\AdapterTrait::getNewSandBox
     */
    public function testRender()
    {
        $renderer = new Renderer([
            'adapter_class_name' => EvalAdapter::class,
        ]);

        self::assertSame('<p>Hello</p>', $renderer->render('p Hello'));
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Renderer\AbstractAdapter::execute
     */
    public function testThisOverride()
    {
        $renderer = new Renderer([
            'adapter_class_name' => EvalAdapter::class,
        ]);

        self::assertSame('<p>2020-02</p>', $renderer->render('p=$this->format("Y-m")', [
            'this' => new DateTime('2020-02-05'),
        ]));
    }
}
