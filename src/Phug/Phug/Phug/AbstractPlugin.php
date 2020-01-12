<?php

namespace Phug;

use Iterator;
use Phug\Compiler\Event\CompileEvent;
use Phug\Compiler\Event\ElementEvent;
use Phug\Compiler\Event\NodeEvent;
use Phug\Compiler\Event\OutputEvent;
use Phug\Formatter\ElementInterface;
use Phug\Formatter\Event\DependencyStorageEvent;
use Phug\Formatter\Event\FormatEvent;
use Phug\Formatter\Event\StringifyEvent;
use Phug\Lexer\Event\EndLexEvent;
use Phug\Lexer\Event\LexEvent;
use Phug\Lexer\Event\TokenEvent;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Event\ParseEvent;
use Phug\Parser\NodeInterface;
use Phug\Renderer\Event\HtmlEvent;
use Phug\Renderer\Event\RenderEvent;
use Phug\Util\ModuleContainerInterface;
use Phug\Util\Partial\OptionTrait;
use ReflectionClass;
use ReflectionException;

/**
 * A plug-in can be used both as an extension (globally enabled with MyPlugin::enable()) or
 * as a module scoped to a given renderer (with MyPlugin::enable($renderer)).
 */
abstract class AbstractPlugin extends AbstractExtension implements RendererModuleInterface
{
    use OptionTrait;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var callable[][]
     */
    private $callbacks;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer->setOptions(Phug::getExtensionsOptions([static::class]));
    }

    public function getContainer()
    {
        return $this->renderer;
    }

    /**
     * @param Renderer|null $renderer
     *
     * @throws ReflectionException
     * @throws PhugException
     */
    public static function enable(Renderer $renderer = null)
    {
        if ($renderer) {
            (new static($renderer))->attachEvents();

            return;
        }

        Phug::addExtension(static::class);

        if (Phug::isRendererInitialized()) {
            (new static(Phug::getRenderer()))->attachEvents();
        }
    }

    /**
     * @throws ReflectionException
     */
    public static function disable()
    {
        $className = static::class;
        Phug::removeExtension($className);

        if (Phug::isRendererInitialized()) {
            $renderer = Phug::getRenderer();
            $renderer->getModule($className)->detachEvents();
            $modules = $renderer->getOption('modules');

            if (is_array($modules)) {
                $renderer->setOption('modules', array_filter($modules, function ($module) use ($className) {
                    return $module !== $className;
                }));
            }
        }
    }

    private function getCallbacks($name)
    {
        list(, $method) = explode('::', $name);

        return $this->callbacks[$method];
    }

    /**
     * @param callable[] $callbacks
     * @param iterable   $tokens
     *
     * @throws ReflectionException
     *
     * @return \Generator|void
     */
    private function iterateTokens($callbacks, $tokens)
    {
        if (count($callbacks) === 0) {
            foreach ($tokens as $token) {
                yield $token;
            }

            return;
        }

        $callback = array_shift($callbacks);

        foreach ($tokens as $token) {
            $result = is_a($token, Invoker::getCallbackType($callback)) ? $callback($token) : null;
            $result = $result ?: $token;

            if (!($result instanceof Iterator)) {
                $result = [$result];
            }

            foreach ($this->iterateTokens($callbacks, $result) as $newToken) {
                yield $newToken;
            }
        }
    }

    /**
     * @param TokenEvent $tokenEvent
     *
     * @throws ReflectionException
     */
    public function handleTokenEvent(TokenEvent $tokenEvent)
    {
        $tokenEvent->setTokenGenerator(
            $this->iterateTokens($this->getCallbacks(__METHOD__), [$tokenEvent->getToken()])
        );
    }

    /**
     * @param NodeEvent $nodeEvent
     *
     * @throws ReflectionException
     */
    public function handleNodeEvent(NodeEvent $nodeEvent)
    {
        $node = $nodeEvent->getNode();

        foreach ($this->getCallbacks(__METHOD__) as $callback) {
            if (is_a($node, Invoker::getCallbackType($callback))) {
                $node = $callback($node) ?: $node;
            }
        }

        $nodeEvent->setNode($node);
    }

    /**
     * @param FormatEvent $formatEvent
     *
     * @throws ReflectionException
     */
    public function handleFormatEvent(FormatEvent $formatEvent)
    {
        $element = $formatEvent->getElement();

        foreach ($this->getCallbacks(__METHOD__) as $callback) {
            if (is_a($element, Invoker::getCallbackType($callback))) {
                $element = $callback($element, $formatEvent->getFormat()) ?: $element;
            }
        }

        $formatEvent->setElement($element);
    }

    protected function getMethodsByPrefix($prefix)
    {
        foreach (get_class_methods($this) as $method) {
            if (preg_match('/^'.$prefix.'[A-Z]/', $method)) {
                yield $method;
            }
        }
    }

    protected function addCallback($methodName, $callback)
    {
        if (!isset($this->callbacks[$methodName])) {
            $this->callbacks[$methodName] = [];
        }

        $this->callbacks[$methodName][] = $callback;
    }

    protected function addSpecificCallback(&$methods, $methodTypes, $type, $callback)
    {
        foreach ($methodTypes as $methodName => list($className, $eventName)) {
            if (is_a($type, $className, true)) {
                $methods[$methodName] = $eventName;
                $this->addCallback($methodName, $callback);

                return true;
            }
        }

        return false;
    }

    /**
     * @throws ReflectionException
     *
     * @return array[]
     */
    public function getEventsList()
    {
        $listeners = [];
        $methods = [];
        $methodTypes = [
            'handleTokenEvent'  => [TokenInterface::class, LexerEvent::TOKEN],
            'handleNodeEvent'   => [NodeInterface::class, CompilerEvent::NODE],
            'handleFormatEvent' => [ElementInterface::class, FormatterEvent::FORMAT],
        ];
        $types = [
            ParseEvent::class             => ParserEvent::PARSE,
            NodeEvent::class              => CompilerEvent::NODE,
            CompileEvent::class           => CompilerEvent::COMPILE,
            OutputEvent::class            => CompilerEvent::OUTPUT,
            ElementEvent::class           => CompilerEvent::ELEMENT,
            TokenEvent::class             => LexerEvent::TOKEN,
            LexEvent::class               => LexerEvent::LEX,
            EndLexEvent::class            => LexerEvent::END_LEX,
            FormatEvent::class            => FormatterEvent::FORMAT,
            StringifyEvent::class         => FormatterEvent::STRINGIFY,
            DependencyStorageEvent::class => FormatterEvent::DEPENDENCY_STORAGE,
            RenderEvent::class            => RendererEvent::RENDER,
            HtmlEvent::class              => RendererEvent::HTML,
        ];

        foreach ($this->getMethodsByPrefix('on') as $method) {
            $callback = [$this, $method];
            $type = Invoker::getCallbackType($callback);

            if ($this->addSpecificCallback($methods, $methodTypes, $type, $callback)) {
                continue;
            }

            $type = isset($types[$type]) ? $types[$type] : $type;
            $listeners[] = [$type, $callback];
        }

        foreach ($methods as $method => $eventName) {
            $listeners[] = [$eventName, [$this, $method]];
        }

        return $listeners;
    }

    /**
     * @throws ReflectionException
     */
    public function attachEvents()
    {
        foreach ($this->getEventsList() as list($event, $listener)) {
            $this->attachEvent($event, $listener);
        }
    }

    /**
     * @throws ReflectionException
     */
    public function detachEvents()
    {
        foreach ($this->getEventsList() as list($event, $listener)) {
            $this->detachEvent($event, $listener);
        }
    }

    /**
     * Get the container able to listen the given event.
     *
     * @param string $event the event to be listenable
     *
     * @throws ReflectionException
     *
     * @return ModuleContainerInterface
     */
    public function getEventContainer($event)
    {
        $instance = $this->renderer;

        if (in_array($event, (new ReflectionClass(CompilerEvent::class))->getConstants())) {
            return $instance->getCompiler();
        }

        if (in_array($event, (new ReflectionClass(FormatterEvent::class))->getConstants())) {
            return $instance->getCompiler()->getFormatter();
        }

        if (in_array($event, (new ReflectionClass(ParserEvent::class))->getConstants())) {
            return $instance->getCompiler()->getParser();
        }

        if (in_array($event, (new ReflectionClass(LexerEvent::class))->getConstants())) {
            return $instance->getCompiler()->getParser()->getLexer();
        }

        return $instance;
    }

    /**
     * Attaches a listener to an event.
     *
     * @param string   $event    the event to attach too
     * @param callable $callback a callable function
     * @param int      $priority the priority at which the $callback executed
     *
     * @throws ReflectionException
     *
     * @return bool true on success false on failure
     */
    public function attachEvent($event, $callback, $priority = 0)
    {
        return $this->getEventContainer($event)->attach($event, $callback, $priority);
    }

    /**
     * Detaches a listener from an event.
     *
     * @param string   $event    the event to attach too
     * @param callable $callback a callable function
     *
     * @throws ReflectionException
     *
     * @return bool true on success false on failure
     */
    public function detachEvent($event, $callback)
    {
        return $this->getEventContainer($event)->detach($event, $callback);
    }
}
