<?php

namespace Phug\Test\Util;

use Phug\EventInterface;
use Phug\Util\AbstractModule;
use Phug\Util\ModuleContainerInterface;
use Phug\Util\ModuleInterface;
use Phug\Util\Partial\ModuleContainerTrait;

//@codingStandardsIgnoreStart
class MockModuleContainer implements ModuleContainerInterface
{
    use ModuleContainerTrait;
}

class SpecificModuleContainer implements ModuleContainerInterface
{
    use ModuleContainerTrait;

    public function getModuleBaseClassName()
    {
        return SpecificModuleInterface::class;
    }

    public function doStuff()
    {
        return $this->trigger('stuff', null, ['payload' => 15]);
    }
}

class MockModuleContainerWithoutInterface
{
    use ModuleContainerTrait;
}

class FirstTestModule extends AbstractModule
{
    public $eventsAttached = false;

    public function attachEvents()
    {
        parent::attachEvents();

        $this->eventsAttached = true;
    }

    public function detachEvents()
    {
        parent::detachEvents();

        $this->eventsAttached = false;
    }
}

class SecondTestModule extends AbstractModule
{
}

interface SpecificModuleInterface extends ModuleInterface
{
}

class FirstSpecificTestModule extends FirstTestModule implements SpecificModuleInterface
{
    public function handleStuff(EventInterface $event)
    {
        return "Payload was {$event->getParam('payload')}";
    }

    public function getEventListeners()
    {
        return [
            'stuff' => [$this, 'handleStuff'],
        ];
    }
}

class SecondSpecificTestModule extends AbstractModule implements SpecificModuleInterface
{
}

//@codingStandardsIgnoreEnd
