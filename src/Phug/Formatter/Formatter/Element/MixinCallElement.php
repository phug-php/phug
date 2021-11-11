<?php

namespace Phug\Formatter\Element;

use Phug\Util\AttributesInterface;
use Phug\Util\Partial\AttributeTrait;
use Phug\Util\Partial\NameTrait;

class MixinCallElement extends AbstractAssignmentContainerElement implements AttributesInterface
{
    use AttributeTrait;
    use NameTrait;
}
