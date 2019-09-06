<?php

namespace Phug\Test\Util;

use PHPUnit\Framework\TestCase;
use Phug\Util\DocumentLocationInterface;
use Phug\Util\Exception\LocatedException;
use Phug\Util\OptionInterface;
use Phug\Util\Partial;
use Phug\Util\ScopeInterface;
use Phug\Util\SourceLocation;
use stdClass;

//@codingStandardsIgnoreStart
/**
 * Class TestClass.
 */
class TestClass implements DocumentLocationInterface, OptionInterface, ScopeInterface
{
    use Partial\AssignmentTrait;
    use Partial\AttributeTrait;
    use Partial\BlockTrait;
    use Partial\CheckTrait;
    use Partial\DocumentLocationTrait;
    use Partial\EscapeTrait;
    use Partial\FilterTrait;
    use Partial\LevelTrait;
    use Partial\ModeTrait;
    use Partial\NameTrait;
    use Partial\OptionTrait;
    use Partial\PairTrait;
    use Partial\PathTrait;
    use Partial\RestTrait;
    use Partial\ScopeTrait;
    use Partial\SubjectTrait;
    use Partial\ValueTrait;
    use Partial\VariadicTrait;
    use Partial\VisibleTrait;

    /**
     * @param int $line
     *
     * @return $this
     */
    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }
}

/**
 * Class PartialTest.
 */
class PartialTest extends TestCase
{
    /**
     * @covers \Phug\Util\Partial\AssignmentTrait
     * @covers \Phug\Util\Partial\AssignmentTrait::getAssignments
     */
    public function testAssignmentTrait()
    {
        $inst = new TestClass();
        self::assertInstanceOf(\SplObjectStorage::class, $inst->getAssignments());

        $someObj = new stdClass();
        $inst->getAssignments()->attach($someObj);

        self::assertTrue($inst->getAssignments()->contains($someObj));
        self::assertSame(1, $inst->getAssignments()->count());
    }

    /**
     * @covers \Phug\Util\Partial\AttributeTrait
     * @covers \Phug\Util\Partial\AttributeTrait::getAttributes
     */
    public function testAttributeTrait()
    {
        $inst = new TestClass();
        self::assertInstanceOf(\SplObjectStorage::class, $inst->getAttributes());

        $someObj = new stdClass();
        $inst->getAttributes()->attach($someObj);

        self::assertTrue($inst->getAttributes()->contains($someObj));
        self::assertSame(1, $inst->getAttributes()->count());
    }

    /**
     * @covers \Phug\Util\Partial\BlockTrait
     * @covers \Phug\Util\Partial\BlockTrait::isBlock
     * @covers \Phug\Util\Partial\BlockTrait::setIsBlock
     */
    public function testBlockTrait()
    {
        $inst = new TestClass();
        self::assertFalse($inst->isBlock(), 'after ctor');

        self::assertSame($inst, $inst->setIsBlock(true));
        self::assertTrue($inst->isBlock(), 'setIsBlock(true)');

        self::assertSame($inst, $inst->setIsBlock(false));
        self::assertFalse($inst->isBlock(), 'setIsBlock(false)');
    }

    /**
     * @covers \Phug\Util\Partial\CheckTrait
     * @covers \Phug\Util\Partial\CheckTrait::isChecked
     * @covers \Phug\Util\Partial\CheckTrait::setIsChecked
     * @covers \Phug\Util\Partial\CheckTrait::check
     * @covers \Phug\Util\Partial\CheckTrait::uncheck
     */
    public function testCheckTrait()
    {
        $inst = new TestClass();
        self::assertTrue($inst->isChecked());

        self::assertSame($inst, $inst->setIsChecked(false));
        self::assertFalse($inst->isChecked(), 'setIsChecked(false)');

        self::assertSame($inst, $inst->setIsChecked(true));
        self::assertTrue($inst->isChecked(), 'setIsChecked(true)');

        self::assertSame($inst, $inst->uncheck());
        self::assertFalse($inst->isChecked(), 'uncheck');

        self::assertSame($inst, $inst->check());
        self::assertTrue($inst->isChecked(), 'check');
    }

    /**
     * @covers \Phug\Util\Partial\RestTrait
     * @covers \Phug\Util\Partial\RestTrait::isRest
     * @covers \Phug\Util\Partial\RestTrait::setIsRest
     */
    public function testRestTrait()
    {
        $inst = new TestClass();
        self::assertFalse($inst->isRest());

        self::assertSame($inst, $inst->setIsRest(true));
        self::assertTrue($inst->isRest(), 'setIsRest(true)');

        self::assertSame($inst, $inst->setIsRest(false));
        self::assertFalse($inst->isRest(), 'setIsRest(false)');
    }

