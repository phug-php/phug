<?php

namespace Phug\Test\Util;

use PHPUnit\Framework\TestCase;
use Phug\Formatter;
use Phug\Formatter\Element\AnonymousBlockElement;
use Phug\Formatter\Element\AssignmentElement;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\DocumentElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\Element\MixinCallElement;
use Phug\Formatter\Element\MixinElement;
use Phug\Formatter\Element\TextElement;
use Phug\Formatter\Util\PhpUnwrapString;
use Phug\Lexer\Token\MixinCallToken;
use Phug\Parser\Node\MixinCallNode;
use Phug\Util\SourceLocation;
use SplObjectStorage;

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
