<?php

namespace Phug\Test\Utils;

use DateTimeImmutable;

class Clock implements ClockInterface
{
    public function now()
    {
        return new DateTimeImmutable('now');
    }
}