    /**
     * @covers \Phug\Util\Partial\DocumentLocationTrait
     * @covers \Phug\Util\Partial\LineGetTrait
     * @covers \Phug\Util\Partial\LineGetTrait::getLine
     * @covers \Phug\Util\Partial\OffsetGetTrait
     * @covers \Phug\Util\Partial\OffsetGetTrait::getOffset
     */
    public function testDocumentLocationTrait()
    {
        $inst = new TestClass();
        self::assertNull($inst->getLine());
        self::assertNull($inst->getOffset());

        self::assertSame($inst, $inst->setLine(15));
        self::assertSame(15, $inst->getLine(), 'getLine()');

        self::assertSame($inst, $inst->setOffset(23));
        self::assertSame(23, $inst->getOffset(), 'getOffset()');
    }

    /**
     * @covers \Phug\Util\Partial\LevelTrait
     * @covers \Phug\Util\Partial\LevelTrait::setLevel
     * @covers \Phug\Util\Partial\LevelGetTrait
     * @covers \Phug\Util\Partial\LevelGetTrait::getLevel
     */
    public function testLevelTrait()
    {
        $inst = new TestClass();
        self::assertSame(0, $inst->getLevel());

        self::assertSame($inst, $inst->setLevel(101));
        self::assertSame(101, $inst->getLevel());
    }

    /**
     * @covers \Phug\Util\Partial\EscapeTrait
     * @covers \Phug\Util\Partial\EscapeTrait::isEscaped
     * @covers \Phug\Util\Partial\EscapeTrait::setIsEscaped
     * @covers \Phug\Util\Partial\EscapeTrait::escape
     * @covers \Phug\Util\Partial\EscapeTrait::unescape
     */
    public function testEscapeTrait()
    {
        $inst = new TestClass();
        self::assertFalse($inst->isEscaped());

        self::assertSame($inst, $inst->setIsEscaped(true));
        self::assertTrue($inst->isEscaped(), 'setIsEscaped(true)');

        self::assertSame($inst, $inst->setIsEscaped(false));
        self::assertFalse($inst->isEscaped(), 'setIsEscaped(false)');

        self::assertSame($inst, $inst->escape());
        self::assertTrue($inst->isEscaped(), 'escape');

        self::assertSame($inst, $inst->unescape());
        self::assertFalse($inst->isEscaped(), 'unescape');
    }

    /**
     * @covers \Phug\Util\Partial\FilterTrait
     * @covers \Phug\Util\Partial\FilterTrait::setFilter
     * @covers \Phug\Util\Partial\FilterTrait::getFilter
     */
    public function testFilterTrait()
    {
        $inst = new TestClass();
        self::assertNull($inst->getFilter());

        self::assertSame($inst, $inst->setFilter('test filter'));
        self::assertSame('test filter', $inst->getFilter());
    }

    /**
     * @covers \Phug\Util\Partial\ModeTrait
     * @covers \Phug\Util\Partial\ModeTrait::setMode
     * @covers \Phug\Util\Partial\ModeTrait::getMode
     */
    public function testModeTrait()
    {
        $inst = new TestClass();
        self::assertNull($inst->getMode());

        self::assertSame($inst, $inst->setMode('test mode'));
        self::assertSame('test mode', $inst->getMode());
    }

    /**
     * @covers \Phug\Util\Partial\NameTrait
     * @covers \Phug\Util\Partial\NameTrait::setName
     * @covers \Phug\Util\Partial\NameTrait::getName
     */
    public function testNameTrait()
    {
        $inst = new TestClass();
        self::assertNull($inst->getName());

        self::assertSame($inst, $inst->setName('test name'));
        self::assertSame('test name', $inst->getName());
    }

    /**
     * @covers \Phug\Util\Partial\PairTrait
     * @covers \Phug\Util\Partial\PairTrait::setKey
     * @covers \Phug\Util\Partial\PairTrait::getKey
     * @covers \Phug\Util\Partial\PairTrait::setItem
     * @covers \Phug\Util\Partial\PairTrait::getItem
     */
    public function testPairTrait()
    {
        $inst = new TestClass();
        self::assertNull($inst->getKey());
        self::assertNull($inst->getValue());

        self::assertSame($inst, $inst->setKey('test key'));
        self::assertSame('test key', $inst->getKey());

        self::assertSame($inst, $inst->setItem('test item'));
        self::assertSame('test item', $inst->getItem());
    }

