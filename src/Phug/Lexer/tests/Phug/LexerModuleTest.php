<?php

namespace Phug\Test;

use Phug\AbstractLexerModule;
use Phug\Lexer;
use Phug\Lexer\Event\EndLexEvent;
use Phug\Lexer\Event\LexEvent;
use Phug\Lexer\Event\TokenEvent;
use Phug\LexerEvent;

//@codingStandardsIgnoreStart
class TestModule extends AbstractLexerModule
{
    public function getEventListeners()
    {
        return [
            LexerEvent::TOKEN => function (TokenEvent $event) {
                if ($event->getToken() instanceof Lexer\Token\TagToken) {
                    $event->setToken(new Lexer\Token\ClassToken());
                }
            },
        ];
    }
}

class GeneratorTestModule extends AbstractLexerModule
{
    private function generateTokens()
    {
        yield (new Lexer\Token\TagToken())->setName('div');
        yield new Lexer\Token\ClassToken();
        yield new Lexer\Token\IdToken();
    }

    public function getEventListeners()
    {
        return [
            LexerEvent::TOKEN => function (TokenEvent $event) {
                $token = $event->getToken();
                if ($token instanceof Lexer\Token\TagToken && $token->getName() === 'p') {
                    $event->setTokenGenerator($this->generateTokens());
                }
            },
        ];
    }
}

class IteratorTestModule extends AbstractLexerModule
{
    public function getEventListeners()
    {
        return [
            LexerEvent::TOKEN => function (TokenEvent $event) {
                $token = $event->getToken();
                if ($token instanceof Lexer\Token\TagToken && $token->getName() === 'p') {
                    $event->setTokenGenerator(new \ArrayIterator([
                        (new Lexer\Token\TagToken())->setName('div'),
                        new Lexer\Token\ClassToken(),
                        new Lexer\Token\IdToken(),
                    ]));
                }
            },
        ];
    }
}

/**
 * @coversDefaultClass Phug\AbstractLexerModule
 */
