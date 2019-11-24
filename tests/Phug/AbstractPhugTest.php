<?php

namespace Phug\Test;

use Phug\Test\Extension\VerbatimExtension;
use Phug\Util\TestCase;

abstract class AbstractPhugTest extends TestCase
{
    /**
     * @var VerbatimExtension
     */
    protected $verbatim;

    public function setUp()
    {
        $this->verbatim = new VerbatimExtension();
    }
}
