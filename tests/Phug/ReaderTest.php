<?php

namespace Tale\Phug\Test;

use PHPUnit\Framework\TestCase;
use Phug\Reader;

/**
 * @coversDefaultClass Phug\Reader
 */
class ReaderTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getInput
     */
    public function testGetInput()
    {
        $reader = new Reader('some string');
        self::assertEquals('some string', $reader->getInput());
    }

    /**
     * @covers ::__construct
     * @covers ::getEncoding
     */
    public function testIfDefaultEncodingIsUtf8ByDefault()
    {
        $reader = new Reader('');
        self::assertEquals('UTF-8', $reader->getEncoding());
    }

    /**
     * @covers ::__construct
     * @covers ::getEncoding
     */
    public function testGetEncoding()
    {
        $reader = new Reader('', 'ASCII');
        self::assertEquals('ASCII', $reader->getEncoding());
    }

    /**
     * @covers ::getLastPeekResult
     * @covers ::peek
     */
    public function testGetLastPeekResult()
    {
        $reader = new Reader('abc');
        self::assertEquals(null, $reader->getLastPeekResult(), 'not peeked yet');

        $reader->peek(2);
        self::assertEquals('ab', $reader->getLastPeekResult(), 'peeked');
    }

    /**
     * @covers ::getLastMatchResult
     * @covers ::match
     */
    public function testGetLastMatchResult()
    {
        $reader = new Reader('abc');
        self::assertEquals(null, $reader->getLastMatchResult(), 'not matched yet');

        //On valid match
        $reader->match('a(.*)');
        self::assertEquals(['abc', 'bc'], $reader->getLastMatchResult(), 'matched valid');

        //On invalid match
        $reader->match('b(.*)');
        self::assertEquals(null, $reader->getLastMatchResult(), 'matched invalid');
    }

    /**
     * @covers ::getNextConsumeLength
     * @covers ::peek
     * @covers ::match
     */
    public function testGetNextConsumeLength()
    {
        $reader = new Reader('abc def');
        self::assertEquals(null, $reader->getNextConsumeLength(), 'not peeked/matched yet');

        $reader->peek(2);
        self::assertEquals(2, $reader->getNextConsumeLength(), 'peeked');

        $reader->match('b[^ ]+');
        self::assertEquals(null, $reader->getNextConsumeLength(), 'matched invalid');

        $reader->match('a[^ ]+');
        self::assertEquals(3, $reader->getNextConsumeLength(), 'matched valid');
    }

    /**
     * @covers ::getNextConsumeLength
     * @covers ::match
     */
    public function testIfMatchIgnoresTrailingNewLines()
    {
        $reader = new Reader("some\nstring");
        $reader->match('some\s');
        self::assertEquals(4, $reader->getNextConsumeLength());
    }

    /**
     * @covers ::getPosition
     * @covers ::getLine
     * @covers ::getOffset
     * @covers ::consume
     */
    public function testCorrectCalculationOfPositionInformation()
    {
        $reader = new Reader("some\nstring\na\nb\ncde");
        self::assertEquals(0, $reader->getPosition(), 'position after construct');
        self::assertEquals(1, $reader->getLine(), 'line after construct');
        self::assertEquals(1, $reader->getOffset(), 'offset after construct');

        $reader->consume(3);
        self::assertEquals(3, $reader->getPosition(), 'position after 3 bytes');
        self::assertEquals(1, $reader->getLine(), 'line after 3 bytes');
        self::assertEquals(4, $reader->getOffset(), 'offset after 3 bytes');

        $reader->consume(4);
        self::assertEquals(7, $reader->getPosition(), 'position after 7 bytes');
        self::assertEquals(2, $reader->getLine(), 'line after 7 bytes');
        self::assertEquals(2, $reader->getOffset(), 'offset after 7 bytes');

        $reader->consume(10);
        self::assertEquals(17, $reader->getPosition(), 'position after 17 bytes');
        self::assertEquals(5, $reader->getLine(), 'line after 17 bytes');
        self::assertEquals(1, $reader->getOffset(), 'offset after 17 bytes');
    }

    /**
     * @covers ::getInput
     * @covers ::normalize
     */
    public function testNormalization()
    {
        $reader = new Reader("some\r\nstring\n\0maybe hack \vattempt?");
        $reader->normalize();
        self::assertEquals("some\nstring\nmaybe hack attempt?", $reader->getInput());
    }

    /**
     * @covers ::consume
     * @covers ::getLength
     */
    public function testGetLength()
    {
        $reader = new Reader('This is some string');
        self::assertEquals(19, $reader->getLength());
        $reader->consume(4);
        self::assertEquals(15, $reader->getLength());
    }

    /**
     * @covers ::consume
     * @covers ::hasLength
     */
    public function testHasLength()
    {
        $reader = new Reader('Some string');
        $reader->consume(4);
        self::assertTrue($reader->hasLength());
        $reader->consume(6);
        self::assertTrue($reader->hasLength());
        $reader->consume(1);
        self::assertFalse($reader->hasLength());
    }

    /**
     * @covers ::peek
     * @covers ::getLastPeekResult
     * @covers ::getNextConsumeLength
     * @covers ::consume
     */
    public function testPeek()
    {
        $reader = new Reader('This');
        self::assertEquals('T', $reader->peek());
        self::assertEquals('T', $reader->getLastPeekResult());
        self::assertEquals(1, $reader->getNextConsumeLength());

        $reader->consume();

        self::assertEquals('his', $reader->peek(3));
        self::assertEquals('his', $reader->getLastPeekResult());
        self::assertEquals(3, $reader->getNextConsumeLength());

        $reader->consume();

        self::assertNull($reader->peek());

        //Test if peek sets the peek-length to the max-length of the document
        self::assertEquals('test', (new Reader('test'))->peek(30000));
    }

    /**
     * @covers ::peek
     * @expectedException \InvalidArgumentException
     */
    public function testPeekThrowsExceptionOnInvalidArguments()
    {
        (new Reader('a'))->peek(0);
    }

    /**
     * @covers ::match
     * @covers ::getLastMatchResult
     * @covers ::getNextConsumeLength
     * @covers ::consume
     */
    public function testMatch()
    {
        $reader = new Reader('This');
        self::assertFalse($reader->match('this'));
        self::assertTrue($reader->match('[Tt]his'));
        self::assertTrue($reader->match('Th(.*)'), 'This');
        self::assertEquals(['This', 'is'], $reader->getLastMatchResult(), 'This');
        self::assertEquals(4, $reader->getNextConsumeLength(), 'This');

        $reader = new Reader("This\nnext line");
        self::assertTrue($reader->match('Th(.*)'), 'This\nnext line');
        self::assertEquals(['This', 'is'], $reader->getLastMatchResult(), 'This\nnext line');
        self::assertEquals(4, $reader->getNextConsumeLength(), 'This\nnext line');

        $reader = new Reader("This\nnext line");
        self::assertTrue($reader->match('Th(.*)\n'), 'This\nnext line\n');
        self::assertEquals(["This\n", 'is'], $reader->getLastMatchResult(), 'This\nnext line\n');
        self::assertEquals(4, $reader->getNextConsumeLength(), 'This\nnext line\n');
    }

    /**
     * @covers ::match
     * @covers ::getPregErrorText
     * @covers ::throwException
     * @covers \Phug\ReaderException
     * @expectedException \Phug\ReaderException
     */
    public function testMatchFailsOnPregError()
    {
        (new Reader('foobar foobar foobar'))->match('(?:\D+|<\d+>)*[!?]');
    }

    /**
     * @covers ::throwException
     * @covers \Phug\ReaderException
     * @expectedException \Phug\ReaderException
     * @expectedExceptionMessage File: path.pug
     */
    public function testPathInErrors()
    {
        $reader = new Reader('foobar foobar foobar');
        $reader->setPath('path.pug');
        $reader->match('(?:\D+|<\d+>)*[!?]');
    }

    /**
     * @covers ::match
     * @covers ::getMatch
     */
    public function testGetMatch()
    {
        $reader = new Reader('This');
        self::assertEquals(true, $reader->match('Th(.*)'), 'Unnamed');
        self::assertEquals('is', $reader->getMatch(1));

        $reader = new Reader('This');
        self::assertEquals(true, $reader->match('Th(?<name>.*)'), 'Named');
        self::assertEquals('is', $reader->getMatch('name'));
    }

    /**
     * @covers ::getMatch
     * @covers ::throwException
     * @covers \Phug\ReaderException
     * @expectedException \Phug\ReaderException
     */
    public function testGetMatchCantOperateOnMissingMatchCall()
    {
        (new Reader('a'))->getMatch('test');
    }

    /**
     * @covers ::match
     * @covers ::getMatchData
     */
    public function testGetMatchData()
    {
        $reader = new Reader('This is Sparta');
        self::assertEquals(true, $reader->match('(?<who>\w+)\s*is\s*(?<what>\w+)'));
        self::assertEquals(['who' => 'This', 'what' => 'Sparta'], $reader->getMatchData());
    }

    /**
     * @covers ::getMatchData
     * @covers ::throwException
     * @covers \Phug\ReaderException
     * @expectedException \Phug\ReaderException
     */
    public function testGetMatchDataCantOperateOnMissingMatchCall()
    {
        (new Reader('a'))->getMatchData();
    }

    /**
     * @covers ::peek
     * @covers ::match
     * @covers ::consume
     */
    public function testConsume()
    {
        $reader = new Reader("This is Sparta\n");
        self::assertEquals(15, $reader->getLength());

        self::assertEquals('Th', $reader->peek(2));

        self::assertEquals(2, $reader->getNextConsumeLength());
        $reader->consume();
        self::assertEquals(13, $reader->getLength());

        $reader->consume(4);
        self::assertEquals(9, $reader->getLength());

        self::assertEquals("s Sparta\n", $reader->getInput());
        self::assertTrue($reader->match('s\s*(?<name>.*)'));
        self::assertEquals(8, $reader->getNextConsumeLength());
        $reader->consume();

        self::assertEquals("\n", $reader->getInput());
        self::assertEquals(1, $reader->getLength());
    }

    /**
     * @covers ::consume
     * @covers ::throwException
     * @covers \Phug\ReaderException
     * @expectedException \Phug\ReaderException
     */
    public function testConsumeThrowsExceptionOnInvalidConsumeLength()
    {
        (new Reader('a'))->consume();
    }

    /**
     * @covers ::readWhile
     */
    public function testReadWhile()
    {
        $reader = new Reader('This is some string read up to!here and then not anymore');
        self::assertEquals('', $reader->readWhile(function ($char) {
            return $char === 'a';
        }));
        self::assertEquals('This is some string read up to', $reader->readWhile(function ($char) {
            return $char !== '!';
        }));

        self::assertNull((new Reader(''))->readWhile('ctype_alpha'));
    }

    /**
     * @covers ::readWhile
     * @expectedException \InvalidArgumentException
     */
    public function testReadWhileExpectsValidCallback()
    {
        (new Reader('a'))->readWhile('test');
    }

    /**
     * @covers ::readUntil
     */
    public function testReadUntil()
    {
        $reader = new Reader('This is some string read up to!here and then not anymore');
        self::assertEquals('', $reader->readUntil(function ($char) {
            return $char !== 'a';
        }));
        self::assertEquals('This is some string read up to', $reader->readUntil(function ($char) {
            return $char === '!';
        }));
    }

    /**
     * @covers ::peekChar
     */
    public function testPeekChar()
    {
        $reader = new Reader('This is some string read up to!here and then not anymore');
        self::assertFalse($reader->peekChar('t'));
        self::assertTrue($reader->peekChar('T'));
    }

    /**
     * @covers ::peekChars
     */
    public function testPeekChars()
    {
        $reader = new Reader('This is some string read up to!here and then not anymore');
        self::assertFalse($reader->peekChars('this'));
        self::assertTrue($reader->peekChars('hoT'));
        self::assertTrue($reader->peekChars(['h', 'o', 'T']));
    }

    /**
     * @covers ::peekString
     */
    public function testPeekString()
    {
        $reader = new Reader('This is some string read up to!here and then not anymore');
        self::assertFalse($reader->peekString('this'));
        self::assertTrue($reader->peekString('This'));
        self::assertTrue($reader->peekString('This is some'));
    }

    /**
     * @covers ::peekNewLine
     * @covers ::consume
     */
    public function testPeekNewLine()
    {
        $reader = new Reader("Some text\nsome other line");
        self::assertFalse($reader->peekNewLine());
        $reader->consume(9);
        self::assertTrue($reader->peekNewLine());
    }

    /**
     * @covers ::peekIndentation
     * @covers ::consume
     */
    public function testPeekIndentation()
    {
        $reader = new Reader("a \tsome text");
        self::assertFalse($reader->peekIndentation());
        $reader->consume(1);
        self::assertTrue($reader->peekIndentation(), 'space');
        $reader->consume(1);
        self::assertTrue($reader->peekIndentation(), 'tab');
        $reader->consume(1);
        self::assertFalse($reader->peekIndentation());
    }

    /**
     * @covers ::peekQuote
     * @covers ::consume
     */
    public function testPeekQuote()
    {
        $reader = new Reader("a'\"`some text");
        self::assertFalse($reader->peekQuote());
        $reader->consume(1);
        self::assertTrue($reader->peekQuote(), 'single');
        $reader->consume(1);
        self::assertTrue($reader->peekQuote(), 'double');
        $reader->consume(1);
        self::assertTrue($reader->peekQuote(), 'backtick');
        $reader->consume(1);
        self::assertFalse($reader->peekQuote());
    }

    /**
     * @covers ::peekSpace
     * @covers ::consume
     */
    public function testPeekSpace()
    {
        $reader = new Reader("a \n\r\tsome text");
        self::assertFalse($reader->peekSpace());
        $reader->consume(1);
        self::assertTrue($reader->peekSpace(), 'space');
        $reader->consume(1);
        self::assertTrue($reader->peekSpace(), 'new line');
        $reader->consume(1);
        self::assertTrue($reader->peekSpace(), 'carriage return');
        $reader->consume(1);
        self::assertTrue($reader->peekSpace(), 'tab');
        $reader->consume(1);
        self::assertFalse($reader->peekSpace());
    }

    /**
     * @covers ::peekDigit
     * @covers ::consume
     */
    public function testPeekDigit()
    {
        $reader = new Reader('a1some text');
        self::assertFalse($reader->peekDigit());
        $reader->consume(1);
        self::assertTrue($reader->peekDigit());
        $reader->consume(1);
        self::assertFalse($reader->peekDigit());
    }

    /**
     * @covers ::peekAlpha
     * @covers ::consume
     */
    public function testPeekAlpha()
    {
        $reader = new Reader('1a34some text');
        self::assertFalse($reader->peekAlpha());
        $reader->consume(1);
        self::assertTrue($reader->peekAlpha());
        $reader->consume(1);
        self::assertFalse($reader->peekAlpha());
    }

    /**
     * @covers ::peekAlphaNumeric
     * @covers ::consume
     */
    public function testPeekAlphaNumeric()
    {
        $reader = new Reader('!a34?%me text');
        self::assertFalse($reader->peekAlphaNumeric());
        $reader->consume(1);
        self::assertTrue($reader->peekAlphaNumeric(), 'a');
        $reader->consume(1);
        self::assertTrue($reader->peekAlphaNumeric(), '3');
        $reader->consume(1);
        self::assertTrue($reader->peekAlphaNumeric(), '4');
        $reader->consume(1);
        self::assertFalse($reader->peekAlphaNumeric());
    }

    /**
     * @covers ::peekAlphaIdentifier
     * @covers ::consume
     */
    public function testPeekAlphaIdentifier()
    {
        $reader = new Reader('!_a4?%me text');
        self::assertFalse($reader->peekAlphaIdentifier());
        $reader->consume(1);
        self::assertTrue($reader->peekAlphaIdentifier(), '_');
        $reader->consume(1);
        self::assertTrue($reader->peekAlphaIdentifier(), 'a');
        $reader->consume(1);
        self::assertFalse($reader->peekAlphaIdentifier());
    }

    /**
     * @covers ::peekIdentifier
     * @covers ::consume
     */
    public function testPeekIdentifier()
    {
        $reader = new Reader('!_a4?%me text');
        self::assertFalse($reader->peekIdentifier());
        $reader->consume(1);
        self::assertTrue($reader->peekIdentifier(), '_');
        $reader->consume(1);
        self::assertTrue($reader->peekIdentifier(), 'a');
        $reader->consume(1);
        self::assertTrue($reader->peekIdentifier(), '4');
        $reader->consume(1);
        self::assertFalse($reader->peekIdentifier());
    }

    /**
     * @covers ::readIndentation
     */
    public function testReadIndentation()
    {
        $reader = new Reader("\t    some indented text");
        self::assertEquals("\t    ", $reader->readIndentation());
        self::assertNull($reader->readIndentation());
    }

    /**
     * @covers ::readUntilNewLine
     */
    public function testReadUntilNewLine()
    {
        $reader = new Reader("some text\nsome next line");
        self::assertEquals('some text', $reader->readUntilNewLine());
        self::assertEquals('', $reader->readUntilNewLine());
    }

    /**
     * @covers ::readSpaces
     */
    public function testReadSpaces()
    {
        $reader = new Reader("  \n\t  \r\nsome text");
        self::assertEquals("  \n\t  \r\n", $reader->readSpaces());
        self::assertNull($reader->readSpaces());
    }

    /**
     * @covers ::readDigits
     */
    public function testReadDigits()
    {
        $reader = new Reader('616167some text');
        self::assertEquals('616167', $reader->readDigits());
        self::assertNull($reader->readDigits());
    }

    /**
     * @covers ::readAlpha
     */
    public function testReadAlpha()
    {
        $reader = new Reader('sometext616167');
        self::assertEquals('sometext', $reader->readAlpha());
        self::assertNull($reader->readAlpha());
    }

    /**
     * @covers ::readAlphaNumeric
     */
    public function testReadAlphaNumeric()
    {
        $reader = new Reader('sometext6161!67');
        self::assertEquals('sometext6161', $reader->readAlphaNumeric());
        self::assertNull($reader->readAlphaNumeric());
    }

    /**
     * @covers ::readIdentifier
     */
    public function testReadIdentifier()
    {
        $reader = new Reader('1sometext6161!67');
        self::assertNull($reader->readIdentifier());
        $reader->consume(1);
        self::assertEquals('sometext6161', $reader->readIdentifier());
        self::assertNull($reader->readIdentifier());

        $reader = new Reader('1@sometext6161!67');
        self::assertNull($reader->readIdentifier('@'));
        $reader->consume(1);
        self::assertNull($reader->readIdentifier());
        self::assertEquals('sometext6161', $reader->readIdentifier('@'));
        self::assertNull($reader->readIdentifier());
    }

    /**
     * @covers ::readString
     */
    public function testReadString()
    {
        self::assertNull((new Reader('abc'))->readString());
        self::assertEquals('abc"def', (new Reader('"abc\"def" ghi'))->readString());
        self::assertEquals('abc"def', (new Reader('\'abc"def\' ghi'))->readString());
        self::assertEquals('abc`def', (new Reader('`abc\`def` ghi'))->readString());
        self::assertEquals('"abc\"def"', (new Reader('"abc\"def" ghi'))->readString(null, true));
        self::assertEquals('`abc\`def`', (new Reader('`abc\`def` ghi'))->readString(null, true));
        self::assertEquals('abc a fucking bear def', (new Reader('"abc\Xdef" ghi'))->readString([
            'X' => ' a fucking bear ',
        ]));
    }

    /**
     * @covers ::readString
     * @covers ::throwException
     * @covers \Phug\ReaderException
     * @expectedException \Phug\ReaderException
     *
     * @dataProvider provideNotCorrectlyClosedStrings
     */
    public function testReadStringFailsOnNotCorrectlyClosedStrings($string)
    {
        (new Reader($string))->readString();
    }

    public function provideNotCorrectlyClosedStrings()
    {
        return [
            ['"abc'],
            ['"\'abc\''],
        ];
    }

    /**
     * @covers ::readExpression
     */
    public function testReadExpression()
    {
        self::assertNull((new Reader(''))->readExpression());
        self::assertEquals('"abc\"def"', (new Reader('"abc\"def"'))->readExpression());
        self::assertEquals('{ $abc (def) }', (new Reader('{ $abc (def) } ghi'))->readExpression([' ']));
        self::assertEquals('$a ? ($b, $c) : $d', (new Reader('$a ? ($b, $c) : $d, $f, $g'))->readExpression([',']));
        self::assertEquals('$a["1, 2", $f, $g]', (new Reader('$a["1, 2", $f, $g], $f, $g'))->readExpression([',']));
    }

    /**
     * @covers ::readExpression
     * @covers ::throwException
     * @covers \Phug\ReaderException
     * @expectedException \Phug\ReaderException
     *
     * @dataProvider provideNotCorrectlyClosedBrackets
     */
    public function testReadExpressionFailsOnNotCorrectlyClosedBrackets($string)
    {
        (new Reader($string))->readExpression([',']);
    }

    public function provideNotCorrectlyClosedBrackets()
    {
        return [
            ['([), '],
            ['([)]'],
            ['($a{$b},'],
            [')'],
        ];
    }

    /**
     * @covers ::getPregErrorText
     */
    public function testGetPregErrorText()
    {
        $ref = new \ReflectionClass(Reader::class);
        $method = $ref->getMethod('getPregErrorText');
        $method->setAccessible(true);

        $reader = new Reader('');
        $this->assertEquals('No error occured', $method->invokeArgs($reader, [1337]));
    }

    public function testReadmeFirstLexingExample()
    {

        //Some C-style example code
        $code = 'someVar = {a, "this is a string (really, it \"is\")", func(b, c), d}';

        $reader = new Reader($code);
        $tokens = [];
        $blockLevel = 0;
        $expressionLevel = 0;
        while ($reader->hasLength()) {
            //Skip spaces of any kind.
            $reader->readSpaces();

            //Scan for identifiers
            if ($identifier = $reader->readIdentifier()) {
                $tokens[] = ['type' => 'identifier', 'name' => $identifier];
                continue;
            }

            //Scan for Assignments
            if ($reader->peekChar('=')) {
                $reader->consume();
                $tokens[] = ['type' => 'assignment'];
                continue;
            }

            //Scan for strings
            if (($string = $reader->readString()) !== null) {
                $tokens[] = ['type' => 'string', 'value' => $string];
                continue;
            }

            //Scan block start
            if ($reader->peekChar('{')) {
                $reader->consume();
                $blockLevel++;
                $tokens[] = ['type' => 'blockStart'];
                continue;
            }

            //Scan block end
            if ($reader->peekChar('}')) {
                $reader->consume();
                $blockLevel--;
                $tokens[] = ['type' => 'blockEnd'];
                continue;
            }

            //Scan parenthesis start
            if ($reader->peekChar('(')) {
                $reader->consume();
                $expressionLevel++;
                $tokens[] = ['type' => 'listStart'];
                continue;
            }

            //Scan parenthesis end
            if ($reader->peekChar(')')) {
                $reader->consume();
                $expressionLevel--;
                $tokens[] = ['type' => 'listEnd'];
                continue;
            }

            //Scan comma
            if ($reader->peekChar(',')) {
                $reader->consume();
                $tokens[] = ['type' => 'next'];
                continue;
            }

            throw new \Exception(
                'Unexpected '.$reader->peek(10)
            );
        }

        $this->assertSame([
            ['type' => 'identifier', 'name' => 'someVar'],
            ['type' => 'assignment'],
            ['type' => 'blockStart'],
            ['type' => 'identifier', 'name' => 'a'],
            ['type' => 'next'],
            ['type' => 'string', 'value' => 'this is a string (really, it "is")'],
            ['type' => 'next'],
            ['type' => 'identifier', 'name' => 'func'],
            ['type' => 'listStart'],
            ['type' => 'identifier', 'name' => 'b'],
            ['type' => 'next'],
            ['type' => 'identifier', 'name' => 'c'],
            ['type' => 'listEnd'],
            ['type' => 'next'],
            ['type' => 'identifier', 'name' => 'd'],
            ['type' => 'blockEnd'],
        ], $tokens);
    }

    public function testReadmeSecondLexingExample()
    {
        $jade = 'a(href=getUri(\'/abc\', true), title=(title ? title : \'Sorry, no title.\'))';

        $reader = new Reader($jade);

        //Scan Identifier ("a")
        $identifier = $reader->readIdentifier();

        $attributes = [];
        //Enter an attribute block if available
        if ($reader->peekChar('(')) {
            $reader->consume();
            while ($reader->hasLength()) {
                //Ignore spaces
                $reader->readSpaces();

                //Scan the attribute name
                if (!($name = $reader->readIdentifier())) {
                    throw new \Exception('Attributes need a name!');
                }

                //Ignore spaces
                $reader->readSpaces();

                //Make sure there's a =-character
                if (!$reader->peekChar('=')) {
                    throw new \Exception('Failed to read: Expected attribute value');
                }

                $reader->consume();

                //Ignore spaces
                $reader->readSpaces();

                //Read the expression until , or ) is encountered
                //It will ignore , and ) inside any kind of brackets and count brackets correctly until we actually
                //reached the end-bracket
                $value = $reader->readExpression([',', ')']);

                //Add the attribute to our attribute array
                $attributes[$name] = $value;

                //If we don't encounter a , to go on, we break the loop
                if (!$reader->peekChar(',')) {
                    break;
                }

                //Else we consume the , and continue our attribute parsing
                $reader->consume();
            }

            //Now make sure we actually closed our attribute block correctly.
            if (!$reader->peekChar(')')) {
                throw new \Exception('Failed to read: Expected closing bracket');
            }
        }

        $element = ['identifier' => $identifier, 'attributes' => $attributes];
        $this->assertEquals([
            'identifier' => 'a',
            'attributes' => [
                'href'  => 'getUri(\'/abc\', true)',
                'title' => '(title ? title : \'Sorry, no title.\')',
            ], ], $element);
    }

    /**
     * @covers ::__construct
     */
    public function testUtf8BomRemove()
    {
        $reader = new Reader(file_get_contents(__DIR__.'/../utf8bom.pug'));
        self::assertTrue($reader->peekChar('p'));
    }
}
