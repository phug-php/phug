<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\WhileToken;

class WhileScannerTest extends AbstractControlStatementScannerTest
{
    protected function getTokenClassName()
    {
        return WhileToken::class;
    }

    protected function getStatementName()
    {
        return 'while';
    }

    /**
     * @covers \Phug\Lexer\Scanner\WhileScanner::__construct
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
}
