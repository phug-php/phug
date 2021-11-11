<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\AttributesInterface;
use Phug\Util\Partial\AttributeTrait;
use Phug\Util\Partial\NameTrait;

class AssignmentNode extends Node implements AttributesInterface
{
    use NameTrait;
    use AttributeTrait;
}
