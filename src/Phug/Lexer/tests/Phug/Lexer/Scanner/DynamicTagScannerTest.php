<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\AutoCloseToken;
use Phug\Lexer\Token\ClassToken;
use Phug\Lexer\Token\ExpansionToken;
use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\IdToken;
use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Lexer\Token\InterpolationStartToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Test\AbstractLexerTest;

class DynamicTagScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\DynamicTagScanner
     * @covers \Phug\Lexer\Scanner\DynamicTagScanner::scan
     */
    public function testUsualTagName()
    {
        /** @var ExpressionToken $tok */
        list(, $tok) = $this->assertTokens('#{"some-tag-name"}', [
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
        ]);

        self::assertSame('"some-tag-name"', $tok->getValue());
        self::assertTrue($tok->isEscaped());
        self::assertTrue($tok->isChecked());

        /** @var ExpressionToken $tok */
        list(, $tok) = $this->assertTokens('!#{"<b>"}', [
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
        ]);

        self::assertSame('"<b>"', $tok->getValue());
        self::assertFalse($tok->isEscaped());
        self::assertTrue($tok->isChecked());

        $template = "#{'foo'}/\n".
            "#{'foo'}(bar='baz')/\n".
            "#{'foo'} /\n".
            "#{'foo'}(bar='baz') /\n";
        $this->assertTokens($template, [
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            AutoCloseToken::class,
            NewLineToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            AutoCloseToken::class,
            NewLineToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            TextToken::class,
            NewLineToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
            TextToken::class,
            NewLineToken::class,
        ]);

        $template = "#{'foo'}\ta\n".
            "#{'foo'}: a\n".
            "#{'foo'} :a\n";
        $this->assertTokens($template, [
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            TextToken::class,
            NewLineToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            ExpansionToken::class,
            TagToken::class,
            NewLineToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            TextToken::class,
            NewLineToken::class,
        ]);

        $template = "#{'foo'}#a\n".
            "#{'foo'}.a\n".
            "#{\n".
            "  'foo'\n".
            "}/\n";
        $this->assertTokens($template, [
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            IdToken::class,
            NewLineToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            ClassToken::class,
            NewLineToken::class,
            InterpolationStartToken::class,
            ExpressionToken::class,
            InterpolationEndToken::class,
            AutoCloseToken::class,
            NewLineToken::class,
        ]);
    }
}
