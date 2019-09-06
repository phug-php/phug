<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\ExpansionToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\SubjectTrait;

abstract class AbstractControlStatementScannerTest extends AbstractLexerTest
{
    abstract protected function getTokenClassName();

    abstract protected function getStatementName();

    public function provideExpressions()
    {
        return [
            ['$someSubject'],
            ['$a ? $b : $c'],
            ['$a ?: $b'],
            ['Foo::$bar'],
            ['Foo::bar()'],
            ['$a ? $b : ($c ? $d : $e)'],
            ['($some ? $ternary : $operator)'],
        ];
    }

    /**
     * @dataProvider provideExpressions
     */
    public function testCommonStatementExpressions($expr)
    {
        $stmt = $this->getStatementName();

        if (!method_exists($this->getTokenClassName(), 'getSubject')) {
            throw new \RuntimeException(
                "Cant run control statement expression tests on a token class that doesn't use ".SubjectTrait::class
            );
        }

        /** @var SubjectTrait|NameTrait $tok */
        list($tok) = $this->assertTokens("$stmt $expr", [$this->getTokenClassName()]);

        if (method_exists($tok, 'getName')) {
            self::assertSame($stmt, $tok->getName());
        }

        self::assertSame($expr, $tok->getSubject());
    }

    /**
     * @dataProvider provideExpressions
     */
    public function testExpandedExpressions($expr)
    {
        $stmt = $this->getStatementName();

        if (!method_exists($this->getTokenClassName(), 'getSubject')) {
            throw new \RuntimeException(
                "Cant run control statement expression tests on a token class that doesn't use ".SubjectTrait::class
            );
        }

        /** @var SubjectTrait|NameTrait $tok */
        list($tok) = $this->assertTokens("$stmt $expr: p Some Text", [
            $this->getTokenClassName(),
            ExpansionToken::class,
            TagToken::class,
            TextToken::class,
        ]);

        if (method_exists($tok, 'getName')) {
            self::assertSame($stmt, $tok->getName());
        }

        self::assertSame($expr, $tok->getSubject());
    }
}
