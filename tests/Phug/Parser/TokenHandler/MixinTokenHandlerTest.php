<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\MixinTokenHandler;
use Phug\Test\AbstractParserTest;
use Phug\Test\Utils\ExceptionAnnotationReader;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\MixinTokenHandler
 */
class MixinTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers ::handleMixinToken
     */
    public function testHandleSingleLine()
    {
        $this->assertNodes('mixin foo(a, b)', [
            '[DocumentNode]',
            '  [MixinNode]',
        ]);
        $mixin = $this->parser->parse('mixin foo(a, b)')->getChildren()[0];
        self::assertSame('foo', $mixin->getName());
    }

    /**
     * @covers                   ::handleMixinToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass mixin tokens to MixinTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new MixinTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
