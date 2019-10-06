<?php

namespace Phug\Test\Util;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Phug\Util\ModuleInterface;
use RuntimeException;
use stdClass;

//@codingStandardsIgnoreStart

/**
 * @coversDefaultClass Phug\Util\Partial\ModuleContainerTrait
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
     */
    public function testInvalidModuleClassName()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Passed module class name stdClass needs to be a class extending Phug\Util\ModuleInterface and/or Phug\Util\ModuleInterface');

        require_once __DIR__.'/MockModuleContainer.php';

        $container = new MockModuleContainer();
        self::assertSame(ModuleInterface::class, $container->getModuleBaseClassName());
        $container->addModule(stdClass::class);
    }

    /**
     * @covers ::getModuleBaseClassName
     * @covers ::addModule
     */
    public function testDoubleRegistration()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Module Phug\Test\Util\FirstTestModule is already registered.');

        require_once __DIR__.'/MockModuleContainer.php';

        $container = new MockModuleContainer();
        self::assertSame(ModuleInterface::class, $container->getModuleBaseClassName());
        $container->addModule(FirstTestModule::class);
        $container->addModule(FirstTestModule::class);
    }

    /**
     * @covers ::getModuleBaseClassName
     * @covers ::addModule
     */
    public function testInstanceDoubleRegistration()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('This occurrence of Phug\Test\Util\FirstTestModule is already registered.');

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
     */
    public function testInstanceDivergentRegistrations()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('This occurrence of Phug\Test\Util\FirstTestModule is already registered in another module container.');

        require_once __DIR__.'/MockModuleContainer.php';

        $container1 = new MockModuleContainer();
        $container2 = new MockModuleContainer();
        $first = new FirstTestModule($container1);
        $container2->addModule($first);
    }

    /**
     * @covers ::getModuleBaseClassName
     * @covers ::removeModule
     */
    public function testRemovalOfNonExistentModule()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('The container doesn\'t contain a Phug\Test\Util\FirstTestModule module');

        require_once __DIR__.'/MockModuleContainer.php';

        $container = new MockModuleContainer();
        self::assertSame(ModuleInterface::class, $container->getModuleBaseClassName());
        self::assertFalse($container->hasModule(FirstTestModule::class));
        $container->removeModule(FirstTestModule::class);
    }

    /**
     * @covers ::getModuleBaseClassName
     * @covers ::removeModule
     */
    public function testRemovalOfNonExistentModuleInstance()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('This occurrence of Phug\Test\Util\FirstTestModule is not registered.');

        require_once __DIR__.'/MockModuleContainer.php';

        $container = new MockModuleContainer();
        $first = new FirstTestModule($container);
        self::assertSame(ModuleInterface::class, $container->getModuleBaseClassName());
        self::assertFalse($container->hasModule($first));
        $container->removeModule($first);
    }

    /**
     * @covers ::addModule
     */
    public function testNonInterfacedContainer()
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Current module container uses the ModuleContainerTrait, but doesn\'t implement Phug\Util\ModuleContainerInterface, please implement it.');

        require_once __DIR__.'/MockModuleContainer.php';

        $container = new MockModuleContainerWithoutInterface();
        $container->addModule(FirstTestModule::class);
    }
}
//@codingStandardsIgnoreEnd
