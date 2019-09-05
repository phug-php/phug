<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer;
use Phug\Lexer\Scanner\CodeScanner;
use Phug\Lexer\State;
use Phug\Lexer\Token\AssignmentToken;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\CodeToken;
use Phug\Lexer\Token\CommentToken;
use Phug\Lexer\Token\EachToken;
use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class CodeScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\CodeScanner
     * @covers \Phug\Lexer\Scanner\CodeScanner::scan
     */
    public function testSingleLineCode()
    {
        /** @var TextToken $tok */
        list(, $tok) = $this->assertTokens('- $someCode()', [
            CodeToken::class,
            TextToken::class,
        ]);

        self::assertSame('$someCode()', $tok->getValue());

        // attached to a tag
        $this->assertTokens('div- foo();', [
            TagToken::class,
            CodeToken::class,
            TextToken::class,
        ]);
    }

    /**
     * @covers \Phug\Lexer\Scanner\CodeScanner
     * @covers \Phug\Lexer\Scanner\CodeScanner::scan
     */
    public function testSpecialOperators()
    {
        $this->assertTokens('p&attributes($test[\'attributes\'] ?? [])', [
            TagToken::class,
            AssignmentToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
        $this->assertTokens('p&attributes($test[\'attributes\'] ?: [])', [
            TagToken::class,
            AssignmentToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);
    }

    /**
     * @covers \Phug\Lexer\Scanner\CodeScanner
     * @covers \Phug\Lexer\Scanner\CodeScanner::scan
     */
    public function testCodeBlock()
    {
        /** @var TextToken $tok */
        list(, , , $tok) = $this->assertTokens("-\n  foo();\n  \$bar = 1;", [
            CodeToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        self::assertSame("foo();\n\$bar = 1;", $tok->getValue());

        $state = new State(new Lexer(), 'p', []);
        $scanners = [
            'tag' => CodeScanner::class,
        ];
        $tokens = [];
        foreach ($state->loopScan($scanners) as $token) {
            $tokens[] = $token;
        }

        self::assertSame(0, count($tokens));

        // attached to a tag
        $this->assertTokens("-\n  foo();\n  \$bar = 1;", [
            CodeToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
        ]);

        $code = '-
  list = ["uno", "dos", "tres",
          "cuatro", "cinco", "seis"];
//- Without a block, the element is accepted and no code is generated
-
each item in list
  -
    string = item.charAt(0)
    
      .toUpperCase() +
    item.slice(1);
  li= string';

        $this->assertTokens($code, [
            CodeToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            NewLineToken::class,
            OutdentToken::class,
            CommentToken::class,
            TextToken::class,
            NewLineToken::class,
            CodeToken::class,
            NewLineToken::class,
            EachToken::class,
            NewLineToken::class,
            IndentToken::class,
            CodeToken::class,
            NewLineToken::class,
            IndentToken::class,
            TextToken::class,
            NewLineToken::class,
            OutdentToken::class,
            TagToken::class,
            ExpressionToken::class,
        ]);
    }
}
