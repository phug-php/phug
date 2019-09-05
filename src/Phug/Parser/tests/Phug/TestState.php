<?php

namespace Phug\Test;

use Phug\Parser;
use Phug\Parser\State;

class TestState extends State
{
    private static $lastOptions;

    public function __construct(Parser $parser, \Generator $tokens, array $options = null)
    {
        parent::__construct($parser, $tokens, $options);

        static::$lastOptions = $options;
    }

    public static function getLastOptions()
    {
        return static::$lastOptions;
    }
}
