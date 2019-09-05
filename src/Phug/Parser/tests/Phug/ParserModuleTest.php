<?php

namespace Phug\Test;

use Phug\AbstractParserModule;
use Phug\Parser;
use Phug\Parser\Event\NodeEvent;
use Phug\Parser\Event\ParseEvent;
use Phug\Parser\Node\CommentNode;
use Phug\Parser\Node\ConditionalNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\ImportNode;
use Phug\Parser\Node\TextNode;
use Phug\ParserEvent;
use Phug\ParserModuleInterface;

//@codingStandardsIgnoreStart
class ParserTestModule extends AbstractParserModule
{
    public function getEventListeners()
    {
        return [
            ParserEvent::DOCUMENT => function (NodeEvent $event) {
                $event->getNode()->prependChild(new TextNode());
            },
        ];
    }
}

class StateEnterLeaveStoreTestModule extends AbstractParserModule
{
    public function getEventListeners()
    {
        return [
            ParserEvent::STATE_ENTER => function (NodeEvent $event) {
                $node = $event->getNode();
                if ($node instanceof ElementNode && $node->getName() === 'div') {
                    $node->prependChild(new TextNode());
                }
            },
            ParserEvent::STATE_LEAVE => function (NodeEvent $event) {
                $node = $event->getNode();
                if ($node instanceof ElementNode && $node->getName() === 'div') {
                    $node->appendChild(new TextNode());
                }
            },
            ParserEvent::STATE_STORE => function (NodeEvent $event) {
                $node = $event->getNode();
                if ($node instanceof ElementNode && $node->getName() === 'div') {
                    $node->append(new TextNode());
                }
            },
        ];
    }
}

/**
 * @coversDefaultClass Phug\AbstractParserModule
 */
class ParserModuleTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     */
    public function testTokenEvent()
    {
        self::assertNodes('p Test', [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [TextNode]',
        ]);

        $parser = new Parser(['parser_modules' => [ParserTestModule::class]]);

        self::assertNodes('p Test', [
            '[DocumentNode]',
            '  [TextNode]',
            '  [ElementNode]',
            '    [TextNode]',
        ], $parser);
    }

    /**
     * @covers ::<public>
     */
    public function testStateEnterLeaveStoreEvents()
    {
        self::assertNodes("div\n\tp= \$test\na", [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [ElementNode]',
            '      [ExpressionNode]',
            '  [ElementNode]',
        ]);

        $parser = new Parser(['parser_modules' => [StateEnterLeaveStoreTestModule::class]]);

        self::assertNodes("div\n\tp= \$test\na", [
            '[DocumentNode]',
            '  [ElementNode]',
            '    [TextNode]',
            '    [ElementNode]',
            '      [ExpressionNode]',
            '    [TextNode]',
            '  [TextNode]',
            '  [ElementNode]',
        ], $parser);
    }

    /**
     * @group events
     * @covers \Phug\Parser::__construct
     * @covers \Phug\Parser\Event\ParseEvent::<public>
     * @covers \Phug\Parser\Event\NodeEvent::<public>
     */
    public function testOnParse()
    {
        $parser = new Parser([
            'on_parse' => function (ParseEvent $event) {
                $event->setInput(str_replace('replacement', 'test.pug', $event->getInput()));
                $event->setPath(
                    dirname(dirname($event->getPath())).DIRECTORY_SEPARATOR.
                    'utils'.DIRECTORY_SEPARATOR.
                    'base.pug'
                );
            },
        ]);

        $document = $parser->parse('include replacement', __FILE__);
        /* @var ImportNode $include */
        $include = $document->getChildAt(0);
        self::assertSame('test.pug', $include->getPath());
        self::assertFileExists($include->getSourceLocation()->getPath());
        self::assertSame('div Test', trim(file_get_contents($include->getSourceLocation()->getPath())));

        include_once __DIR__.'/TestState.php';

        $parser = new Parser([
            'on_parse' => function (ParseEvent $event) {
                if ($event->getStateClassName() !== TestState::class) {
                    $event->setStateClassName(TestState::class);
                    $event->setStateOptions(array_merge($event->getStateOptions(), [
                        'custom' => 42,
                    ]));
                }
            },
        ]);

        $parser->parse('div');

        self::assertSame(42, TestState::getLastOptions()['custom']);

        $enter = [];
        $leave = [];
        $parser = new Parser([
            'on_state_enter' => function (NodeEvent $event) use (&$enter) {
                $enter[] = get_class($event->getNode());
            },
            'on_state_leave' => function (NodeEvent $event) use (&$leave) {
                $leave[] = get_class($event->getNode());
            },
            'on_state_store' => function (NodeEvent $event) {
                if ($event->getNode() instanceof ConditionalNode) {
                    $event->setNode(new TextNode());
                }
            },
        ]);
        $store = [];
        $parser->attach(ParserEvent::STATE_STORE, function (NodeEvent $event) use (&$store) {
            $store[] = get_class($event->getNode());
        });
        $parser->parse("div\n  if true\n    p Hello\n// foo");

        self::assertSame([ElementNode::class, ConditionalNode::class], $enter);
        self::assertSame([ConditionalNode::class, ElementNode::class], $leave);
        self::assertSame([ElementNode::class, TextNode::class, ElementNode::class, CommentNode::class], $store);

        $parser = new Parser([
            'on_document' => function (NodeEvent $event) {
                /* @var Parser\NodeInterface $child */
                $child = $event->getNode()->getChildAt(0);
                $event->setNode($child);
            },
        ]);
        /* @var ElementNode $div */
        $div = $parser->parse('div');

        self::assertInstanceOf(ElementNode::class, $div);
        self::assertSame('div', $div->getName());
    }

    /**
     * @covers \Phug\Parser::getModuleBaseClassName
     */
    public function testCetModuleBaseClassName()
    {
        $parser = new Parser();

        self::assertSame(ParserModuleInterface::class, $parser->getModuleBaseClassName());
    }
}
//@codingStandardsIgnoreEnd