    /**
     * @covers \Phug\Util\Partial\PathTrait
     * @covers \Phug\Util\Partial\PathTrait::setPath
     * @covers \Phug\Util\Partial\PathTrait::getPath
     */
    public function testPathTrait()
    {
        $inst = new TestClass();
        self::assertNull($inst->getPath());

        self::assertSame($inst, $inst->setPath('test path'));
        self::assertSame('test path', $inst->getPath());
    }

    /**
     * @covers \Phug\Util\Partial\SubjectTrait
     * @covers \Phug\Util\Partial\SubjectTrait::setSubject
     * @covers \Phug\Util\Partial\SubjectTrait::getSubject
     */
    public function testSubjectTrait()
    {
        $inst = new TestClass();
        self::assertNull($inst->getSubject());

        self::assertSame($inst, $inst->setSubject('test subject'));
        self::assertSame('test subject', $inst->getSubject());
    }

    /**
     * @covers \Phug\Util\Partial\ValueTrait
     * @covers \Phug\Util\Partial\ValueTrait::setValue
     * @covers \Phug\Util\Partial\ValueTrait::getValue
     * @covers \Phug\Util\Partial\StaticMemberTrait
     * @covers \Phug\Util\Partial\StaticMemberTrait::hasStaticMember
     */
    public function testValueTrait()
    {
        $inst = new TestClass();
        self::assertNull($inst->getValue());

        self::assertSame($inst, $inst->setValue('test value'));
        self::assertSame('test value', $inst->getValue());

        self::assertFalse($inst->hasStaticValue());
        self::assertSame($inst, $inst->setValue('$foo'));
        self::assertFalse($inst->hasStaticValue());
        self::assertSame($inst, $inst->setValue([]));
        self::assertFalse($inst->hasStaticValue());

        self::assertSame($inst, $inst->setValue('0x54'));
        self::assertTrue($inst->hasStaticValue());
        self::assertSame($inst, $inst->setValue('"foo"'));
        self::assertTrue($inst->hasStaticValue());

        self::assertSame($inst, $inst->setName('"foo"'));
        self::assertTrue($inst->hasStaticMember('name'));
        self::assertSame($inst, $inst->setName('"$foo"'));
        self::assertFalse($inst->hasStaticMember('name'));
    }

    /**
     * @covers \Phug\Util\Partial\VisibleTrait
     * @covers \Phug\Util\Partial\VisibleTrait::isVisible
     * @covers \Phug\Util\Partial\VisibleTrait::setIsVisible
     * @covers \Phug\Util\Partial\VisibleTrait::hide
     * @covers \Phug\Util\Partial\VisibleTrait::show
     */
    public function testVisibleTrait()
    {
        $inst = new TestClass();
        self::assertTrue($inst->isVisible());

        self::assertSame($inst, $inst->setIsVisible(false));
        self::assertFalse($inst->isVisible(), 'setIsVisible(false)');

        self::assertSame($inst, $inst->setIsVisible(true));
        self::assertTrue($inst->isVisible(), 'setIsVisible(true)');

        self::assertSame($inst, $inst->hide());
        self::assertFalse($inst->isVisible(), 'hide');

        self::assertSame($inst, $inst->show());
        self::assertTrue($inst->isVisible(), 'show');
    }

    /**
     * @covers \Phug\Util\Partial\VariadicTrait
     * @covers \Phug\Util\Partial\VariadicTrait::isVariadic
     * @covers \Phug\Util\Partial\VariadicTrait::setIsVariadic
     */
    public function testVariadicTrait()
    {
        $inst = new TestClass();
        self::assertFalse($inst->isVariadic());

        self::assertSame($inst, $inst->setIsVariadic(true));
        self::assertTrue($inst->isVariadic(), 'setIsVariadic(true)');

        self::assertSame($inst, $inst->setIsVariadic(false));
        self::assertFalse($inst->isVariadic(), 'setIsVariadic(false)');
    }

