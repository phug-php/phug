<?php

namespace Phug\Util\Partial;

trait AttributesOrderTrait
{
    /**
     * @var int
     */
    protected $attributeIndex = 0;

    /**
     * @return int
     */
    public function getNextAttributeIndex()
    {
        return $this->attributeIndex++;
    }
}
