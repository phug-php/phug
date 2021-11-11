<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\AttributesInterface;
use Phug\Util\Partial\AssignmentTrait;
use Phug\Util\Partial\AttributeTrait;
use Phug\Util\Partial\NameTrait;

class MixinCallNode extends Node implements AttributesInterface
{
    use NameTrait;
    use AttributeTrait;
    use AssignmentTrait;

    /**
     * @var bool
     */
    private $argumentsCompleted = false;

    /**
     * @return mixed
     */
    public function areArgumentsCompleted()
    {
        return $this->argumentsCompleted;
    }

    /**
     * @return $this
     */
    public function markArgumentsAsComplete()
    {
        $this->argumentsCompleted = true;

        return $this;
    }
}