    /**
     * @covers \Phug\Util\Partial\OptionTrait
     * @covers \Phug\Util\Partial\OptionTrait::setOptionArrays
     * @covers \Phug\Util\Partial\OptionTrait::handleOptionName
     * @covers \Phug\Util\Partial\OptionTrait::addOptionNameHandlers
     * @covers \Phug\Util\Partial\OptionTrait::setDefaultOption
     * @covers \Phug\Util\Partial\OptionTrait::setOptionsDefaults
     * @covers \Phug\Util\Partial\OptionTrait::filterTraversable
     * @covers \Phug\Util\Partial\OptionTrait::getOptions
     * @covers \Phug\Util\Partial\OptionTrait::setOptions
     * @covers \Phug\Util\Partial\OptionTrait::setOptionsRecursive
     * @covers \Phug\Util\Partial\OptionTrait::getOption
     * @covers \Phug\Util\Partial\OptionTrait::setOption
     * @covers \Phug\Util\Partial\OptionTrait::hasOption
     * @covers \Phug\Util\Partial\OptionTrait::unsetOption
     * @covers \Phug\Util\Partial\OptionTrait::resetOptions
     */
    public function testOptionTraitAndInterface()
    {
        $inst = new TestClass();
        self::assertInstanceOf(\ArrayObject::class, $inst->getOptions());
        self::assertCount(0, $inst->getOptions());

        $options = [
            'a' => 1,
            'b' => [
                'c' => 2,
                'd' => 3,
            ],
        ];

        $flatOptions = [
            'b' => 2,
        ];

        $anotherFlatOptions = [
            'a' => 3,
        ];

        $deepOptions = [
            'b' => [
                'c' => 3,
                'e' => 4,
            ],
        ];

        $anotherDeepOptions = [
            'b' => [
                'e' => 5,
                'f' => 6,
            ],
        ];

        self::assertSame($inst, $inst->setOptions($options));
        self::assertSame($options, (array) $inst->getOptions(), '$options === $inst->getOptions()');
        self::assertTrue($inst->hasOption('b'), '$inst->hasOption(b)');
        self::assertTrue(isset($inst->getOption('b')['c']), '$inst->hasOption([b, c])');
        self::assertFalse($inst->hasOption('unknown'), '$inst->hasOption(unknown)');
        self::assertFalse($inst->hasOption(['unknown', 'unknown']), '$inst->hasOption([unknown, unknown])');
        self::assertFalse(isset($inst->getOption('b')['unknown']), '$inst->hasOption([b, unknown])');
        self::assertSame(['c' => 2, 'd' => 3], $inst->getOption('b'), '$options[b] === $inst->getOption(b)');

        $cloned = clone $inst;
        $cloned->setOptions($flatOptions);
        self::assertSame(2, $cloned->getOption('b'), '$cloned->getOption(b) === 2');

        $cloned->setOptions([], null, $anotherFlatOptions);
        self::assertSame(3, $cloned->getOption('a'), '$cloned->getOption(a) === 3 (thrid argument)');

        self::assertSame($inst, $inst->setOptionsRecursive($options, $deepOptions));
        self::assertSame(['c' => 3, 'd' => 3, 'e' => 4], $inst->getOption('b'), '$inst->getOption(b) (deep)');

        $inst->setOptionsRecursive([], $anotherDeepOptions);
        self::assertSame(5, $inst->getOption('b')['e'], '$inst->getOption(b)[e] === 5 (second argument)');
        self::assertSame(6, $inst->getOption('b')['f'], '$inst->getOption(b)[f] === 6 (second argument)');

        $inst->setOption('b', 5);
        self::assertSame(5, $inst->getOption('b'), '$inst->getOption(b) === 5');

        $inst->setOption(['foo', 'bar'], 3);
        $inst->setOption(['foo', 'baz'], 6);
        self::assertSame(6, $inst->getOption(['foo', 'baz']), '$inst->getOption(foo.baz) === 6');
        $inst->setOption(['foo', 'baz'], 8);
        self::assertSame(8, $inst->getOption(['foo', 'baz']), '$inst->getOption(foo.baz) === 8');
        self::assertSame(8, $inst->getOption('foo.baz'), '$inst->getOption(foo.baz) === 8');
        $inst->setOption('foo.baz', 'r');
        self::assertSame('r', $inst->getOption(['foo', 'baz']), '$inst->getOption(foo.baz) === r');
        self::assertSame('r', $inst->getOption('foo.baz'), '$inst->getOption(foo.baz) === r');
        $inst->unsetOption(['foo', 'baz']);
        self::assertFalse($inst->hasOption(['foo', 'baz']), '$inst->hasOption(foo.bar) === false');
        $inst->setOption('foo_bar', 'a');
        self::assertSame('a', $inst->getOption('foo_bar'), '$inst->getOption(foo_bar) === a');
        self::assertFalse($inst->hasOption('fooBar'), '$inst->hasOption(fooBar) === false');
        $inst->addOptionNameHandlers(function ($name) {
            return preg_replace_callback('/[A-Z]/', function ($matches) {
                return '_'.strtolower($matches[0]);
            }, $name);
        });
        self::assertSame('a', $inst->getOption('fooBar'), '$inst->getOption(fooBar) === a');
        $inst->setOption('fooBar', 'b');
        self::assertSame('b', $inst->getOption('foo_bar'), '$inst->getOption(foo_bar) === b');

        self::assertSame($inst, $inst->setOptionsDefaults([
            'new_option' => 3,
        ]));
        self::assertSame(3, $inst->getOption('new_option'), '$inst->getOption(new_option) === 3');
        $inst->setOptionsDefaults([
            'new_option' => 79,
        ]);
        self::assertSame(3, $inst->getOption('new_option'), '$inst->getOption(new_option) === 3');

        $inst->resetOptions();
        self::assertFalse($inst->hasOption('foo.baz'));
        self::assertFalse($inst->hasOption('new_option'));

        $ref = new \ArrayObject([
            'foo' => 'bar',
        ]);
        $inst->resetOptions();
        $inst->setOptionsDefaults($ref, [
            'bar' => 'baz',
            'foo' => 'baz',
        ]);
        self::assertSame('bar', $inst->getOption('foo'));
        self::assertSame('baz', $inst->getOption('bar'));
        self::assertSame('bar', $ref['foo']);
        self::assertSame('baz', $ref['bar']);

        $ref = new \ArrayObject([
            'foo' => [
                'bar' => 'baz',
            ],
        ]);
        $inst->resetOptions();
        $inst->setOptionsDefaults($ref, [
            'foo' => [
                'baz' => 'bar',
            ],
        ]);
        self::assertSame('bar', $inst->getOption('foo.baz'));
        self::assertSame('baz', $inst->getOption('foo.bar'));

        $ref = new \ArrayObject([
            'foo' => 'bar',
        ]);
        $inst->resetOptions();
        $inst->setOptionsRecursive($ref, [
            'bar' => 'baz',
            'foo' => 'baz',
        ]);
        self::assertSame('baz', $inst->getOption('foo'));
        self::assertSame('baz', $inst->getOption('bar'));
        self::assertSame('baz', $ref['foo']);
        self::assertSame('baz', $ref['bar']);
    }

