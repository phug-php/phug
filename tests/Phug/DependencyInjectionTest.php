<?php

namespace Phug\Test;

use Phug\DependencyInjection;
use Phug\Util\UnorderedArguments;

class DependencyInjectionTest extends AbstractDependencyInjectionTest
{
    /**
     * @covers \Phug\DependencyInjection::<public>
     * @covers \Phug\DependencyInjection\Dependency::<public>
     * @covers \Phug\DependencyInjection\Requirement::<public>
     */
    public function testGetProvider()
    {
        $injector = new DependencyInjection();
        $injector->register('foo', function ($value) {
            return strtoupper($value);
        });
        $injector->provider('bar', ['foo', function ($foo) {
            return function ($start, $end) use ($foo) {
                return $foo($start).$end;
            };
        }]);

        self::assertSame('ABcd', $injector->call('bar', 'ab', 'cd'));
    }

    /**
     * @covers \Phug\DependencyInjection::<public>
     * @covers \Phug\DependencyInjection\Dependency::<public>
     * @covers \Phug\DependencyInjection\Requirement::<public>
     */
    public function testProvider()
    {
        $injector = new DependencyInjection();
        $injector->provider('escape', 'htmlspecialchars');
        $injector->register('upper', 'strtoupper');

        self::assertSame('&LT;', $injector->call('upper', $injector->call('escape', '<')));
    }

    /**
     * @covers \Phug\DependencyInjection::<public>
     */
    public function testProviderWithReference()
    {
        $injector = new DependencyInjection();
        $text = '<';
        $injector->provider('escape', function () {
            return function (&$ref) {
                $ref = htmlspecialchars($ref);
            };
        });
        $escape = $injector->get('escape');
        $escape($text);

        self::assertSame('&lt;', $text);
    }

    /**
     * @covers \Phug\DependencyInjection::getRequirementsStates
     */
    public function testGetRequirementsStates()
    {
        $injector = new DependencyInjection();

        self::assertSame([], $injector->getRequirementsStates());

        $injector->provider('escape', 'htmlspecialchars');
        $injector->register('upper', 'strtoupper');

        self::assertSame([
            'escape' => false,
            'upper'  => false,
        ], $injector->getRequirementsStates());

        $injector->setAsRequired('escape');

        self::assertSame([
            'escape' => true,
            'upper'  => false,
        ], $injector->getRequirementsStates());
    }

    /**
     * @covers \Phug\DependencyInjection::import
     */
    public function testImport()
    {
        $injector = new DependencyInjection();
        $injector->register('answer', 42);
        self::assertFalse($injector->getProvider('answer')->isRequired());
        self::assertSame(42, $injector->import('answer'));
        self::assertTrue($injector->getProvider('answer')->isRequired());
    }

    /**
     * @covers                   \Phug\DependencyInjection::setAsRequired
     * @expectedException        \Phug\DependencyException
     * @expectedExceptionCode    2
     * @expectedExceptionMessage Dependency not found: baz < bar < foo
     */
    public function testRequiredFailure()
    {
        $injector = new DependencyInjection();
        $injector->provider('bar', ['baz', 1]);
        $injector->provider('foo', ['bar', 2]);
        $injector->setAsRequired('foo');
    }

    /**
     * @covers                   \Phug\DependencyInjection::getProvider
     * @expectedException        \Phug\DependencyException
     * @expectedExceptionCode    1
     * @expectedExceptionMessage foobar dependency not found.
     */
    public function testGetProviderException()
    {
        $injector = new DependencyInjection();
        $injector->getProvider('foobar');
    }

    /**
     * @covers \Phug\DependencyInjection::<public>
     * @covers \Phug\DependencyInjection\FunctionWrapper::<public>
     * @covers \Phug\DependencyInjection\Dependency::<public>
     * @covers \Phug\DependencyInjection\Requirement::<public>
     */
    public function testExport()
    {
        $injector = new DependencyInjection();
        $injector->provider('a', ['b', 'c', function ($b, $c) {
            return function ($n) use ($b, $c) {
                return $n + $b() + $c;
            };
        }]);
        $injector->provider('b', ['d', 'c', function ($d, $c) {
            return function () use ($d, $c) {
                return $d + $c;
            };
        }]);
        $injector->register('c', 1);
        $injector->register('d', 2);

        self::assertSame(7, $injector->call('a', 3));

        $injector->setAsRequired('a');
        $export = $injector->export('module');

        self::assertSameLines([
            '$module = [',
            "  'a' => function (\$n) use (&\$module) {",
            "    \$b = \$module['b'];",
            "    \$c = \$module['c'];",
            '    return $n + $b() + $c;',
            '  },',
            "  'b' => function () use (&\$module) {",
            "    \$d = \$module['d'];",
            "    \$c = \$module['c'];",
            '    return $d + $c;',
            '  },',
            "  'c' => 1,",
            "  'd' => 2,",
            '];',
        ], $export);
        self::assertSame(7, eval($export.'return $module["a"](3);'));

        $injector = new DependencyInjection();
        $injector->provider('a', ['b', 'c', function ($b, $c) {
            return function ($n) use ($b, $c) {
                return $n + $b() + $c;
            };
        }]);
        $injector->provider('b', ['d', 'c', function ($d, $c) {
            return function () use ($d, $c) {
                return $d + $c;
            };
        }]);
        $injector->register('c', 1);
        $injector->register('d', 2);

        self::assertSame(3, $injector->call('b'));

        $injector->setAsRequired('b');
        $export = $injector->export('module');

        self::assertSameLines([
            '$module = [',
            "  'b' => function () use (&\$module) {",
            "    \$d = \$module['d'];",
            "    \$c = \$module['c'];",
            '    return $d + $c;',
            '  },',
            "  'c' => 1,",
            "  'd' => 2,",
            '];',
        ], $export);
        self::assertSame(3, eval($export.'return $module["b"]();'));
    }

