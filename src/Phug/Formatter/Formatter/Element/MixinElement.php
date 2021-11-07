<?php

namespace Phug\Formatter\Element;

use Phug\Formatter\AbstractElement;
use Phug\Util\AttributesInterface;
use Phug\Util\Partial\AttributeTrait;
use Phug\Util\Partial\NameTrait;

class MixinElement extends AbstractElement implements AttributesInterface
{
    use AttributeTrait;
    use NameTrait;
}