    /**
     * @covers \Phug\Util\Partial\ScopeTrait
     * @covers \Phug\Util\Partial\ScopeTrait::setScope
     * @covers \Phug\Util\Partial\ScopeTrait::getScopeId
     */
    public function testScopeTrait()
    {
        $inst = new TestClass();
        self::assertNull($inst->getScopeId());

        $foo = new stdClass();
        self::assertSame($inst, $inst->setScope($foo));
        self::assertSame(spl_object_hash($foo), $inst->getScopeId());

        self::assertSame($inst, $inst->setScope(null));
        self::assertNull($inst->getScopeId());
    }

    /**
     * @covers \Phug\Util\SourceLocation
     * @covers \Phug\Util\SourceLocation::getPath
     * @covers \Phug\Util\SourceLocation::getLine
     * @covers \Phug\Util\SourceLocation::getOffset
     * @covers \Phug\Util\Partial\SourceLocationTrait
     * @covers \Phug\Util\Partial\SourceLocationTrait::getPath
     * @covers \Phug\Util\Partial\SourceLocationTrait::getLine
     * @covers \Phug\Util\Partial\SourceLocationTrait::getOffset
     * @covers \Phug\Util\Partial\SourceLocationTrait::getOffsetLength
     * @covers \Phug\Util\Partial\SourceLocationTrait::setOffsetLength
     * @covers \Phug\Util\Exception\LocatedException
     * @covers \Phug\Util\Exception\LocatedException::getLocation
     */
    public function testSourceLocationTrait()
    {
        $inst = new SourceLocation('foo.pug', 2, 15);

        self::assertSame('foo.pug', $inst->getPath());
        self::assertSame(2, $inst->getLine());
        self::assertSame(15, $inst->getOffset());

        $inst = new SourceLocation('foo.pug', 2, 15, 0);

        self::assertSame(0, $inst->getOffsetLength());

        self::assertSame($inst, $inst->setOffsetLength(9));

        self::assertSame(9, $inst->getOffsetLength());

        $exception = new LocatedException($inst);

        self::assertSame(9, $exception->getLocation()->getOffsetLength());
        self::assertSame(0, $exception->getCode());
        self::assertSame('', $exception->getMessage());
    }
}
//@codingStandardsIgnoreEnd
