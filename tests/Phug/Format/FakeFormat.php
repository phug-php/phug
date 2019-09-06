<?php

namespace Phug\Test\Format;

use ArrayAccess;
use Phug\Formatter;
use Phug\Formatter\AbstractFormat;
use Phug\Formatter\ElementInterface;

class FakeFormat extends AbstractFormat implements ArrayAccess
{
    public function __invoke(ElementInterface $element)
    {
        $this->setFormatter(new Formatter([
            'dependencies_storage' => 'format',
        ]));
        $helper = $this->getHelper('get_helper');

        return $helper($element->getName());
    }

    public function offsetExists($offset)
    {
        return strpos($offset, 'non_existing') === false;
    }

    public function offsetGet($offset)
    {
        return $offset;
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }
}
