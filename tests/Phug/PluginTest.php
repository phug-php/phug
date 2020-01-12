<?php

namespace Phug\Test;

use Phug\Renderer;
use Phug\Test\Utils\Plugin;

/**
 * @coversDefaultClass \Phug\AbstractPlugin
 */
class PluginTest extends AbstractPhugTest
{
    /**
     * @covers ::<public>
     * @covers ::getCallbacks
     * @covers ::iterateTokens
     * @covers ::getMethodsByPrefix
     * @covers ::addCallback
     * @covers ::addSpecificCallback
     *
     * @throws \Phug\PhugException
     * @throws \Phug\RendererException
     * @throws \ReflectionException
     */
    public function testPlugin()
    {
        $renderer = new Renderer();
        Plugin::enable($renderer);

        $this->assertSame('<section>9 + 9</section>', $renderer->render('p=9 + 9'));

        $this->assertSame('<section>Hello!</section>', $renderer->render('div Hello'));
    }
}