    /**
     * @covers \Phug\DependencyInjection::getStorageItem
     * @covers \Phug\DependencyInjection::dumpDependency
     * @covers \Phug\DependencyInjection\FunctionWrapper::<public>
     */
    public function testDumpDependency()
    {
        $injector = new DependencyInjection();
        $injector->provider('a', function () {
            return function (array $array, UnorderedArguments $args) {
                return $args->required($array[0]);
            };
        });
        $injector->setAsRequired('a');
        $export = $injector->export('module');

        self::assertSameLines([
            '$module = [',
            "  'a' => function (array \$array, Phug\\Util\\UnorderedArguments \$args) use (&\$module) {",
            '    return $args->required($array[0]);',
            '  },',
            '];',
        ], $export);
        self::assertTrue(eval($export.'return $module["a"]('.
            '["boolean"], '.
            'new \\Phug\\Util\\UnorderedArguments([true])'.
        ');'));

        $injector = new DependencyInjection();
        $injector->provider('a', function () {
            return function (&$pass = null) {
                $pass = 42;
            };
        });
        $injector->setAsRequired('a');
        $export = $injector->export('module');

        self::assertSameLines([
            '$module = [',
            "  'a' => function (&\$pass = NULL) use (&\$module) {",
            '    $pass = 42;',
            '  },',
            '];',
        ], $export);
        self::assertSame(42, eval($export.'$module["a"]($box); return $box;'));
    }

    /**
     * @covers                   \Phug\DependencyInjection::provider
     * @expectedException        \Phug\DependencyException
     * @expectedExceptionMessage Invalid provider passed to foobar,
     * @expectedExceptionMessage it must be an array or a callable function.
     */
    public function testProviderException()
    {
        $injector = new DependencyInjection();
        $injector->provider('foobar', '-');
    }

    /**
     * @covers \Phug\DependencyInjection::get
     */
    public function testCache()
    {
        $providerCallCount = 0;
        $serviceCallCount = 0;
        $injector = new DependencyInjection();
        $injector->provider('foobar', function () use (&$providerCallCount, &$serviceCallCount) {
            $providerCallCount++;

            return function () use (&$serviceCallCount) {
                $serviceCallCount++;
            };
        });

        self::assertSame(0, $providerCallCount);
        self::assertSame(0, $serviceCallCount);

        $injector->call('foobar');

        self::assertSame(1, $providerCallCount);
        self::assertSame(1, $serviceCallCount);

        $injector->call('foobar');

        self::assertSame(1, $providerCallCount);
        self::assertSame(2, $serviceCallCount);
    }

    public function testAlias()
    {
        $injector = new DependencyInjection();
        $injector->provider('foo-bar::a b', function () {
            return 'foo';
        });
        $injector->provider('a', ['foo-bar::a b', function ($a) {
            return function () use ($a) {
                return $a;
            };
        }]);
        $injector->setAsRequired('a');
        $export = $injector->export('dep');

        self::assertSame('foo', $injector->call('a'));
        self::assertSame('foo', eval($export.'return $dep["a"]();'));

        $injector = new DependencyInjection();
        $injector->register('a', 3);
        $injector->register('b', 7);
        $injector->provider('c', ['a', 'b', function ($b, $a) {
            return function () use ($a, $b) {
                return $a - $b;
            };
        }]);
        $injector->setAsRequired('c');
        $export = $injector->export('dep');

        self::assertSame(4, $injector->call('c'));
        self::assertSame(4, eval($export.'return $dep["c"]();'));
    }

    /**
     * @covers \Phug\DependencyInjection::countRequiredDependencies
     */
    public function testCountRequiredDependencies()
    {
        $injector = new DependencyInjection();
        $injector->provider('a', function () {
            return 'foo';
        });
        $injector->provider('b', function () {
            return 'bar';
        });
        $injector->provider('c', ['b', function ($a) {
            return function () use ($a) {
                return $a;
            };
        }]);

        self::assertSame(0, $injector->countRequiredDependencies());
        $injector->setAsRequired('a');
        self::assertSame(1, $injector->countRequiredDependencies());
        $injector->setAsRequired('c');
        self::assertSame(3, $injector->countRequiredDependencies());
    }

    /**
     * @covers \Phug\DependencyInjection::get
     */
    public function testRecursion()
    {
        $injector = new DependencyInjection();
        $injector->provider('r', ['r', function ($r) {
            return function ($input, $depth = 0) use ($r) {
                if (!is_array($input)) {
                    return $input;
                }

                $result = '';
                foreach ($input as $key => $value) {
                    $result .= "\n+".str_repeat('-', $depth * 2).$key.'='.$r($value, $depth + 1);
                }

                return $result;
            };
        }]);
        $result = ltrim($injector->call('r', [
            'foo' => [
                'bar' => 42,
            ],
        ]));

        self::assertSame("+foo=\n+--bar=42", $result);
    }
}
