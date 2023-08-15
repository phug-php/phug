<?php

namespace Phug\Util;

/**
 * Interface OrderableInterface.
 *
 * Allow objects to have an order index which allow, when compared to others in a list,
 * to know if they were added earlier or later.
 */
interface OrderableInterface
{
    /**
     * @return int|null
     */
    public function getOrder();

    /**
     * @param int|null $order
     */
    public function setOrder($order);
}
