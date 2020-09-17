<?php

namespace Phug\Test\Util;

//@codingStandardsIgnoreStart
use ErrorException;
use Exception;
use PHPUnit\Framework\TestCase;
use Phug\Util\SandBox;

/**
 * @coversDefaultClass \Phug\Util\SandBox
 */
class SandBoxTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getResult
     * @covers ::getBuffer
     * @covers ::outputBuffer
     */
    public function testSuccess()
    {
        $sandBox = new SandBox(function () {
            echo 'bar';

            return 'foo';
        });

        self::assertNull($sandBox->getThrowable());
        self::assertSame('foo', $sandBox->getResult());
        self::assertSame('bar', $sandBox->getBuffer());

        ob_start();
        $sandBox->outputBuffer();
        $contents = ob_get_contents();
        ob_end_clean();

        self::assertSame('bar', $contents);

        ob_start();
        $sandBox->outputBuffer();
        $contents = ob_get_contents();
        ob_end_clean();

        self::assertSame('', $contents);
    }

    /**
     * @covers ::__construct
     * @covers ::getThrowable
     * @covers ::getBuffer
     */
    public function testError()
    {
        $sandBox = new SandBox(function () {
            echo 'foo';
            $a = trigger_error('Division by zero');
            echo 'bar';

            return $a;
        });

        self::assertInstanceOf(Exception::class, $sandBox->getThrowable());
        self::assertContains('Division by zero', $sandBox->getThrowable()->getMessage());
        self::assertNull($sandBox->getResult());
        self::assertSame('foo', $sandBox->getBuffer());

        $sandBox = new SandBox(function () {
            return @trigger_error('Notice', E_USER_NOTICE);
        });

        self::assertNull($sandBox->getThrowable());

        $sandBox = new SandBox(function () {
            return trigger_error('Notice', E_USER_NOTICE);
        });

        self::assertInstanceOf(Exception::class, $sandBox->getThrowable());
        self::assertContains('Notice', $sandBox->getThrowable()->getMessage());

        $level = error_reporting();

        error_reporting(E_ALL);
        $sandBox = new SandBox(function () {
            $a = [];
            $b = $a['foo'];
        });

        self::assertInstanceOf(ErrorException::class, $sandBox->getThrowable());

        error_reporting(E_ALL ^ E_USER_NOTICE);

        $sandBox = new SandBox(function () {
            trigger_error('Undefined index');
        });

        self::assertNull($sandBox->getThrowable());

        error_reporting(E_ALL);

        $sandBox = new SandBox(function () {
            trigger_error('Undefined index');
        }, function ($number, $message, $file, $line) {
            throw new ErrorException('interceptor', $number);
        });

        self::assertSame('interceptor', $sandBox->getThrowable()->getMessage());
        self::assertSame(E_USER_NOTICE, $sandBox->getThrowable()->getCode());

        error_reporting($level);
    }
}
//@codingStandardsIgnoreEnd
