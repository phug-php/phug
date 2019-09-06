<?php

namespace Phug\Test;

use Phug\Lexer;
use Phug\Lexer\Scanner\TextLineScanner;
use Phug\Lexer\State;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\BlockToken;
use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;

/**
 * @coversDefaultClass \Phug\Lexer
 */
class LexerTest extends AbstractLexerTest
{
    /**
     * @covers ::__construct
     * @covers ::updateOptions
     * @covers \Phug\Scanners::getList
     * @covers ::getScanners
     */
    public function testGetScanners()
    {
        $lexer = new Lexer([
            'scanners' => [
                'indent' => TextLineScanner::class,
            ],
        ]);
        $indent = $lexer->getScanners()['indent'];

        self::assertSame(TextLineScanner::class, $indent);
    }

    /**
     * @covers            \Phug\Lexer\Partial\StateTrait::getState
     * @expectedException \RuntimeException
     */
    public function testGetStateException()
    {
        $this->expectMessageToBeThrown(
            'Failed to get state: No lexing process active. '.
            'Use the `lex()`-method'
        );

        $lexer = new Lexer();
        $lexer->getState();
    }

    /**
     * @covers ::lex
     * @covers \Phug\Lexer\Partial\StateTrait::getState
     */
    public function testGetState()
    {
        include_once __DIR__.'/MockScanner.php';

        $mock = new MockScanner();
        $lexer = new Lexer([
            'scanners' => [
                'tag' => $mock,
            ],
        ]);
        $mock->setLexer($lexer);

        iterator_to_array($lexer->lex('p'));

        self::assertInstanceOf(State::class, $mock->getState());
    }

    /**
     * @covers ::lex
     * @covers \Phug\Lexer\Partial\StateTrait::hasState
     * @covers \Phug\Lexer\State::lastTokenIs
     */
    public function testHasState()
    {
        $lexer = new Lexer();

        self::assertFalse($lexer->hasState());

        $areTags = [];
        foreach ($lexer->lex('p Text') as $token) {
            $areTags[] = $lexer->getState()->lastTokenIs([TagToken::class]);
            self::assertTrue($lexer->hasState());
        }

        self::assertSame([true, false], $areTags);
        self::assertFalse($lexer->hasState());
    }

    /**
     * @covers ::filterScanner
     * @covers ::prependScanner
     * @covers ::addScanner
     */
    public function testAddScanner()
    {
        include_once __DIR__.'/MockScanner.php';

        $lexer = new Lexer();
        $self = $lexer->addScanner('foo', MockScanner::class);
        $scanners = $lexer->getScanners();

        self::assertSame(MockScanner::class, end($scanners));
        self::assertSame($lexer, $self);

        $lexer = new Lexer();
        $self = $lexer->prependScanner('foo', MockScanner::class);
        $scanners = $lexer->getScanners();
        foreach ($scanners as $scanner) {
            break;
        }

        self::assertSame(MockScanner::class, $scanner);
        self::assertSame($lexer, $self);
    }

    /**
     * @covers            ::filterScanner
     * @expectedException \InvalidArgumentException
     */
    public function testFilterScanner()
    {
        $this->expectMessageToBeThrown(
            'Scanner NotAValidClassName is not a valid '.
            'Phug\\Lexer\\ScannerInterface '.
            'instance or extended class'
        );

        $lexer = new Lexer();
        $lexer->addScanner('foo', 'NotAValidClassName');
        iterator_to_array($lexer->lex('p'));
    }

    /**
     * @covers            ::lex
     * @expectedException \InvalidArgumentException
     */
    public function testBadStateClassName()
    {
        $this->expectMessageToBeThrown(
            'lexer_state_class_name needs to be a valid '.
            'Phug\\Lexer\\State sub class'
        );

        $lexer = new Lexer([
            'lexer_state_class_name' => 'NotAValidClassName',
        ]);
        iterator_to_array($lexer->lex('p'));
    }

    /**
     * @covers  ::lex
     */
    public function testIfPathIsPassedToTokenSourceLocationsCorrectly()
    {
        $lexer = new Lexer();

        /* @var TagToken $tag */
        /* @var TextToken $text */
        list($tag, $text) = iterator_to_array($lexer->lex('p Test', 'test.pug'));

        self::assertInstanceOf(TagToken::class, $tag);
        self::assertInstanceOf(TextToken::class, $text);
        self::assertSame('test.pug', $tag->getSourceLocation()->getPath());
        self::assertSame('test.pug', $text->getSourceLocation()->getPath());
    }

    /**
     * @covers ::dump
     * @covers \Phug\Lexer\Partial\DumpTokenTrait::dumpToken
     * @covers \Phug\Lexer\Partial\DumpTokenTrait::dumpAttributeToken
     * @covers \Phug\Lexer\Partial\DumpTokenTrait::dumpTextToken
     * @covers \Phug\Lexer\Partial\DumpTokenTrait::dumpExpressionToken
     * @covers \Phug\Lexer\Partial\DumpTokenTrait::getTokenSymbol
     * @covers \Phug\Lexer\Partial\DumpTokenTrait::getTokenName
     */
    public function testDump()
    {
        $lexer = new Lexer();
        $attr = new AttributeToken();
        $attr->setName('foo');
        $attr->setValue('bar');
        $text = new TextToken();
        $text->setValue('bla');
        $exp = new ExpressionToken();
        $exp->setValue('$foo');

        self::assertSame('[)]', $lexer->dump(new AttributeEndToken()));
        self::assertSame('[(]', $lexer->dump(new AttributeStartToken()));
        self::assertSame('[Attr foo=bar (unescaped, checked)]', $lexer->dump($attr));
        self::assertSame('[Expr $foo (unescaped, checked)]', $lexer->dump($exp));
        self::assertSame('[->]', $lexer->dump(new IndentToken()));
        self::assertSame('[<-]', $lexer->dump(new OutdentToken()));
        self::assertSame("[\\n]\n", $lexer->dump(new NewLineToken()));
        self::assertSame('[Text bla]', $lexer->dump($text));
        self::assertSame('[Phug\Lexer\Token\Block]', $lexer->dump(new BlockToken()));
        self::assertSame('[Phug\Lexer\Token\Tag][Text Hello]', $lexer->dump('p Hello'));
    }
}
