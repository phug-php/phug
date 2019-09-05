<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\ExpansionToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Lexer\Token\WhenToken;

class WhenScannerTest extends AbstractControlStatementScannerTest
{
    protected function getTokenClassName()
    {
        return WhenToken::class;
    }

    protected function getStatementName()
    {
        return 'when';
    }

    /**
     * @covers \Phug\Lexer\Scanner\WhenScanner::__construct
     * @covers \Phug\Lexer\Scanner\ControlStatementScanner
     * @covers \Phug\Lexer\Scanner\ControlStatementScanner::__construct
     * @covers \Phug\Lexer\Scanner\ControlStatementScanner::scan
     * @covers \Phug\Lexer\Scanner\Partial\NamespaceAndTernaryTrait::checkForTernary
     * @covers \Phug\Lexer\Scanner\Partial\NamespaceAndTernaryTrait::checkForNamespaceAndTernary
     * @dataProvider provideExpressions
     */
    public function testExpandedExpressions($expr)
    {
        parent::testExpandedExpressions($expr);
    }

    public function testDefault()
    {

        /** @var WhenToken $tok */
        list($tok) = $this->assertTokens('default: p Do something', [
            WhenToken::class,
            ExpansionToken::class,
            TagToken::class,
            TextToken::class,
        ]);

        self::assertSame('default', $tok->getName());
        self::assertNull($tok->getSubject());
    }
}
