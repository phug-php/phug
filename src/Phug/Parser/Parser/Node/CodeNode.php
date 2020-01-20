<?php

namespace Phug\Parser\Node;

use Phug\Formatter\Partial\TransformableTrait;
use Phug\Parser\Node;
use Phug\Util\Partial\BlockTrait;
use Phug\Util\Partial\ValueTrait;

class CodeNode extends Node
{
    use ValueTrait;
    use BlockTrait;
    use TransformableTrait;
}
