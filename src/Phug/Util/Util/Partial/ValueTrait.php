<?php

namespace Phug\Util\Partial;

/**
 * Class ValueTrait.
 *
 * @template T of mixed
 */
trait ValueTrait
{
    use StaticMemberTrait;

    /**
     * @var T
     */
    private $value = null;

    /**
     * @return T
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function hasStaticValue()
    {
        return $this->hasStaticMember('value');
    }

    /**
     * @param T $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
