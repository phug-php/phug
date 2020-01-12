<?php

namespace Phug\Test\Utils;

use Phug\AbstractPlugin;
use Phug\Renderer\Event\RenderEvent;

class RendererPlugin extends AbstractPlugin
{
    public function onRender(RenderEvent $event)
    {
        $event->setInput($event->getInput()."\nfooter");
    }
}
