<?php

namespace Phug\Util;

use Phug\Util\Partial\OrderTrait;
use Phug\Util\Partial\ValueTrait;

/**
 * Class OrderedValue.
 *
 * @template T
 *
 * @template-implements ValueTrait<T>
 */
class OrderedValue implements OrderableInterface
{
    use ValueTrait;
    use OrderTrait;

    public function __construct($value, $order)
    {
        $this->value = $value;
        $this->order = $order;
    }
}
