<?php

namespace Phug\Test\Util;

use InvalidArgumentException;
use Phug\Util\ModuleInterface;
use Phug\Util\TestCase;
use stdClass;

//@codingStandardsIgnoreStart

/**
 * @coversDefaultClass \Phug\Util\Partial\ModuleContainerTrait
 */
class ModuleContainerTest extends TestCase
{
    /**
     * @covers ::getModuleBaseClassName
     * @covers ::addModule
     * @covers ::addModules
     * @covers ::hasModule
     * @covers ::getModule
     * @covers ::getModules
     * @covers ::getStaticModules
     * @covers ::removeModule
     */
    public function testHasGetSetRemoveModule()
    {
        require_once __DIR__.'/MockModuleContainer.php';

        $container = new MockModuleContainer();

        self::assertSame(ModuleInterface::class, $container->getModuleBaseClassName());

        self::assertFalse($container->hasModule(FirstTestModule::class));
        self::assertFalse($container->hasModule(SecondTestModule::class));

        self::assertSame($container, $container->addModule(FirstTestModule::class));

        self::assertTrue($container->hasModule(FirstTestModule::class));
        self::assertInstanceOf(FirstTestModule::class, $container->getModule(FirstTestModule::class));
        self::assertFalse($container->hasModule(SecondTestModule::class));

        self::assertSame($container, $container->removeModule(FirstTestModule::class));

        self::assertFalse($container->hasModule(FirstTestModule::class));
        self::assertFalse($container->hasModule(SecondTestModule::class));

        $staticModules = [
            FirstTestModule::class,
            SecondTestModule::class,
        ];
        self::assertSame($container, $container->addModules($staticModules));
        self::assertSame($staticModules, $container->getStaticModules());

        self::assertTrue($container->hasModule(FirstTestModule::class));
        self::assertInstanceOf(FirstTestModule::class, $container->getModule(FirstTestModule::class));

        self::assertTrue($container->hasModule(SecondTestModule::class));
        self::assertInstanceOf(SecondTestModule::class, $container->getModule(SecondTestModule::class));

        $modules = $container->getModules();
        self::assertCount(2, $modules);
        self::assertInstanceOf(FirstTestModule::class, $modules[0]);
        self::assertInstanceOf(SecondTestModule::class, $modules[1]);

        $container = new MockModuleContainer();
        $first = new FirstTestModule($container);
        $second = new SecondTestModule($container);

        self::assertSame(ModuleInterface::class, $container->getModuleBaseClassName());

        self::assertFalse($container->hasModule($first));
        self::assertFalse($container->hasModule($second));

        self::assertSame($container, $container->addModule($first));

        self::assertTrue($container->hasModule($first));
        self::assertInstanceOf(FirstTestModule::class, $container->getModule($first));
        self::assertFalse($container->hasModule($second));

        self::assertSame($container, $container->removeModule($first));

        self::assertFalse($container->hasModule($first));
        self::assertFalse($container->hasModule($second));

        self::assertSame($container, $container->addModules([
            $first,
            $second,
            new FirstTestModule($container), // should not produce error
        ]));

        self::assertTrue($container->hasModule($first));
        self::assertInstanceOf(FirstTestModule::class, $container->getModule($first));

        self::assertTrue($container->hasModule($second));
        self::assertInstanceOf(SecondTestModule::class, $container->getModule($second));
    }

    /**
     * @covers ::getModuleBaseClassName
     * @covers ::addModule
     *
     * @expectedException InvalidArgumentException
     *
     * @expectedExceptionMessage Passed module class name stdClass needs to be a class extending Phug\Util\ModuleInterface and/or Phug\Util\ModuleInterface
     */
    public function testInvalidModuleClassName()
    {
        require_once __DIR__.'/MockModuleContainer.php';

        $container = new MockModuleContainer();
        self::assertSame(ModuleInterface::class, $container->getModuleBaseClassName());
        $container->addModule(stdClass::class);
    }

    /**
     * @covers ::getModuleBaseClassName
     * @covers ::addModule
     *
     * @expectedException InvalidArgumentException
     *
     * @expectedExceptionMessage Module Phug\Test\Util\FirstTestModule is already registered.
     */
    public function testDoubleRegistration()
    {
        require_once __DIR__.'/MockModuleContainer.php';

        $container = new MockModuleContainer();
        self::assertSame(ModuleInterface::class, $container->getModuleBaseClassName());
        $container->addModule(FirstTestModule::class);
        $container->addModule(FirstTestModule::class);
    }

    /**
     * @covers ::getModuleBaseClassName
     * @covers ::addModule
     *
     * @expectedException InvalidArgumentException
     *
     * @expectedExceptionMessage This occurrence of Phug\Test\Util\FirstTestModule is already registered.
     */
    public function testInstanceDoubleRegistration()
    {
        require_once __DIR__.'/MockModuleContainer.php';

        $container = new MockModuleContainer();
        self::assertSame(ModuleInterface::class, $container->getModuleBaseClassName());
        $first = new FirstTestModule($container);
        $container->addModule($first);
        $container->addModule($first);
    }

    /**
     * @covers ::getModuleBaseClassName
     * @covers ::addModule
     *
     * @expectedException InvalidArgumentException
     *
     * @expectedExceptionMessage This occurrence of Phug\Test\Util\FirstTestModule is already registered in another module container.
     */
    public function testInstanceDivergentRegistrations()
    {
        require_once __DIR__.'/MockModuleContainer.php';

        $container1 = new MockModuleContainer();
        $container2 = new MockModuleContainer();
        $first = new FirstTestModule($container1);
        $container2->addModule($first);
    }

    /**
     * @covers ::getModuleBaseClassName
     * @covers ::removeModule
     *
     * @expectedException InvalidArgumentException
     *
     * @expectedExceptionMessage The container doesn't contain a Phug\Test\Util\FirstTestModule module
     */
    public function testRemovalOfNonExistentModule()
    {
        require_once __DIR__.'/MockModuleContainer.php';

        $container = new MockModuleContainer();
        self::assertSame(ModuleInterface::class, $container->getModuleBaseClassName());
        self::assertFalse($container->hasModule(FirstTestModule::class));
        $container->removeModule(FirstTestModule::class);
    }

    /**
     * @covers ::getModuleBaseClassName
     * @covers ::removeModule
     *
     * @expectedException InvalidArgumentException
     *
     * @expectedExceptionMessage This occurrence of Phug\Test\Util\FirstTestModule is not registered.
     */
    public function testRemovalOfNonExistentModuleInstance()
    {
        require_once __DIR__.'/MockModuleContainer.php';

        $container = new MockModuleContainer();
        $first = new FirstTestModule($container);
        self::assertSame(ModuleInterface::class, $container->getModuleBaseClassName());
        self::assertFalse($container->hasModule($first));
        $container->removeModule($first);
    }

    /**
     * @covers ::addModule
     *
     * @expectedException \RuntimeException
     *
     * @expectedExceptionMessage Current module container uses the ModuleContainerTrait, but doesn't implement Phug\Util\ModuleContainerInterface, please implement it.
     */
    public function testNonInterfacedContainer()
    {
        require_once __DIR__.'/MockModuleContainer.php';

        $container = new MockModuleContainerWithoutInterface();
        $container->addModule(FirstTestModule::class);
    }
}
//@codingStandardsIgnoreEnd
