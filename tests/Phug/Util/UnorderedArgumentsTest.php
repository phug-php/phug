<?php

namespace Phug\Test\Util;

use PHPUnit\Framework\TestCase;
use Phug\Util\UnorderedArguments;

//@codingStandardsIgnoreStart
interface Abc
{
}
class Def implements Abc
{
}
/**
 * Class UnorderedArgumentsTest.
 *
 * @coversDefaultClass Phug\Util\UnorderedArguments
 */
class UnorderedArgumentsTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::optional
     */
    public function testOptional()
    {
        $arguments = new UnorderedArguments(['foo', 42]);

        self::assertSame(42, $arguments->optional('integer'));
        self::assertNull($arguments->optional('array'));
        self::assertSame('foo', $arguments->optional('string'));
    }

    /**
     * @covers ::required
     */
    public function testRequired()
    {
        $argument = new UnorderedArguments(['test']);
        $arguments = new UnorderedArguments(['foo', 42, $argument]);

        self::assertSame('foo', $arguments->required('string'));
        self::assertSame($argument, $arguments->required(UnorderedArguments::class));
        self::assertSame(42, $arguments->required('integer'));
    }

    /**
     * @covers                   ::required
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Arguments miss one of the boolean type
     */
    public function testRequiredException()
    {
        $argument = new UnorderedArguments(['test']);
        $arguments = new UnorderedArguments(['foo', 42, $argument, []]);

        $arguments->required('boolean');
    }

    /**
     * @covers ::noMoreArguments
     */
    public function testNoMoreArguments()
    {
        $arguments = new UnorderedArguments(['foo']);

        $arguments->optional('string');
        $this->assertNull($arguments->noMoreArguments());
    }

    /**
     * @covers                   ::noMoreArguments
     * @covers                   ::noMoreDefinedArguments
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage You pass 2 unexpected arguments
     */
    public function testNoMoreArgumentsException()
    {
        $arguments = new UnorderedArguments(['foo', 'bar', 'biz', 42]);

        $arguments->optional('string');
        $arguments->required('string');
        $arguments->noMoreArguments();
    }

    /**
     * @covers                   ::noMoreArguments
     * @covers                   ::noMoreDefinedArguments
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage You pass 2 unexpected arguments
     */
    public function testNoMoreUndefinedArgumentsException()
    {
        $arguments = new UnorderedArguments(['foo', null, 'biz', null]);

        $arguments->optional('string');
        $arguments->required('string');
        $arguments->noMoreDefinedArguments();
        $arguments->noMoreArguments();
    }

    /**
     * @covers                   ::noMoreArguments
     * @covers                   ::noMoreDefinedArguments
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage You pass 1 unexpected not null arguments
     */
    public function testNoMoreDefinedArgumentsException()
    {
        $arguments = new UnorderedArguments(['foo', 'biz', 1, null]);

        $arguments->optional('string');
        $arguments->required('string');
        $arguments->noMoreDefinedArguments();
        $arguments->noMoreArguments();
    }

    /**
     * @covers ::optional
     */
    public function testInterfacesAsString()
    {
        $arguments = new UnorderedArguments([Def::class]);

        $def = $arguments->optional(Abc::class);

        $this->assertSame(Def::class, $def);
    }
}
//@codingStandardsIgnoreEnd
