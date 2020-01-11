<?php

namespace Phug\Component;

use Phug\AbstractExtension;
use Phug\CompilerEvent;
use Phug\Formatter\Event\FormatEvent;
use Phug\LexerEvent;
use Phug\Parser\Event\ParseEvent;
use Phug\Phug;
use Phug\Renderer;
use Phug\RendererModuleInterface;
use Phug\Util\ModuleContainerInterface;
use Phug\Util\Partial\OptionTrait;
use ReflectionClass;

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

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer->setOptions([
            'keywords' => $this->getKeywords(),
        ]);
    }

    public function getContainer()
    {
        return $this->renderer;
    }

    public static function enable(Renderer $renderer = null)
    {
        if ($renderer) {
            (new static($renderer))->attachEvents();

            return;
        }

        Phug::addExtension(static::class);
    }

    public static function disable()
    {
        Phug::removeExtension(static::class);
    }

    public function getEventListeners()
    {
        return [
            CompilerEvent::NODE => [$this, 'handleNodeEvent'],
        ];
    }

    public function attachEvents()
    {
        foreach ($this->getEventListeners() as $event => $listener) {
            $this->attachEvent($event, $listener);
        }
    }

    public function detachEvents()
    {
        foreach ($this->getEventListeners() as $event => $listener) {
            $this->detachEvent($event, $listener);
        }
    }

    /**
     * Get the container able to listen the given event.
     *
     * @param string $event the event to be listenable
     *
     * @return ModuleContainerInterface
     */
    public function getEventContainer($event)
    {
        $instance = $this->renderer;

        if (in_array($event, (new ReflectionClass(CompilerEvent::class))->getConstants())) {
            return $instance->getCompiler();
        }

        if (in_array($event, (new ReflectionClass(FormatEvent::class))->getConstants())) {
            return $instance->getCompiler()->getFormatter();
        }

        if (in_array($event, (new ReflectionClass(ParseEvent::class))->getConstants())) {
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
     * @return bool true on success false on failure
     */
    public function detachEvent($event, $callback)
    {
        return $this->getEventContainer($event)->detach($event, $callback);
    }
}
