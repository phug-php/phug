<?php

namespace Phug\Test\Util;

use PHPUnit\Framework\TestCase;
use Phug\Util\AssociativeStorage;
use Phug\Util\Partial\NameTrait;

//@codingStandardsIgnoreStart
class Entity
{
    use NameTrait;
}
/**
 * Class AssociativeStorageTest.
 *
 * @coversDefaultClass \Phug\Util\AssociativeStorage
 */
class AssociativeStorageTest extends TestCase
{
    /**
     * @covers                   ::<public>
     * @covers                   ::attachStrictMode
     *
     * @expectedException        \InvalidArgumentException
     *
     * @expectedExceptionMessage Duplicate entity for the name foo
     */
    public function testStrictMode()
    {
        $storage = new AssociativeStorage();
        $a = new Entity();
        $a->setName('foo');
        $b = new Entity();
        $b->setName('foo');

        $storage->attach($a);
        $storage->attach($b);
    }

    /**
     * @covers                   ::<public>
     * @covers                   ::attachStrictMode
     *
     * @expectedException        \InvalidArgumentException
     *
     * @expectedExceptionMessage Unknown mode: 99
     */
    public function testWrongMode()
    {
        new AssociativeStorage('foo', 99);
    }

    /**
     * @covers ::<public>
     * @covers ::attachReplaceMode
     * @covers ::attachIgnoreMode
     * @covers ::attachAllMode
     */
    public function testOtherModes()
    {
        $storage = new AssociativeStorage();
        $storage->setMode(AssociativeStorage::REPLACE);
        $a = new Entity();
        $a->setName('foo');
        $b = new Entity();
        $b->setName('foo');

        $storage->attach($a);
        $storage->attach($b);

        self::assertSame(1, iterator_count($storage->findAllByName('foo')));
        self::assertSame($b, $storage->findFirstByName('foo'));

        $storage = new AssociativeStorage('bar', AssociativeStorage::IGNORE);
        $a = new Entity();
        $a->setName('foo');
        $b = new Entity();
        $b->setName('foo');

        $storage->attach($a);
        $storage->attach($b);

        self::assertSame(1, iterator_count($storage->findAllByName('foo')));
        self::assertSame($a, $storage->findFirstByName('foo'));

        $storage = new AssociativeStorage('bar', AssociativeStorage::ALL);
        $a = new Entity();
        $a->setName('foo');
        $b = new Entity();
        $b->setName('foo');

        $storage->attach($a);
        $storage->attach($b);

        self::assertSame(2, iterator_count($storage->findAllByName('foo')));
        self::assertSame($a, $storage->findFirstByName('foo'));
        self::assertnull($storage->findFirstByName('fooBarBiz'));
    }

    /**
     * @covers ::addMode
     */
    public function testCustomMode()
    {
        $storage = new AssociativeStorage();
        $storage->addMode(1001, function (AssociativeStorage $storage, $entity) {
            foreach ($storage->findAllByName($storage->identifyEntity($entity)) as $duplicate) {
                if ($duplicate->priority < $entity->priority) {
                    return false;
                }
                $storage->detach($duplicate);
            }

            return true;
        })->setMode(1001);
        $a = new Entity();
        $a->priority = 20;
        $a->setName('foo');
        $b = new Entity();
        $b->setName('foo');
        $b->priority = 10;
        $c = new Entity();
        $c->setName('foo');
        $c->priority = 30;

        $storage->attach($a);
        $storage->attach($b);
        $storage->attach($c);

        self::assertSame(1, iterator_count($storage->findAllByName('foo')));
        self::assertSame($b, $storage->findFirstByName('foo'));
    }
}
//@codingStandardsIgnoreEnd
