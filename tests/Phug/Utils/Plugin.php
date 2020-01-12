<?php

namespace Phug\Test\Utils;

use Phug\Compiler\Event\NodeEvent;
use Phug\AbstractPlugin;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\Element\TextElement;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\TextNode;

class Plugin extends AbstractPlugin
{
    public function onNodeEvent(NodeEvent $event)
    {
        $node = $event->getNode();

        if ($node instanceof TextNode) {
            $node->setValue($node->getValue().'!');
        }
    }

    public function onElementNode(ElementNode $element)
    {
        $element->setName('section');
    }

    public function onExpressionElement(ExpressionElement $expression)
    {
        return new TextElement($expression->getValue());
    }
}
