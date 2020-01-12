<?php

namespace Phug\Test;

use Phug\Phug;
use Phug\Renderer;
use Phug\Test\Utils\LexerPlugin;
use Phug\Test\Utils\ParserPlugin;
use Phug\Test\Utils\Plugin;
use Phug\Test\Utils\RendererPlugin;

/**
 * @coversDefaultClass \Phug\AbstractPlugin
 */
class PluginTest extends AbstractPhugTest
{
    public function setUp()
    {
        parent::setUp();

        Phug::reset();
    }

    protected function tearDown()
    {
        Phug::reset();

        parent::tearDown();
    }

    /**
     * @covers ::<public>
     * @covers ::getCallbacks
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

    /**
     * @covers ::<public>
     * @covers ::getCallbacks
     * @covers ::getMethodsByPrefix
     * @covers ::addCallback
     * @covers ::addSpecificCallback
     *
     * @throws \Phug\PhugException
     * @throws \Phug\RendererException
     * @throws \ReflectionException
     */
    public function testGlobalPlugin()
    {
        Plugin::enable();

        $this->assertSame('<section>9 + 9</section>', Phug::render('p=9 + 9'));

        $this->assertSame('<section>Hello!</section>', Phug::render('div Hello'));

        Plugin::disable();

        $this->assertSame('<p>18</p>', Phug::render('p=9 + 9'));

        $this->assertSame('<div>Hello</div>', Phug::render('div Hello'));
    }

    /**
     * @covers ::getContainer
     *
     * @throws \Phug\PhugException
     * @throws \Phug\RendererException
     * @throws \ReflectionException
     */
    public function testGetContainer()
    {
        $renderer = new Renderer();

        $this->assertSame($renderer, (new Plugin($renderer))->getContainer());
    }

    /**
     * @covers ::<public>
     *
     * @throws \Phug\PhugException
     * @throws \Phug\RendererException
     * @throws \ReflectionException
     */
    public function testPluginInstance()
    {
        $renderer = new Renderer();
        $plugin = new Plugin($renderer);
        $plugin->attachEvents();

        $this->assertSame('<section>9 + 9</section>', $renderer->render('p=9 + 9'));

        $this->assertSame('<section>Hello!</section>', $renderer->render('div Hello'));

        $plugin->detachEvents();

        $this->assertSame('<p>18</p>', $renderer->render('p=9 + 9'));

        $this->assertSame('<div>Hello</div>', $renderer->render('div Hello'));
    }

    /**
     * @covers ::<public>
     * @covers ::iterateTokens
     *
     * @throws \Phug\PhugException
     * @throws \Phug\RendererException
     * @throws \ReflectionException
     */
    public function testLexerPlugin()
    {
        LexerPlugin::enable();

        $this->assertSame('<p id="joker">18</p>', Phug::render('p#foo=9 + 9'));

        $this->assertSame('<p>Hello</p>', Phug::render('| Hello'));

        LexerPlugin::disable();

        $this->assertSame('<p>18</p>', Phug::render('p=9 + 9'));

        $this->assertSame('Hello', Phug::render('| Hello'));
    }

    /**
     * @covers ::getEventContainer
     *
     * @throws \Phug\PhugException
     * @throws \Phug\RendererException
     * @throws \ReflectionException
     */
    public function testParserPlugin()
    {
        ParserPlugin::enable();

        $this->assertSame('<p>Hello</p><footer></footer>', Phug::render('p Hello'));

        ParserPlugin::disable();

        $this->assertSame('<p>Hello</p>', Phug::render('p Hello'));
    }

    /**
     * @covers ::enable
     * @covers ::disable
     * @covers ::getEventContainer
     * @covers \Phug\Phug::isRendererInitialized
     *
     * @throws \Phug\PhugException
     * @throws \Phug\RendererException
     * @throws \ReflectionException
     */
    public function testRendererPlugin()
    {
        RendererPlugin::enable();

        $this->assertSame('<p>Hello</p><footer></footer>', Phug::render('p Hello'));

        RendererPlugin::disable();

        $this->assertSame('<p>Hello</p>', Phug::render('p Hello'));
    }

    /**
     * @covers ::enable
     * @covers ::disable
     * @covers \Phug\Phug::isRendererInitialized
     *
     * @throws \Phug\PhugException
     * @throws \Phug\RendererException
     * @throws \ReflectionException
     */
    public function testRendererPluginWithRendererAlreadySet()
    {
        $this->assertSame('<p>Hello</p>', Phug::render('p Hello'));

        RendererPlugin::enable();

        $this->assertSame('<p>Hello</p><footer></footer>', Phug::render('p Hello'));

        RendererPlugin::disable();

        $this->assertSame('<p>Hello</p>', Phug::render('p Hello'));
    }
}
