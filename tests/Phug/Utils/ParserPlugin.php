<?php

namespace Phug\Test\Utils;

use Phug\AbstractPlugin;
use Phug\Parser\Event\ParseEvent;

class ParserPlugin extends AbstractPlugin
{
    public function onParse(ParseEvent $event)
    {
        $event->setInput($event->getInput()."\nfooter");
    }
}
