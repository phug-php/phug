<?php

namespace Phug\Util\Partial;

use ArrayAccess;
use ArrayObject;
use Phug\Util\Collection;

trait OrderTrait
{
    /**
     * @var int|null
     */
    private $order = null;

    /**
     * @return int|null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int|null $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }
}
