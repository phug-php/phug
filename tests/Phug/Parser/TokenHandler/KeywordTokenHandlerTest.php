<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\KeywordTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\TokenHandler\KeywordTokenHandler
 */
class KeywordTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::<public>
     */
    public function testHandleToken()
    {
        $parser = new Parser([
            'keywords' => [
                'foo' => 'FOO',
            ],
        ]);
        $code = "foo bar\n  bar";
        $dump = str_replace('Phug\\Parser\\Node\\', '', $parser->dump($code));
        self::assertSame(implode("\n", [
            '[DocumentNode]',
            '  [KeywordNode]',
            '    [ElementNode]',
        ]), $dump);

        $document = $parser->parse($code);

        /** @var Parser\Node\KeywordNode $keyword */
        $keyword = $document->getChildAt(0);

        self::assertSame('foo', $keyword->getName());
        self::assertSame('bar', $keyword->getValue());
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You can only pass keyword tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new KeywordTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
