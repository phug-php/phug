<?php

namespace Phug\Component;

use Phug\AbstractExtension;
use Phug\CompilerEvent;
use Phug\Phug;
use Phug\Renderer;
use Phug\RendererModuleInterface;
use Phug\Util\Partial\OptionTrait;

abstract class AbstractModule extends AbstractExtension implements RendererModuleInterface
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

    public function attachEvents()
    {
        $this->renderer->getCompiler()->attach(CompilerEvent::NODE, [$this, 'handleNodeEvent']);
    }

    public function detachEvents()
    {
        $this->renderer->getCompiler()->detach(CompilerEvent::NODE, [$this, 'handleNodeEvent']);
    }
}
