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

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return strpos($offset, 'non_existing') === false;
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $offset;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
    }
}
