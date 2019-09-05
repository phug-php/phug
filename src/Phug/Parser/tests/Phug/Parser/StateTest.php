<?php

namespace Phug\Test\Parser;

use PHPUnit\Framework\TestCase;
use Phug\Lexer;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\Node\AttributeNode;
use Phug\Parser\Node\DocumentNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\TagTokenHandler;
use Phug\ParserException;
use Phug\Util\SourceLocation;

/**
 * @coversDefaultClass Phug\Parser\State
 */
class StateTest extends TestCase
{
    private function generateTokens()
    {
        yield 1;
        yield 2;
    }

    /**
     * @covers ::<public>
     */
    public function testGettersAndSetters()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));

        $state->setLevel(3);

        self::assertSame(3, $state->getLevel());

        $state->setTokens($this->generateTokens());

        self::assertSame([1, 2], iterator_to_array($state->getTokens()));

        self::assertInstanceOf(DocumentNode::class, $state->getDocumentNode());

        $element = new ElementNode();

        $state->setParentNode($element);

        self::assertSame($element, $state->getParentNode());

        $element = new ElementNode();

        $state->setCurrentNode($element);

        self::assertSame($element, $state->getCurrentNode());

        $element = new ElementNode();

        $state->setLastNode($element);

        self::assertSame($element, $state->getLastNode());

        $element = new ElementNode();

        $state->setOuterNode($element);

        self::assertSame($element, $state->getOuterNode());
    }

    /**
     * @covers ::<public>
     */
    public function testTokensCrawler()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex("div\np"));

        self::assertTrue($state->hasTokens());
        self::assertSame($state, $state->nextToken());
        self::assertSame('p', $state->nextToken()->getToken()->getName());
        self::assertFalse($state->nextToken()->hasTokens());
    }

    /**
     * @covers ::handleToken
     * @covers ::getNamedHandler
     */
    public function testHandleToken()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'), [
            'token_handlers' => [
                TagToken::class => TagTokenHandler::class,
            ],
        ]);

        $tag = new TagToken();
        $tag->setName('foo');
        $state->handleToken($tag);

        self::assertSame('foo', $state->getCurrentNode()->getName());
    }

    /**
     * @covers ::handleToken
     * @covers ::getNamedHandler
     */
    public function testHandleTokenTwice()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div Hello'), [
            'token_handlers' => [
                TagToken::class => TagTokenHandler::class,
            ],
        ]);

        $tag = new TagToken();
        $tag->setName('foo');
        $state->handleToken($tag);

        self::assertSame('foo', $state->getCurrentNode()->getName());

        // Should works twice and use the chaced named handler.
        $bar = new ElementNode();
        $state->setCurrentNode($bar);
        $tag = new TagToken();
        $tag->setName('bar');
        $state->handleToken($tag);

        self::assertSame('bar', $bar->getName());
    }

    /**
     * @covers                   ::handleToken
     * @expectedException        \Phug\ParserException
     * @expectedExceptionMessage Failed to parse: Unexpected token
     * @expectedExceptionMessage `Phug\Lexer\Token\TagToken`,
     * @expectedExceptionMessage no token handler registered
     */
    public function testHandleTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));

        $tag = new TagToken();
        $state->handleToken($tag);
    }

    /**
     * @covers ::handleToken
     * @covers ::getParser
     * @covers \Phug\ParserException::<public>
     */
    public function testMissingTokenHandlerException()
    {
        $lexer = new Lexer();
        $parser = new Parser();
        $state = new State($parser, $lexer->lex('div'), [
            'token_handlers' => [
                TagToken::class => null,
            ],
        ]);

        self::assertSame($parser, $state->getParser());

        $tag = new TagToken();
        $exception = null;

        try {
            $state->handleToken($tag);
        } catch (ParserException $caught) {
            $exception = $caught;
        }

        self::assertInstanceOf(ParserException::class, $exception);
        self::assertInstanceOf(TagToken::class, $exception->getRelatedToken());
        self::assertSame($tag, $exception->getRelatedToken());
    }

    /**
     * @covers ::handleToken
     */
    public function testHandleTokenWithAnInstance()
    {
        $lexer = new Lexer();
        $handler = new TagTokenHandler();
        $state = new State(new Parser(), $lexer->lex('div'), [
            'token_handlers' => [
                TagToken::class => $handler,
            ],
        ]);

        $tag = new TagToken();
        $tag->setName('foo');
        $state->handleToken($tag);

        self::assertSame('foo', $state->getCurrentNode()->getName());
    }

    /**
     * @covers ::lookUp
     */
    public function testLookUp()
    {
        $tokens = [];
        $lexer = new Lexer();
        $handler = new TagTokenHandler();
        $state = new State(new Parser(), $lexer->lex('div'));

        foreach ($state->lookUp([TagToken::class]) as $token) {
            $tokens[] = $token;
        }

        self::assertSame(1, count($tokens));
        self::assertInstanceOf(TagToken::class, $tokens[0]);

        $tokens = [];
        $lexer = new Lexer();
        $handler = new TagTokenHandler();
        $state = new State(new Parser(), $lexer->lex('div'));

        foreach ($state->lookUp([AttributeToken::class]) as $token) {
            $tokens[] = $token;
        }

        self::assertSame(0, count($tokens));
    }

    /**
     * @covers ::lookUpNext
     */
    public function testLookUpNext()
    {
        $tokens = [];
        $lexer = new Lexer();
        $handler = new TagTokenHandler();
        $state = new State(new Parser(), $lexer->lex("\ndiv\np"));
        $types = [TagToken::class, NewLineToken::class];

        foreach ($state->lookUpNext($types) as $token) {
            $tokens[] = $token;
        }

        self::assertSame(3, count($tokens));
        self::assertInstanceOf(TagToken::class, $tokens[0]);
        self::assertSame('div', $tokens[0]->getName());
        self::assertInstanceOf(NewLineToken::class, $tokens[1]);
        self::assertInstanceOf(TagToken::class, $tokens[2]);
        self::assertSame('p', $tokens[2]->getName());
    }

    /**
     * @covers ::expect
     * @covers ::expectNext
     */
    public function testExpect()
    {
        $tokens = [];
        $lexer = new Lexer();
        $handler = new TagTokenHandler();
        $state = new State(new Parser(), $lexer->lex("\ndiv\n+p"));
        $types = [TagToken::class, NewLineToken::class];

        self::assertInstanceOf(TagToken::class, $state->expectNext($types));
        self::assertInstanceOf(NewLineToken::class, $state->expectNext($types));
        self::assertNull($state->expectNext($types));
    }

    /**
     * @covers ::is
     */
    public function testIs()
    {
        $tokens = [];
        $lexer = new Lexer();
        $handler = new TagTokenHandler();
        $state = new State(new Parser(), $lexer->lex(''));
        $types = [DocumentNode::class, ElementNode::class];
        $element = new ElementNode();
        $attribute = new AttributeNode();

        self::assertTrue($state->is($element, $types));
        self::assertFalse($state->is($attribute, $types));
    }

    /**
     * @covers ::currentNodeIs
     */
    public function testCurrentNodeIs()
    {
        $tokens = [];
        $lexer = new Lexer();
        $handler = new TagTokenHandler();
        $state = new State(new Parser(), $lexer->lex(''));
        $types = [DocumentNode::class, ElementNode::class];
        $element = new ElementNode();
        $attribute = new AttributeNode();

        self::assertFalse($state->currentNodeIs($types));

        $state->setCurrentNode($element);
        self::assertTrue($state->currentNodeIs($types));

        $state->setCurrentNode($attribute);
        self::assertFalse($state->currentNodeIs($types));
    }

    /**
     * @covers ::append
     */
    public function testAppend()
    {
        $tokens = [];
        $lexer = new Lexer();
        $handler = new TagTokenHandler();
        $state = new State(new Parser(), $lexer->lex(''));
        $a = new ElementNode();
        $b = new ElementNode();

        self::assertNull($state->getCurrentNode());

        $state->append($a);

        self::assertSame($a, $state->getCurrentNode());
        self::assertSame(0, count($a->getChildren()));

        $state->append($b);

        self::assertSame($a, $state->getCurrentNode());
        self::assertSame(1, count($a->getChildren()));
        self::assertSame($b, $a->getChildren()[0]);
    }

    /**
     * @covers ::lastNodeIs
     */
    public function testLastNodeIs()
    {
        $tokens = [];
        $lexer = new Lexer();
        $handler = new TagTokenHandler();
        $state = new State(new Parser(), $lexer->lex(''));
        $types = [DocumentNode::class, ElementNode::class];
        $element = new ElementNode();
        $attribute = new AttributeNode();

        self::assertFalse($state->lastNodeIs($types));

        $state->setLastNode($element);
        self::assertTrue($state->lastNodeIs($types));

        $state->setLastNode($attribute);
        self::assertFalse($state->lastNodeIs($types));
    }

    /**
     * @covers ::parentNodeIs
     */
    public function testParentNodeIs()
    {
        $tokens = [];
        $lexer = new Lexer();
        $handler = new TagTokenHandler();
        $state = new State(new Parser(), $lexer->lex(''));
        $types = [DocumentNode::class, ElementNode::class];
        $element = new ElementNode();
        $attribute = new AttributeNode();

        $state->setParentNode(null);
        self::assertFalse($state->parentNodeIs($types));

        $state->setParentNode($element);
        self::assertTrue($state->parentNodeIs($types));

        $state->setParentNode($attribute);
        self::assertFalse($state->parentNodeIs($types));
    }

    /**
     * @covers                   ::createNode
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage ErrorException is not a valid token class
     */
    public function testCreateNodeException()
    {
        $tokens = [];
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));

        $state->createNode(\ErrorException::class);
    }

    /**
     * @covers ::enter
     */
    public function testEnter()
    {
        $tokens = [];
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));

        $element = new ElementNode();
        $state->setLevel(42)->setParentNode($element)->enter();

        self::assertSame(43, $state->getLevel());
        self::assertSame($element, $state->getParentNode());

        $child = new ElementNode();
        $state->setLastNode($child)->enter();

        self::assertSame(44, $state->getLevel());
        self::assertSame($child, $state->getParentNode());
    }

    /**
     * @covers ::leave
     */
    public function testLeave()
    {
        $tokens = [];
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));

        $element = new ElementNode();
        $state->setLevel(42)->setParentNode($element)->enter();
        $child = new ElementNode();
        $state->setLastNode($child)->enter();
        $child->setParent($element);

        self::assertSame(44, $state->getLevel());
        self::assertSame($child, $state->getParentNode());

        $state->leave();

        self::assertSame(43, $state->getLevel());
        self::assertSame($element, $state->getParentNode());
    }

    /**
     * @covers                   ::leave
     * @expectedException        \Phug\ParserException
     * @expectedExceptionMessage Failed to outdent: No parent to outdent to.
     * @expectedExceptionMessage Seems the parser moved out too many levels.
     */
    public function testLeaveException()
    {
        $tokens = [];
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));

        $element = new ElementNode();
        $state->setLevel(42)->setParentNode($element)->leave();
    }

    /**
     * @covers ::store
     */
    public function testStore()
    {
        $tokens = [];
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $element = new ElementNode();
        $state->setParentNode($element);

        self::assertSame($state, $state->store());
        self::assertSame(0, count($element->getChildren()));

        $childA = new ElementNode();
        $state->setCurrentNode($childA);

        self::assertSame($state, $state->store());
        self::assertSame(1, count($element->getChildren()));
        self::assertSame($childA, $element->getChildren()[0]);

        $childB = new ElementNode();
        $state->setCurrentNode($childB);
        $childC = new ElementNode();
        $state->setOuterNode($childC);

        self::assertSame($state, $state->store());
        self::assertSame(2, count($element->getChildren()));
        self::assertSame($childB, $element->getChildren()[1]);
        self::assertSame($childC, $childB->getOuterNode());
    }

    /**
     * @covers                   ::throwException
     * @expectedException        \Phug\ParserException
     * @expectedExceptionMessage Failed to parse: Unexpected token
     * @expectedExceptionMessage `Phug\Lexer\Token\TagToken`,
     * @expectedExceptionMessage no token handler registered
     * @expectedExceptionMessage Token: Phug\Lexer\Token\TagToken
     * @expectedExceptionMessage Line: 12
     * @expectedExceptionMessage Offset: 5
     */
    public function testThrowException()
    {
        $tokens = [];
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $token = new TagToken(new SourceLocation(null, 0, 0), 12, 5);
        $state->handleToken($token);
    }
}
