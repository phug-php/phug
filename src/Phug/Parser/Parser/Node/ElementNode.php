<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\AttributesInterface;
use Phug\Util\AttributesOrderInterface;
use Phug\Util\Partial\AssignmentTrait;
use Phug\Util\Partial\AttributesOrderTrait;
use Phug\Util\Partial\AttributeTrait;
use Phug\Util\Partial\NameTrait;

class ElementNode extends Node implements AttributesInterface, AttributesOrderInterface
{
    use NameTrait;
    use AttributeTrait;
    use AssignmentTrait;
    use AttributesOrderTrait;

    /**
     * @var bool
     */
    protected $autoClosed = false;

    /**
     * @return string
     */
    public function getAttribute($name)
    {
        foreach ($this->getAttributes() as $attribute) {
            if ($attribute->getName() === $name) {
                return $attribute->getValue();
            }
        }
    }

    /**
     * For the element to be auto-closed.
     */
    public function autoClose()
    {
        $this->autoClosed = true;
    }

    /**
     * @return bool true if the element is auto-closed
     */
    public function isAutoClosed()
    {
        return $this->autoClosed;
    }
}
