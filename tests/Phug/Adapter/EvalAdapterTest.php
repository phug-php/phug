<?php

namespace Phug\Test\Adapter;

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
}
