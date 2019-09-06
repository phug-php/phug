<?php

namespace Phug\Test\Extension;

class Ev2Extension extends AbstractEventExtension
{
    public function getOptions()
    {
        return [
            'on_format' => static::getStaticFormatEvent('b', 'b'),
        ];
    }

    public function getEvents()
    {
        return [
            'on_node'   => static::getStaticNodeEvent('c', 'c'),
            'on_format' => static::getStaticFormatEvent('d', 'd'),
        ];
    }
}
