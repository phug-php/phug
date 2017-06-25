<?php

namespace Phug\Test;

use Phug\Phug;

abstract class AbstractPhugTest extends \PHPUnit_Framework_TestCase
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
