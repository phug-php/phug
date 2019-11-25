<?php

namespace Phug\Test\Util;

use PHPUnit\Framework\TestCase;

//@codingStandardsIgnoreStart

/**
 * @coversDefaultClass \Phug\Util\AbstractModule
 */
class ModuleTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getContainer
     * @covers ::getEventListeners
     * @covers ::attachEvents
     * @covers ::detachEvents
     */
    public function testManualModuleInstanciation()
    {
        require_once __DIR__.'/MockModuleContainer.php';

        $container = new MockModuleContainer();
        $module = new FirstTestModule($container);

        self::assertSame($container, $module->getContainer());
        self::assertInternalType('array', $module->getEventListeners());

        self::assertFalse($module->eventsAttached);

        $module->attachEvents();
        self::assertTrue($module->eventsAttached);
        $module->detachEvents();
        self::assertFalse($module->eventsAttached);
    }

    /**
     * @covers ::attachEvents
     * @covers ::detachEvents
     */
    public function testContainerModuleInstanciation()
    {
        require_once __DIR__.'/MockModuleContainer.php';

        $container = new MockModuleContainer();

        /* @var FirstTestModule $module */
        self::assertFalse($container->hasModule(FirstTestModule::class));
        self::assertSame($container, $container->addModule(FirstTestModule::class));
        self::assertTrue($container->hasModule(FirstTestModule::class));
        self::assertInstanceOf(FirstTestModule::class, $module = $container->getModule(FirstTestModule::class));

        self::assertTrue($module->eventsAttached);
        self::assertSame($container, $container->removeModule(FirstTestModule::class));
        self::assertFalse($container->hasModule(FirstTestModule::class));
        self::assertFalse($module->eventsAttached);
    }

    /**
     * @covers ::attachEvents
     * @covers ::detachEvents
     */
    public function testModuleEventHandling()
    {
        require_once __DIR__.'/MockModuleContainer.php';

        $container = new SpecificModuleContainer();

        /* @var FirstSpecificTestModule $module */
        self::assertFalse($container->hasModule(FirstSpecificTestModule::class));
        self::assertSame($container, $container->addModule(FirstSpecificTestModule::class));
        self::assertTrue($container->hasModule(FirstSpecificTestModule::class));
        self::assertInstanceOf(FirstSpecificTestModule::class, $module = $container->getModule(FirstSpecificTestModule::class));

        self::assertTrue($module->eventsAttached);

        //This will trigger the event system, check out SpecificModuleContainer->doStuff and FirstSpecificTestModule->handleStuff
        self::assertSame('Payload was 15', $container->doStuff());

        self::assertSame($container, $container->removeModule(FirstSpecificTestModule::class));
        self::assertFalse($container->hasModule(FirstSpecificTestModule::class));
        self::assertFalse($module->eventsAttached);

        //Default value if no listeners attached is always null
        self::assertNull($container->doStuff());
    }
}
//@codingStandardsIgnoreEnd
