<?php

namespace Phug\Util;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCaseTypeBase extends PHPUnitTestCase
{
    protected function prepareTest()
    {
        // Override
    }

    protected function finishTest()
    {
        // Override
    }

    protected function setUp()
    {
        $this->prepareTest();
    }

    protected function tearDown()
    {
        $this->finishTest();
    }
}
