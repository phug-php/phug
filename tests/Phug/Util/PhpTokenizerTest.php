<?php

namespace Phug\Test\Util;

//@codingStandardsIgnoreStart
use PHPUnit\Framework\TestCase;
use Phug\Util\PhpTokenizer;

/**
 * @coversDefaultClass \Phug\Util\PhpTokenizer
 */
class PhpTokenizerTest extends TestCase
{
    /**
     * @covers ::getTokens
     */
    public function testGetTokens()
    {
        $tokens = PhpTokenizer::getTokens('$foo = 9;');

        self::assertSame('$foo', $tokens[0][1]);
        self::assertSame(';', $tokens[5]);
    }
}
//@codingStandardsIgnoreEnd
