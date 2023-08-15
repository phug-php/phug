<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\AttributesInterface;
use Phug\Util\OrderableInterface;
use Phug\Util\Partial\AttributeTrait;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\OrderTrait;

class AssignmentNode extends Node implements AttributesInterface, OrderableInterface
{
    use NameTrait;
    use AttributeTrait;
    use OrderTrait;
}
