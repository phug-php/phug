<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\AssignmentToken;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\TagToken;
use Phug\Test\AbstractLexerTest;

class AssignmentScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\AssignmentScanner
     * @covers \Phug\Lexer\Scanner\AssignmentScanner::scan
     */
    public function testScan()
    {
        /** @var AssignmentToken $tok */
        list($tok) = $this->assertTokens('&test', [
            AssignmentToken::class,
        ]);

        self::assertSame('test', $tok->getName());
    }

    public function testScanWithAttributes()
    {
        /** @var AssignmentToken $tok */
        list($tok) = $this->assertTokens('&test(a=a b=b c=c)', [
            AssignmentToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        self::assertSame('test', $tok->getName());
    }

    /**
     * @see https://github.com/phug-php/phug/issues/22
     */
    public function testObjectInTernary()
    {
        $code = "a()&attributes(isNestedFile ? {'href': '../account-orders.html'} : {'href': 'accountorders.html'})";
        /** @var AttributeToken $tok */
        list(, , , , , $tok) = $this->assertTokens($code, [
            TagToken::class,
            AttributeStartToken::class,
            AttributeEndToken::class,
            AssignmentToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        self::assertSame(
            'isNestedFile ? {\'href\': \'../account-orders.html\'} : {\'href\': \'accountorders.html\'}',
            $tok->getName()
        );
    }
}
