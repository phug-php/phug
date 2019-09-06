<?php

namespace Phug\Test\Lexer;

use PHPUnit\Framework\TestCase;
use Phug\Lexer;
use Phug\Lexer\Scanner\TagScanner;
use Phug\Lexer\Scanner\TextScanner;
use Phug\Lexer\State;
use Phug\Lexer\Token\BlockToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Reader;
use Phug\Test\MockScanner;

/**
 * @coversDefaultClass \Phug\Lexer\State
 */
class StateTest extends TestCase
{
    /**
     * @covers                   ::__construct
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Configuration option `reader_class_name`
     * @expectedExceptionMessage needs to be a valid FQCN of a class
     * @expectedExceptionMessage that extends Phug\Reader
     */
    public function testBadReaderClass()
    {
        new State(new Lexer(), 'p Hello', [
            'reader_class_name' => 'NotAValidClassName',
        ]);
    }

    /**
     * @covers ::__construct
     * @covers ::getReader
     */
    public function testGetReader()
    {
        $state = new State(new Lexer(), 'p Hello', []);

        self::assertInstanceOf(Reader::class, $state->getReader());
    }

    /**
     * @covers ::__construct
     * @covers \Phug\Lexer\Partial\IndentStyleTrait::getIndentStyle
     */
    public function testGetIndentStyle()
    {
        $state = new State(new Lexer(), 'p Hello', [
            'indent_style' => null,
        ]);

        self::assertNull($state->getIndentStyle());
    }

    /**
     * @covers ::__construct
     * @covers \Phug\Lexer\Partial\IndentStyleTrait::setIndentStyle
     * @covers \Phug\Lexer\Partial\IndentStyleTrait::getIndentStyle
     */
    public function testSetIndentStyle()
    {
        $state = new State(new Lexer(), 'p Hello', []);
        $state->setIndentStyle(Lexer::INDENT_TAB);

        self::assertSame(Lexer::INDENT_TAB, $state->getIndentStyle());
    }

    /**
     * @covers                   \Phug\Lexer\Partial\IndentStyleTrait::setIndentStyle
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage indentStyle needs to be null or one of the INDENT_* constants of the lexer
     */
    public function testSetIndentStyleException()
    {
        $state = new State(new Lexer(), 'p Hello', []);
        $state->setIndentStyle(42);
    }

    /**
     * @covers \Phug\Lexer\Partial\IndentStyleTrait::getIndentWidth
     */
    public function testGetIndentWidth()
    {
        $state = new State(new Lexer(), 'p Hello', [
            'indent_width' => null,
        ]);

        self::assertNull($state->getIndentWidth());
    }

    /**
     * @covers ::getLexer
     */
    public function testGetLexer()
    {
        $state = new State($lexer = new Lexer(), 'p Hello', []);

        foreach ($lexer->lex('p Hello') as $token) {
            self::assertSame($lexer, $lexer->getState()->getLexer());
            break;
        }
    }

    /**
     * @covers \Phug\Lexer\Partial\IndentStyleTrait::setIndentWidth
     */
    public function testSetIndentWidth()
    {
        $state = new State(new Lexer(), 'p Hello', [
            'indent_width' => null,
        ]);
        $state->setIndentWidth(42);

        self::assertSame(42, $state->getIndentWidth());
    }

    /**
     * @covers                   \Phug\Lexer\Partial\IndentStyleTrait::setIndentWidth
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage indentWidth needs to be null or an integer above 0
     */
    public function testSetIndentWidthException()
    {
        $state = new State(new Lexer(), 'p Hello', []);
        $state->setIndentWidth(-1);
    }

    /**
     * @covers ::createToken
     */
    public function testCreateToken()
    {
        $state = new State(new Lexer(), 'p Hello', []);
        $block = $state->createToken(BlockToken::class);

        self::assertInstanceOf(BlockToken::class, $block);
    }

    /**
     * @covers                   ::createToken
     * @covers                   ::throwException
     * @expectedException        \Phug\LexerException
     * @expectedExceptionMessage Failed to lex: bar
     * @expectedExceptionMessage Near: p Hello
     * @expectedExceptionMessage Line: 1
     * @expectedExceptionMessage Offset: 0
     * @expectedExceptionMessage Position: 0
     * @expectedExceptionMessage Path: foo
     */
    public function testCreateTokenException()
    {
        $state = new State(new Lexer(), 'p Hello', [
            'path' => 'foo',
        ]);
        $state->createToken('bar');
    }

    /**
     * @covers                   ::__construct
     * @expectedException        \Phug\ReaderException
     * @expectedExceptionMessage File: path.pug
     */
    public function testReaderExceptionWithPath()
    {
        $state = new State(new Lexer(), 'foobar foobar foobar', [
            'path' => 'path.pug',
        ]);
        foreach ($state->scanToken(TextToken::class, '(?:\D+|<\d+>)*[!?]') as $token) {
            self::assertSame('should not exist', $token);
        }
    }

    /**
     * @covers ::scan
     * @covers ::loopScan
     * @covers ::scanToken
     * @covers ::filterScanners
     */
    public function testScan()
    {
        $state = new State(new Lexer(), 'p Hello', []);
        $scanners = [
            'tag'       => TagScanner::class,
            'text_line' => TextScanner::class,
        ];
        $tokens = [];
        foreach ($state->loopScan($scanners) as $token) {
            $tokens[] = $token;
        }

        self::assertInstanceOf(TagToken::class, $tokens[0]);
        self::assertSame('p', $tokens[0]->getName());
        self::assertInstanceOf(TextToken::class, $tokens[1]);
        self::assertSame('Hello', $tokens[1]->getValue());
    }

    /**
     * @covers                   ::scan
     * @covers                   ::filterScanners
     * @covers                   ::throwException
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage The passed scanner with key `tag`
     * @expectedExceptionMessage doesn't seem to be either a valid
     * @expectedExceptionMessage Phug\Lexer\ScannerInterface
     */
    public function testFilterScanersException()
    {
        $state = new State(new Lexer(), 'p Hello', []);
        $scanners = [
            'tag' => stdClass::class,
        ];
        foreach ($state->loopScan($scanners) as $token) {
        }
    }

    /**
     * @covers                   ::scan
     * @covers                   ::filterScanners
     * @covers                   ::throwException
     * @expectedException        \Phug\LexerException
     * @expectedExceptionMessage Scanner Phug\Test\MockScanner
     * @expectedExceptionMessage generated a result that is not a
     * @expectedExceptionMessage Phug\Lexer\TokenInterface
     */
    public function testScanException()
    {
        include_once __DIR__.'/../MockScanner.php';

        $mock = new MockScanner();
        $mock->badTokens();

        $state = new State(new Lexer(), 'p Hello', []);
        $scanners = [
            'tag' => $mock,
        ];
        foreach ($state->scan($scanners) as $token) {
        }
    }

    /**
     * @covers                   ::loopScan
     * @covers                   ::throwException
     * @expectedException        \Phug\LexerException
     * @expectedExceptionMessage Unexpected p Hello
     */
    public function testLoopScanException()
    {
        $state = new State(new Lexer(), 'p Hello', []);
        foreach ($state->loopScan([], true) as $token) {
        }
    }
}
