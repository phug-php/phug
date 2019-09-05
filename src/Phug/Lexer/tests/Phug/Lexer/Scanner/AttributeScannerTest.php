<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer;
use Phug\Lexer\Scanner\AttributeScanner;
use Phug\Lexer\State;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\ClassToken;
use Phug\Lexer\Token\DoctypeToken;
use Phug\Lexer\Token\IdToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class AttributeScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\AttributeScanner
     * @covers \Phug\Lexer\Scanner\AttributeScanner::skipComments
     * @covers \Phug\Lexer\Scanner\AttributeScanner::getAttributeToken
     * @covers \Phug\Lexer\Scanner\AttributeScanner::seedAttributeToken
     * @covers \Phug\Lexer\Scanner\AttributeScanner::scanParenthesesContent
     * @covers \Phug\Lexer\Scanner\AttributeScanner::scanParentheses
     * @covers \Phug\Lexer\Scanner\AttributeScanner::scan
     * @covers \Phug\Lexer\Scanner\ElementScanner
     * @covers \Phug\Lexer\Scanner\ElementScanner::scan
     */
    public function testScan()
    {
        $tokens = $this->assertTokens('(a + b)', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        /** @var AttributeToken $token */
        $token = $tokens[1];

        self::assertSame('a + b', $token->getName());
        self::assertNull($token->getValue());

        $this->assertTokens('()', [
            AttributeStartToken::class,
            AttributeEndToken::class,
        ]);

        $this->assertTokens('(a=b c=d e=f)', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        $this->assertTokens('(a=b . c)', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        $this->assertTokens('(a b c)', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        $this->assertTokens('(a, b, c)', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        $tokens = $this->assertTokens('(a, b, ...c)', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        /** @var AttributeToken $b */
        $b = $tokens[2];
        /** @var AttributeToken $c */
        $c = $tokens[3];

        self::assertFalse($b->isVariadic());
        self::assertTrue($c->isVariadic());

        $this->assertTokens('(a=b,c=d, e=f)', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        $this->assertTokens('(a=b c=clone $foo e=new Date())', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        $this->assertTokens('(a=b c=d e=f)#foo', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            IdToken::class,
        ]);

        $this->assertTokens('(a=b c=d e=f).foo', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            ClassToken::class,
        ]);

        $this->assertTokens('(a=b c=d e=f). foo', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            TextToken::class,
        ]);

        $this->assertTokens(
            '(a=b
        c=d     e=f
        //ignored line
    ,g=h        )',
            [
                AttributeStartToken::class,
                AttributeToken::class,
                AttributeToken::class,
                AttributeToken::class,
                AttributeToken::class,
                AttributeEndToken::class,
            ]
        );

        $this->assertTokens(
            '(
                a//ignore
                b //ignore
                c//ignore
                =d
                e=//ignore
                f//ignore
                g=h//ignore
            )',
            [
                AttributeStartToken::class,
                AttributeToken::class,
                AttributeToken::class,
                AttributeToken::class,
                AttributeToken::class,
                AttributeToken::class,
                AttributeEndToken::class,
            ]
        );
    }

    /**
     * @covers \Phug\Lexer\Scanner\AttributeScanner
     * @covers \Phug\Lexer\Scanner\AttributeScanner::getAttributeValue
     */
    public function testScanSpacing()
    {
        $this->assertTokens("(\n  id=\$test['id']\n)", [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
    }

    /**
     * @covers            \Phug\Lexer\Scanner\AttributeScanner
     * @covers            \Phug\Lexer\Scanner\AttributeScanner::skipComments
     * @covers            \Phug\Lexer\Scanner\AttributeScanner::getAttributeToken
     * @covers            \Phug\Lexer\Scanner\AttributeScanner::seedAttributeToken
     * @covers            \Phug\Lexer\Scanner\AttributeScanner::scanParenthesesContent
     * @covers            \Phug\Lexer\Scanner\AttributeScanner::scanParentheses
     * @covers            \Phug\Lexer\Scanner\AttributeScanner::scan
     * @expectedException \Phug\LexerException
     */
    public function testFailsOnUnclosedBracket()
    {
        iterator_to_array($this->lexer->lex('(a=b'));
    }

    /**
     * @covers \Phug\Lexer\Scanner\AttributeScanner
     * @covers \Phug\Lexer\Scanner\AttributeScanner::skipComments
     * @covers \Phug\Lexer\Scanner\AttributeScanner::getAttributeToken
     * @covers \Phug\Lexer\Scanner\AttributeScanner::seedAttributeToken
     * @covers \Phug\Lexer\Scanner\AttributeScanner::scanParenthesesContent
     * @covers \Phug\Lexer\Scanner\AttributeScanner::scanParentheses
     * @covers \Phug\Lexer\Scanner\AttributeScanner::scan
     */
    public function testDetailedScan()
    {
        /** @var AttributeToken $attr */
        list(, $attr) = $this->assertTokens('(a=b)', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertSame('a', $attr->getName());
        $this->assertSame('b', $attr->getValue());
        $this->assertSame(true, $attr->isEscaped());
        $this->assertSame(true, $attr->isChecked());

        /** @var AttributeToken $attr */
        list(, $attr) = $this->assertTokens('(a!=b)', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertSame('a', $attr->getName());
        $this->assertSame('b', $attr->getValue());
        $this->assertSame(false, $attr->isEscaped());
        $this->assertSame(true, $attr->isChecked());

        /** @var AttributeToken $attr */
        list(, $attr) = $this->assertTokens('(a?=b)', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertSame('a', $attr->getName());
        $this->assertSame('b', $attr->getValue());
        $this->assertSame(true, $attr->isEscaped());
        $this->assertSame(false, $attr->isChecked());

        /** @var AttributeToken $attr */
        list(, $attr) = $this->assertTokens('(a?!=b)', [
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertSame('a', $attr->getName());
        $this->assertSame('b', $attr->getValue());
        $this->assertSame(false, $attr->isEscaped());
        $this->assertSame(false, $attr->isChecked());
    }

    /**
     * @covers \Phug\Lexer\Scanner\AttributeScanner
     * @covers \Phug\Lexer\Scanner\AttributeScanner::skipComments
     * @covers \Phug\Lexer\Scanner\AttributeScanner::getAttributeToken
     * @covers \Phug\Lexer\Scanner\AttributeScanner::seedAttributeToken
     * @covers \Phug\Lexer\Scanner\AttributeScanner::scanParenthesesContent
     * @covers \Phug\Lexer\Scanner\AttributeScanner::scanParentheses
     * @covers \Phug\Lexer\Scanner\AttributeScanner::scan
     */
    public function testAttributeQuit()
    {
        $state = new State(new Lexer(), 'p', []);
        $scanners = [
            'attribute' => AttributeScanner::class,
        ];
        $tokens = [];
        foreach ($state->loopScan($scanners) as $token) {
            $tokens[] = $token;
        }

        self::assertSame(0, count($tokens));
    }

    /**
     * @covers \Phug\Lexer\Scanner\AttributeScanner
     * @covers \Phug\Lexer\Scanner\AttributeScanner::scan
     */
    public function testSpecialAttributes()
    {
        $this->assertTokens('!!! strict', [
            DoctypeToken::class,
        ]);
        $this->assertTokens('a(href=\'/contact\') contact', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            TextToken::class,
        ]);
        $this->assertTokens('a(href=\'/save\').button save', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            ClassToken::class,
            TextToken::class,
        ]);
        $this->assertTokens('a(foo, bar, baz)', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens('a(foo=\'foo, bar, baz\', bar=1)', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens('a(foo=\'((foo))\', bar= 11 ? 1 : 0 )', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens('a(bar=$var ? "color: red" : "color: blue" baz="baz")', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens(implode("\n", [
            'select',
            '  option(value=\'foo\', selected) Foo',
            '  option(selected, value=\'bar\') Bar',
        ]), [
            TagToken::class,
            NewLineToken::class,
            IndentToken::class,
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            TextToken::class,
            NewLineToken::class,
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            TextToken::class,
        ]);
        $this->assertTokens('a(5)', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens('a("a")', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        /* @var AttributeToken $attribute */
        list(, , $attribute) = $this->assertTokens('a(=5)', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        self::assertSame('5', $attribute->getName());
        self::assertNull($attribute->getValue());
        /* @var AttributeToken $attribute */
        list(, , $attribute) = $this->assertTokens('a(="a")', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        self::assertSame('"a"', $attribute->getName());
        self::assertNull($attribute->getValue());
        $this->assertTokens('a(yop bar baz)', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens('a(yop=\'yop bar, baz\' bar=1)', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens('a(yop=\'((yop))\' bar= 11 ? 1 : 0 )', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens('a(yop=\'((yop))\' bar= 11 ? 1 : 0 )', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens('input(type=\'radio\' checked=0)', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens('input(type=\'radio\' checked=\'\')', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens('input(type=\'radio\' checked=false)', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens('input(type=\'radio\' checked=null)', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens('input(type=\'radio\' checked=undefined)', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens('input(class=(true==true) ? \'on\' : \'off\')', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
    }

    public function testDoubleTernary()
    {
        list(, , , $href, $target, $rel) = $this->assertTokens(implode("\n", [
            'a.footer-group-link(',
            '  href = row.url,',
            "  target = row.is_blank ? '_blank' : null,",
            "  rel = row.nofollow ? 'nofollow' : null,",
            ')',
        ]), [
            TagToken::class,
            ClassToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        /* @var AttributeToken $href */
        $this->assertSame('href', $href->getName());
        $this->assertSame('row.url', $href->getValue());

        /* @var AttributeToken $target */
        $this->assertSame('target', $target->getName());
        $this->assertSame('row.is_blank ? \'_blank\' : null', $target->getValue());

        /* @var AttributeToken $rel */
        $this->assertSame('rel', $rel->getName());
        $this->assertSame('row.nofollow ? \'nofollow\' : null', $rel->getValue());

        list(, , , $href, $target, $rel) = $this->assertTokens(implode("\n", [
            'a.footer-group-link(',
            '  href = row.url',
            "  target = row.is_blank ? '_blank' : null",
            "  rel = row.nofollow ? 'nofollow' : null",
            ')',
        ]), [
            TagToken::class,
            ClassToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        /* @var AttributeToken $href */
        $this->assertSame('href', $href->getName());
        $this->assertSame('row.url', $href->getValue());

        /* @var AttributeToken $target */
        $this->assertSame('target', $target->getName());
        $this->assertSame('row.is_blank ? \'_blank\' : null', $target->getValue());

        /* @var AttributeToken $rel */
        $this->assertSame('rel', $rel->getName());
        $this->assertSame('row.nofollow ? \'nofollow\' : null', $rel->getValue());
    }

    public function testTernaryWithCondition()
    {
        list(, , , $href, $target, $rel) = $this->assertTokens(implode("\n", [
            'a.footer-group-link(',
            '  href = row.url,',
            "  target = row.is_blank == 9 ? '_blank' : null,",
            "  rel = row.nofollow > 4 ? 'nofollow' : null,",
            ')',
        ]), [
            TagToken::class,
            ClassToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        /* @var AttributeToken $href */
        $this->assertSame('href', $href->getName());
        $this->assertSame('row.url', $href->getValue());

        /* @var AttributeToken $target */
        $this->assertSame('target', $target->getName());
        $this->assertSame('row.is_blank == 9 ? \'_blank\' : null', $target->getValue());

        /* @var AttributeToken $rel */
        $this->assertSame('rel', $rel->getName());
        $this->assertSame('row.nofollow > 4 ? \'nofollow\' : null', $rel->getValue());

        list(, , , $href, $target, $rel) = $this->assertTokens(implode("\n", [
            'a.footer-group-link(',
            '  href = row.url',
            "  target = row.is_blank == 9 ? '_blank' : null",
            "  rel = row.nofollow > 4 ? 'nofollow' : null",
            ')',
        ]), [
            TagToken::class,
            ClassToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        /* @var AttributeToken $href */
        $this->assertSame('href', $href->getName());
        $this->assertSame('row.url', $href->getValue());

        /* @var AttributeToken $target */
        $this->assertSame('target', $target->getName());
        $this->assertSame('row.is_blank == 9 ? \'_blank\' : null', $target->getValue());

        /* @var AttributeToken $rel */
        $this->assertSame('rel', $rel->getName());
        $this->assertSame('row.nofollow > 4 ? \'nofollow\' : null', $rel->getValue());
    }

    /**
     * @covers \Phug\Lexer\Scanner\AttributeScanner
     * @covers \Phug\Lexer\Scanner\AttributeScanner::scan
     * @covers \Phug\Lexer\Scanner\ExpressionScanner
     * @covers \Phug\Lexer\Scanner\ExpressionScanner::scan
     */
    public function testJsAttributeStyle()
    {
        list($tag, , $href, $class) = $this->assertTokens('a(href=href[i] class="a")', [
            TagToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        /* @var TagToken $tag */
        self::assertSame('a', $tag->getName());
        /* @var AttributeToken $href */
        self::assertSame('href', $href->getName());
        self::assertSame('href[i]', $href->getValue());
        /* @var AttributeToken $class */
        self::assertSame('class', $class->getName());
        self::assertSame('"a"', $class->getValue());
    }
}
