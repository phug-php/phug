<?php

namespace Phug\Test;

use Phug\Compiler\Event\NodeEvent;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\Element\TextElement;
use Phug\Formatter\Event\FormatEvent;
use Phug\Formatter\Format\HtmlFormat;
use Phug\Lexer\Event\TokenEvent;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Parser\Node\ElementNode;
use Phug\Phug;
use Phug\PhugException;
use Phug\Renderer;
use Phug\RendererException;
use Phug\Test\Utils\LexerPlugin;
use Phug\Test\Utils\ParserPlugin;
use Phug\Test\Utils\Plugin;
use Phug\Test\Utils\RendererPlugin;
use ReflectionException;

/**
 * @coversDefaultClass \Phug\AbstractPlugin
 */
class PluginTest extends AbstractPhugTest
{
    protected function setUp()
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
     * @throws PhugException
     * @throws RendererException
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
     * @throws PhugException
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
     * @covers \Phug\Partial\PluginEnablerTrait::enable
     * @covers \Phug\Partial\PluginEnablerTrait::disable
     * @covers ::activateOnRenderer
     *
     * @throws PhugException
     */
    public function testDoubleTaps()
    {
        Plugin::enable();
        Plugin::enable();

        $this->assertSame('<section>9 + 9</section>', Phug::render('p=9 + 9'));

        $this->assertSame('<section>Hello!</section>', Phug::render('div Hello'));

        Plugin::disable();
        Plugin::disable();

        $this->assertSame('<p>18</p>', Phug::render('p=9 + 9'));

        $this->assertSame('<div>Hello</div>', Phug::render('div Hello'));
    }

    /**
     * @covers ::getContainer
     * @covers ::getRenderer
     * @covers ::getCompiler
     * @covers ::getFormatter
     * @covers ::getParser
     * @covers ::getLexer
     *
     * @throws RendererException
     */
    public function testGetContainer()
    {
        $renderer = new Renderer();
        $plugin = new Plugin($renderer);

        $this->assertSame($renderer, $plugin->getContainer());
        $this->assertSame($renderer, $plugin->getRenderer());
        $this->assertSame($renderer->getCompiler(), $plugin->getCompiler());
        $this->assertSame($renderer->getCompiler()->getFormatter(), $plugin->getFormatter());
        $this->assertSame($renderer->getCompiler()->getParser(), $plugin->getParser());
        $this->assertSame($renderer->getCompiler()->getParser()->getLexer(), $plugin->getLexer());
    }

    /**
     * @covers ::<public>
     *
     * @throws RendererException
     * @throws ReflectionException
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
     * @covers \Phug\Partial\TokenGeneratorTrait::getGeneratorFromIterable
     * @covers \Phug\Partial\TokenGeneratorTrait::getTokenGenerator
     *
     * @throws PhugException
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
     * @covers ::getEventToContainerMap
     * @covers ::getClassForEvents
     *
     * @throws PhugException
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
     * @covers ::getEventToContainerMap
     * @covers ::getClassForEvents
     * @covers \Phug\Phug::isRendererInitialized
     *
     * @throws PhugException
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
     * @throws PhugException
     */
    public function testRendererPluginWithRendererAlreadySet()
    {
        $this->assertSame('<p>Hello</p>', Phug::render('p Hello'));

        RendererPlugin::enable();

        $this->assertSame('<p>Hello</p><footer></footer>', Phug::render('p Hello'));

        RendererPlugin::disable();

        $this->assertSame('<p>Hello</p>', Phug::render('p Hello'));
    }

    /**
     * @covers ::handleFormatEvent
     *
     * @throws RendererException
     * @throws PhugException
     */
    public function testHandleFormatEvent()
    {
        $renderer = new Renderer();
        Plugin::enable($renderer);
        $plugin = $renderer->getModule(Plugin::class);
        $event = new FormatEvent(new ExpressionElement('call()'), new HtmlFormat());
        $plugin->handleFormatEvent($event);

        $this->assertInstanceOf(TextElement::class, $event->getElement());
    }

    /**
     * @covers ::handleNodeEvent
     *
     * @throws RendererException
     * @throws PhugException
     */
    public function testHandleNodeEvent()
    {
        $renderer = new Renderer();
        Plugin::enable($renderer);
        $plugin = $renderer->getModule(Plugin::class);
        $element = new ElementNode();
        $element->setName('p');
        $event = new NodeEvent($element);
        $plugin->handleNodeEvent($event);

        $this->assertSame('section', $element->getName());
    }

    /**
     * @covers ::handleTokenEvent
     *
     * @throws RendererException
     * @throws PhugException
     */
    public function testHandleTokenEvent()
    {
        $renderer = new Renderer();
        LexerPlugin::enable($renderer);
        $plugin = $renderer->getModule(LexerPlugin::class);
        $event = new TokenEvent(new TextToken());
        $plugin->handleTokenEvent($event);

        $tokens = [];

        foreach ($event->getTokenGenerator() as $token) {
            $tokens[] = get_class($token);
        }

        $this->assertSame([TagToken::class, TextToken::class], $tokens);
    }
}
