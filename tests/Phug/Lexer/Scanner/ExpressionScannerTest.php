<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer;
use Phug\Lexer\Scanner\ExpressionScanner;
use Phug\Lexer\State;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\TagToken;
use Phug\Test\AbstractLexerTest;

class ExpressionScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\ExpressionScanner
     * @covers \Phug\Lexer\Scanner\ExpressionScanner::scan
     */
    public function testExpressionInTag()
    {
        /** @var ExpressionToken $exp */
        list($tag, $exp) = $this->assertTokens('script!= \'foo()\'', [
            TagToken::class,
            ExpressionToken::class,
        ]);
        self::assertSame('\'foo()\'', trim($exp->getValue()));
        self::assertFalse($exp->isEscaped());

        /** @var ExpressionToken $tok */
        list($tok) = $this->assertTokens('=$foo', [
            ExpressionToken::class,
        ]);

        self::assertSame('$foo', $tok->getValue());
        self::assertTrue($tok->isEscaped());
        self::assertTrue($tok->isChecked());

        /** @var ExpressionToken $tok */
        list($tok) = $this->assertTokens('!=42', [
            ExpressionToken::class,
        ]);

        self::assertSame('42', $tok->getValue());
        self::assertFalse($tok->isEscaped());
        self::assertTrue($tok->isChecked());

        /** @var ExpressionToken $tok */
        list($tok) = $this->assertTokens('?=bar()', [
            ExpressionToken::class,
        ]);

        self::assertSame('bar()', $tok->getValue());
        self::assertTrue($tok->isEscaped());
        self::assertFalse($tok->isChecked());

        list($tok) = $this->assertTokens('?!=true', [
            ExpressionToken::class,
        ]);

        self::assertSame('true', $tok->getValue());
        self::assertFalse($tok->isEscaped());
        self::assertFalse($tok->isChecked());
    }

    /**
     * @covers \Phug\Lexer\Scanner\ExpressionScanner
     * @covers \Phug\Lexer\Scanner\ExpressionScanner::scan
     * @covers \Phug\Lexer\Scanner\AttributeScanner::isTruncatedExpression
     */
    public function testExpressionInAttribute()
    {
        /** @var AttributeToken $second */
        list($tag, $open, $first, $second) = $this->assertTokens('a(foo=\'((foo))\' bar= (1) ? 1 : 0 )', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        self::assertSame('(1) ? 1 : 0', trim($second->getValue()));

        /** @var AttributeToken $second */
        list($tag, $open, $first, $second) = $this->assertTokens('a(foo=\'((foo))\' bar= (1) ?one=1 :zero=0 )', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        self::assertSame('(1) ?one=1 :zero=0', trim($second->getValue()));

        /** @var AttributeToken $first */
        /** @var AttributeToken $second */
        /** @var AttributeToken $third */
        list($tag, $open, $first, $second, $third) = $this->assertTokens('a(bar= (1) one=1 :zero=0 )', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        self::assertSame('(1)', trim($first->getValue()));
        self::assertSame('1', trim($second->getValue()));
        self::assertSame('0', trim($third->getValue()));

        /** @var AttributeToken $attribute */
        list($start, $attribute) = $this->assertTokens('(foo=new Date(0))', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        self::assertSame('new Date(0)', trim($attribute->getValue()));
    }

    /**
     * @covers \Phug\Lexer\Scanner\ExpressionScanner
     * @covers \Phug\Lexer\Scanner\ExpressionScanner::scan
     */
    public function testExpressionQuit()
    {
        $state = new State(new Lexer(), 'p', []);
        $scanners = [
            'expression' => ExpressionScanner::class,
        ];
        $tokens = [];
        foreach ($state->loopScan($scanners) as $token) {
            $tokens[] = $token;
        }

        self::assertSame(0, count($tokens));
    }
}
