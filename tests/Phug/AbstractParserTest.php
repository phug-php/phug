<?php

namespace Phug\Test;

use Phug\Parser;
use Phug\Util\TestCase;

abstract class AbstractParserTest extends TestCase
{
    /**
     * @var Parser
     */
    protected $parser;

    protected function prepareTest()
    {
        $this->parser = new Parser();
    }

    protected function assertNodes($expression, $expected, Parser $parser = null)
    {
        if (is_array($expected)) {
            $expected = implode("\n", $expected);
        }

        $parser = $parser ?: $this->parser;

        $dump = str_replace('Phug\\Parser\\Node\\', '', $parser->dump($expression));

        self::assertSame($expected, $dump);
    }
}
