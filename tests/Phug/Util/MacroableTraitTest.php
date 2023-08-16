<?php

namespace Phug\Test\Util;

use Phug\Util\OptionInterface;
use Phug\Util\Partial\MacroableTrait;
use Phug\Util\TestCase;

//@codingStandardsIgnoreStart

class MacroableTestClass
{
    use MacroableTrait;

    public function getTwo()
    {
        return 2;
    }
}

class MacroableOptionTestClass implements OptionInterface
{
    use MacroableTrait;

    private $options = [];

    public function getTwo()
    {
        return 2;
    }

    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    public function getOption($name)
    {
        return $this->options[$name];
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    public function setOptionsRecursive($options)
    {
    }

    public function setOptionsDefaults($options)
    {
    }

    public function unsetOption($name)
    {
    }
}

class MacroableTraitTest extends TestCase
{
    /**
     * @covers \Phug\Util\Partial\MacroableTrait
     * @covers \Phug\Util\Partial\MacroableTrait::macro
     * @covers \Phug\Util\Partial\MacroableTrait::hasMacro
     * @covers \Phug\Util\Partial\MacroableTrait::__call
     * @covers \Phug\Util\Partial\MacroableTrait::__callStatic
     */
    public function testMacro()
    {
        MacroableTestClass::macro('multiple', function ($factor) {
            /* @var MacroableTestClass $this */

            return $factor * $this->getTwo();
        });
        $inst = new MacroableTestClass();

        self::assertSame(6, $inst->multiple(3));

        MacroableTestClass::macro('lower', 'strtolower');

        self::assertSame('abc', $inst->lower('ABC'));
        self::assertSame('abc', MacroableTestClass::lower('ABC'));

        MacroableTestClass::macro('sayBye', function () {
            return 'Bye from '.static::class;
        });

        self::assertSame('Bye from Phug\Test\Util\MacroableTestClass', MacroableTestClass::sayBye());
    }

    /**
     * @covers \Phug\Util\Partial\MacroableTrait
     * @covers \Phug\Util\Partial\MacroableTrait::macro
     * @covers \Phug\Util\Partial\MacroableTrait::hasMacro
     * @covers \Phug\Util\Partial\MacroableTrait::__call
     * @covers \Phug\Util\Partial\MacroableTrait::__callStatic
     */
    public function testMacrosOption()
    {
        $inst = new MacroableOptionTestClass();
        $inst->setOption('macros', [
            'divide' => function ($divider) {
                return $this->getTwo() / $divider;
            },
        ]);

        self::assertSame(0.5, $inst->divide(4));

        $inst->setOption('macros', [
            'upper' => 'strtoupper',
        ]);

        self::assertSame('ABC', $inst->upper('abc'));
    }

    /**
     * @covers                   \Phug\Util\Partial\MacroableTrait::__call
     *
     * @expectedException        \BadMethodCallException
     *
     * @expectedExceptionMessage Method fooBar does not exist.
     */
    public function testMacroCallBadMethod()
    {
        $inst = new MacroableTestClass();
        $inst->fooBar();
    }

    /**
     * @covers                   \Phug\Util\Partial\MacroableTrait::__callStatic
     *
     * @expectedException        \BadMethodCallException
     *
     * @expectedExceptionMessage Method fooBar does not exist.
     */
    public function testMacroCallStaticBadMethod()
    {
        MacroableOptionTestClass::fooBar();
    }
}
//@codingStandardsIgnoreEnd
