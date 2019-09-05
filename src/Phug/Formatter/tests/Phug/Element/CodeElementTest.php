<?php

namespace Phug\Test\Element;

use PHPUnit\Framework\TestCase;
use Phug\Formatter;
use Phug\Formatter\Element\CodeElement;

/**
 * @coversDefaultClass \Phug\Formatter\Element\CodeElement
 */
class CodeElementTest extends TestCase
{
    /**
     * @covers ::<public>
     */
    public function testCodeElement()
    {
        $foo = new CodeElement('$foo');

        self::assertSame('$foo', $foo->getValue());
    }

    /**
     * @covers ::<public>
     */
    public function testHooks()
    {
        $code = new CodeElement('$foo = 9;');
        $code->setPreHook('aa');
        self::assertSame('aa', $code->getPreHook());
        $code->setPreHook('bb');
        self::assertSame('bb', $code->getPreHook());
        $code->setPostHook('aa');
        self::assertSame('aa', $code->getPostHook());
        $code->setPostHook('bb');
        self::assertSame('bb', $code->getPostHook());
        $code->setPreHook('$__eachScopeVariables = [\'foo\' => isset($foo) ? $foo : null];');
        $code->setPostHook('extract($__eachScopeVariables);');
        $formatter = new Formatter();

        ob_start();
        $php = $formatter->format($code);
        eval('?>'.$formatter->formatDependencies().$php);
        ob_end_clean();

        self::assertNull($foo);

        $code = new CodeElement('$foo = 9;');
        $formatter = new Formatter();

        ob_start();
        $php = $formatter->format($code);
        eval('?>'.$formatter->formatDependencies().$php);
        ob_end_clean();

        self::assertSame(9, $foo);

        $code = new CodeElement('$foo = 9;');
        $code->setPreHook('$__eachScopeVariables = [\'foo\' => isset($foo) ? $foo : null];');
        $code->setPostHook('extract($__eachScopeVariables);');
        $formatter = new Formatter();

        ob_start();
        $foo = 42;
        $php = $formatter->format($code);
        eval('?>'.$formatter->formatDependencies().$php);
        ob_end_clean();

        self::assertSame(42, $foo);
    }

    /**
     * @covers ::<public>
     */
    public function testAppendAndPrepend()
    {
        $code = new CodeElement('$foo = 9;');
        $code->prependCode('aa');
        self::assertSame('aa', $code->getPreHook());
        $code->prependCode('bb');
        self::assertSame('bbaa', $code->getPreHook());
        $code->appendCode('aa');
        self::assertSame('aa', $code->getPostHook());
        $code->appendCode('bb');
        self::assertSame('aabb', $code->getPostHook());

        $code = new CodeElement('$foo = 9;');
        $code->prependCode('$__eachScopeVariables = [\'foo\' => isset($foo) ? $foo : null];');
        $code->appendCode('extract($__eachScopeVariables);');
        $formatter = new Formatter();

        ob_start();
        $php = $formatter->format($code);
        eval('?>'.$formatter->formatDependencies().$php);
        ob_end_clean();

        self::assertNull($foo);

        $code = new CodeElement('$foo = 9;');
        $formatter = new Formatter();

        ob_start();
        $php = $formatter->format($code);
        eval('?>'.$formatter->formatDependencies().$php);
        ob_end_clean();

        self::assertSame(9, $foo);

        $code = new CodeElement('$foo = 9;');
        $code->prependCode('$__eachScopeVariables = [\'foo\' => isset($foo) ? $foo : null];');
        $code->appendCode('extract($__eachScopeVariables);');
        $formatter = new Formatter();

        ob_start();
        $foo = 42;
        $php = $formatter->format($code);
        eval('?>'.$formatter->formatDependencies().$php);
        ob_end_clean();

        self::assertSame(42, $foo);
    }
}