class LexerModuleTest extends AbstractLexerTest
{
    /**
     * @covers ::<public>
     * @covers \Phug\Lexer::lex
     * @covers \Phug\Lexer\Event\TokenEvent::__construct
     * @covers \Phug\Lexer\Event\TokenEvent::getToken
     * @covers \Phug\Lexer\Event\TokenEvent::setToken
     * @covers \Phug\Lexer::handleToken
     * @covers \Phug\Lexer::handleTokens
     * @covers \Phug\Lexer::getModuleBaseClassName
     */
    public function testTokenEvent()
    {
        self::assertTokens('p Test', [
            Lexer\Token\TagToken::class,
            Lexer\Token\TextToken::class,
        ]);

        $lexer = new Lexer(['lexer_modules' => [TestModule::class]]);

        self::assertTokens('p Test', [
            Lexer\Token\ClassToken::class,
            Lexer\Token\TextToken::class,
        ], $lexer);
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Lexer::lex
     * @covers \Phug\Lexer::__construct
     * @covers \Phug\Lexer::updateOptions
     * @covers \Phug\Scanners::getList
     * @covers \Phug\Lexer\Event\LexEvent::__construct
     * @covers \Phug\Lexer\Event\LexEvent::getInput
     * @covers \Phug\Lexer\Event\LexEvent::setInput
     * @covers \Phug\Lexer\Event\LexEvent::getPath
     * @covers \Phug\Lexer\Event\LexEvent::setPath
     * @covers \Phug\Lexer\Event\LexEvent::getStateClassName
     * @covers \Phug\Lexer\Event\LexEvent::setStateClassName
     * @covers \Phug\Lexer\Event\LexEvent::getStateOptions
     * @covers \Phug\Lexer\Event\LexEvent::setStateOptions
     * @covers \Phug\Lexer\Event\EndLexEvent::__construct
     * @covers \Phug\Lexer\Event\EndLexEvent::getLexEvent
     * @covers \Phug\Lexer::handleToken
     * @covers \Phug\Lexer::handleTokens
     * @covers \Phug\Lexer::getModuleBaseClassName
     */
    public function testLexEvent()
    {
        $lexer = new Lexer([
            'on_lex' => function (LexEvent $event) {
                $event->setInput('.bar:'.$event->getInput());
            },
            'on_token' => function (TokenEvent $event) {
                if ($event->getToken() instanceof Lexer\Token\ClassToken) {
                    $event->setToken(new Lexer\Token\TagToken());
                }
            },
        ]);

        self::assertTokens('.foo Test', [
            Lexer\Token\TagToken::class,
            Lexer\Token\ExpansionToken::class,
            Lexer\Token\TagToken::class,
            Lexer\Token\TextToken::class,
        ], $lexer);

        $lexer = new Lexer([
            'on_lex' => function (LexEvent $event) {
                $path = $event->getPath();
                $event->setPath($event->getInput());
                $event->setInput($path);
            },
        ]);

        $tokens = [];
        foreach ($lexer->lex('path.pug', '| foo') as $token) {
            $tokens[] = $token;
        }

        self::assertCount(1, $tokens);
        $token = $tokens[0];
        self::assertInstanceOf(Lexer\Token\TextToken::class, $token);
        /* @var Lexer\Token\TextToken $token */
        self::assertSame('foo', $token->getValue());
        self::assertSame('path.pug', $token->getSourceLocation()->getPath());

        $lexer = new Lexer([
            'on_lex' => function (LexEvent $event) {
                $event->setStateClassName($event->getStateClassName().'\\Custom');
            },
        ]);

        $message = null;

        try {
            foreach ($lexer->lex('path.pug', '| foo') as $token) {
            }
        } catch (\InvalidArgumentException $exception) {
            $message = $exception->getMessage();
        }

        self::assertSame('lexer_state_class_name needs '.
            'to be a valid Phug\Lexer\State sub class, '.
            'Phug\\Lexer\\State\\Custom given', $message);

        $copy = null;
        $copyEnd = null;
        $lexer = new Lexer([
            'allow_mixed_indent' => false,
            'on_lex_end'         => function (EndLexEvent $event) use (&$copyEnd) {
                $copyEnd = $event->getLexEvent();
            },
            'on_lex'             => function (LexEvent $event) use (&$copy) {
                $copy = $event;
                $options = $event->getStateOptions();
                $options['allow_mixed_indent'] = true;
                $event->setStateOptions($options);
            },
        ]);

        self::assertTokens("p\n\t  div", [
            Lexer\Token\TagToken::class,
            Lexer\Token\NewLineToken::class,
            Lexer\Token\IndentToken::class,
            Lexer\Token\TagToken::class,
        ], $lexer);

        self::assertSame($copy, $copyEnd);
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Lexer::lex
     * @covers \Phug\Lexer\Event\TokenEvent::__construct
     * @covers \Phug\Lexer\Event\TokenEvent::getTokenGenerator
     * @covers \Phug\Lexer\Event\TokenEvent::setTokenGenerator
     * @covers \Phug\Lexer::handleToken
     * @covers \Phug\Lexer::handleTokens
     * @covers \Phug\Lexer::getModuleBaseClassName
     */
    public function testTokenGeneratorEvent()
    {
        self::assertTokens('p Test', [
            Lexer\Token\TagToken::class,
            Lexer\Token\TextToken::class,
        ]);

        $lexer = new Lexer(['lexer_modules' => [GeneratorTestModule::class]]);

        self::assertTokens('p Test', [
            Lexer\Token\TagToken::class,
            Lexer\Token\ClassToken::class,
            Lexer\Token\IdToken::class,
            Lexer\Token\TextToken::class,
        ], $lexer);

        $lexer = new Lexer(['lexer_modules' => [IteratorTestModule::class]]);

        self::assertTokens('p Test', [
            Lexer\Token\TagToken::class,
            Lexer\Token\ClassToken::class,
            Lexer\Token\IdToken::class,
            Lexer\Token\TextToken::class,
        ], $lexer);
    }
}
//@codingStandardsIgnoreEnd
