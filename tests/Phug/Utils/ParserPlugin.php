<?php

namespace Phug\Test\Utils;

use Phug\AbstractPlugin;
use Phug\Lexer\Token\IdToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Parser\Event\ParseEvent;

class ParserPlugin extends AbstractPlugin
{
    public function onParse(ParseEvent $event)
    {
        $event->setInput($event->getInput()."\nfooter");
    }
}
