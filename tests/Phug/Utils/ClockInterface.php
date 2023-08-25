<?php

namespace Phug\Test\Utils;

use DateTimeImmutable;

interface ClockInterface
{
    /** @return DateTimeImmutable */
    public function now();
}
