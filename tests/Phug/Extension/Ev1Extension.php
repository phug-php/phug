<?php

namespace Phug\Test\Extension;

class Ev1Extension extends AbstractEventExtension
{
    public function getOptions()
    {
        return [
            'on_node'   => static::getStaticNodeEvent('foo', '42'),
            'on_format' => [
                static::getStaticFormatEvent('a', 'a'),
            ],
        ];
    }

    public function getEvents()
    {
        return [
            'on_node' => [
                static::getStaticNodeEvent('bar', '9'),
                static::getStaticNodeEvent('biz', '1'),
            ],
        ];
    }
}
