<?php

namespace Phug\Test\Extension;

use Phug\AbstractExtension;
use Phug\Compiler\Event\NodeEvent;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\Event\FormatEvent;
use Phug\Parser\Node\AttributeNode;
use Phug\Parser\Node\ElementNode;

abstract class AbstractEventExtension extends AbstractExtension
{
    public static function getStaticNodeEvent($name, $value)
    {
        return function (NodeEvent $event) use ($name, $value) {
            $node = $event->getNode();
            if ($node instanceof ElementNode) {
                $attribute = new AttributeNode();
                $attribute->setName($name);
                $attribute->setValue($value);
                $node->getAttributes()->attach($attribute);
            }
        };
    }

    public static function getStaticFormatEvent($name, $value)
    {
        return function (FormatEvent $event) use ($name, $value) {
            $node = $event->getElement();
            if ($node instanceof MarkupElement) {
                $attribute = new AttributeElement();
                $attribute->setName($name);
                $attribute->setValue($value);
                $node->getAttributes()->attach($attribute);
            }
        };
    }
}
