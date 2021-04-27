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

    protected function setUp(): void
    {
        $this->prepareTest();
    }

    protected function tearDown(): void
    {
        $this->finishTest();
    }
}
