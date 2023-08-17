<?php

namespace Phug\Util;

use ReturnTypeWillChange;
use SplObjectStorage;

class AttributesStorage extends SplObjectStorage
{
    /** @var mixed */
    private $holder;

    public function __construct($holder)
    {
        $this->holder = $holder;
    }

    #[ReturnTypeWillChange]
    public function attach($object, $info = null)
    {
        if ($object instanceof OrderableInterface &&
            $object->getOrder() === null &&
            $this->holder instanceof AttributesOrderInterface
        ) {
            $object->setOrder($this->holder->getNextAttributeIndex());
        }

        parent::attach($object, $info);
    }

    #[ReturnTypeWillChange]
    public function addAll($storage)
    {
        if ($storage && $this->holder instanceof AttributesOrderInterface) {
            foreach ($storage as $element) {
                if ($element instanceof OrderableInterface && $element->getOrder() === null) {
                    $element->setOrder($this->holder->getNextAttributeIndex());
                }
            }
        }

        parent::addAll($storage);
    }
}
