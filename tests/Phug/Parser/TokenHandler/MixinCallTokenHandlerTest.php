<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\TagToken;
use Phug\Parser;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\MixinCallTokenHandler;
use Phug\Test\AbstractParserTest;

/**
 * @coversDefaultClass Phug\Parser\TokenHandler\MixinCallTokenHandler
 */
class MixinCallTokenHandlerTest extends AbstractParserTest
{
    /**
     * @covers \Phug\Parser\Node\MixinCallNode::<public>
     * @covers ::<public>
     */
    public function testHandleSingleLine()
    {
        $template = '+foo(1, 2)';
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [MixinCallNode]',
        ]);
        $mixin = $this->parser->parse($template)->getChildren()[0];
        self::assertSame('foo', $mixin->getName());

        $template = '+#{$foo}(1, 2)';
        $this->assertNodes($template, [
            '[DocumentNode]',
            '  [MixinCallNode]',
        ]);
        $mixin = $this->parser->parse($template)->getChildren()[0];
        self::assertSame('$foo', $mixin->getName()->getValue());
    }

    /**
     * @covers                   ::<public>
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage You can only pass mixin call tokens to this token handler
     */
    public function testHandleTokenTokenException()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex(''));
        $handler = new MixinCallTokenHandler();
        $handler->handleToken(new TagToken(), $state);
    }
}
