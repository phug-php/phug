<?php

namespace Phug\Test;

use PHPUnit\Framework\TestCase;
use Phug\Phug;

abstract class AbstractPhugTest extends TestCase
{
    /**
     * @var VerbatimExtension
     */
    protected $verbatim;

    public function setUp()
    {
        include_once __DIR__.'/VerbatimExtension.php';
        $this->verbatim = new VerbatimExtension(new Phug());
    }
}
