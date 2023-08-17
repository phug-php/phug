<?php

namespace Phug\Util;

/**
 * Interface AttributesOrderInterface.
 *
 * Allow objects that have attributes to remember of the order in which attributes and assignments
 * were added to them.
 */
interface AttributesOrderInterface
{
    /**
     * @return int
     */
    public function getNextAttributeIndex();
}
