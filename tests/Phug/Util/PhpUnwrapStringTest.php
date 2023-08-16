<?php

namespace Phug\Test\Util;

use Phug\Formatter\Util\PhpUnwrapString;
use Phug\Util\TestCase;

/**
 * @coversDefaultClass \Phug\Formatter\Util\PhpUnwrapString
 */
class PhpUnwrapStringTest extends TestCase
{
    /**
     * @covers \Phug\Formatter\Util\PhpUnwrapString::<public>
     * @covers ::<public>
     */
    public function testPhpUnwrapString()
    {
        self::assertSame('echo "Foo";', (string) PhpUnwrapString::withoutOpenTag('<?php echo "Foo";'));
        self::assertSame('echo "Foo"; ?>', (string) PhpUnwrapString::withoutOpenTag('<?php echo "Foo"; ?>'));
        self::assertSame('?><div><?php echo "Foo"; ?></div>', (string) PhpUnwrapString::withoutOpenTag('<div><?php echo "Foo"; ?></div>'));
    }
}
