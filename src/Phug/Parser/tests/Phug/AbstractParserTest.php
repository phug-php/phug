<?php

namespace Phug\Test;

use PHPUnit\Framework\TestCase;
use Phug\Parser;

abstract class AbstractParserTest extends TestCase
{
    /**
     * @var Parser
     */
    protected $parser;

    public function setUp()
    {
        $this->parser = new Parser();
    }

    public function assertNodes($expression, $expected, Parser $parser = null)
    {
        if (is_array($expected)) {
            $expected = implode("\n", $expected);
        }

        $parser = $parser ?: $this->parser;

        $dump = str_replace('Phug\\Parser\\Node\\', '', $parser->dump($expression));

        self::assertSame($expected, $dump);
    }
}
