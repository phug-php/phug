<?php

namespace Phug\Test\Lexer;

use PHPUnit\Framework\TestCase;
use Phug\Lexer;
use Phug\Lexer\Token\AssignmentToken;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Lexer\TokenInterface;
use Phug\Util\SourceLocation;

class TokenTest extends TestCase
{
    /**
     * @covers \Phug\Lexer\AbstractToken::getSourceLocation
     * @covers \Phug\Lexer\State::endToken
     * @covers \Phug\Lexer\State::createCurrentSourceLocation
     */
    public function testSourceLocation()
    {
        $lexer = new Lexer();
        $tokens = [];

        foreach ($lexer->lex('p Test') as $token) {
            $tokens[] = $token;
        }

        self::assertCount(2, $tokens);
        /** @var TagToken $token */
        $token = $tokens[0];
        self::assertInstanceOf(TagToken::class, $token);
        self::assertSame(1, $token->getSourceLocation()->getLine());
        self::assertSame(1, $token->getSourceLocation()->getOffset());
        self::assertSame(1, $token->getSourceLocation()->getOffsetLength());
        /** @var TextToken $token */
        $token = $tokens[1];
        self::assertInstanceOf(TextToken::class, $token);
        self::assertSame(1, $token->getSourceLocation()->getLine());
        self::assertSame(2, $token->getSourceLocation()->getOffset());
        self::assertSame(5, $token->getSourceLocation()->getOffsetLength());
    }

    /**
     * @covers \Phug\Lexer\Token\AssignmentToken
     * @covers \Phug\Lexer\Token\AssignmentToken::setName
     * @covers \Phug\Lexer\Token\AssignmentToken::getName
     */
    public function testAssignmentToken()
    {
        /** @var AssignmentToken $tok */
        $tok = $this->createAndTestToken(AssignmentToken::class);

        self::assertNull($tok->getName());

        $tok->setName('some name');
        self::assertSame('some name', $tok->getName());
    }

    /**
     * @covers \Phug\Lexer\AbstractToken::__construct
     * @covers \Phug\Lexer\Token\AttributeStartToken
     * @covers \Phug\Lexer\Token\AttributeToken
     * @covers \Phug\Lexer\Token\AttributeToken::setName
     * @covers \Phug\Lexer\Token\AttributeToken::getName
     * @covers \Phug\Lexer\Token\AttributeToken::setValue
     * @covers \Phug\Lexer\Token\AttributeToken::getValue
     * @covers \Phug\Lexer\Token\AttributeToken::escape
     * @covers \Phug\Lexer\Token\AttributeToken::unescape
     * @covers \Phug\Lexer\Token\AttributeToken::setIsEscaped
     * @covers \Phug\Lexer\Token\AttributeToken::check
     * @covers \Phug\Lexer\Token\AttributeToken::uncheck
     * @covers \Phug\Lexer\Token\AttributeToken::setIsChecked
     * @covers \Phug\Lexer\Token\AttributeEndToken
     */
    public function testAttributeTokens()
    {
        $this->createAndTestToken(AttributeStartToken::class);

        /** @var AttributeToken $tok */
        $tok = $this->createAndTestToken(AttributeToken::class);

        self::assertNull($tok->getName());

        $tok->setName('some name');
        self::assertSame('some name', $tok->getName());

        self::assertNull($tok->getValue());

        $tok->setValue('some value');
        self::assertSame('some value', $tok->getValue());

        self::assertFalse($tok->isEscaped());

        $tok->escape();
        self::assertTrue($tok->isEscaped());

        $tok->unescape();
        self::assertFalse($tok->isEscaped());

        $tok->setIsEscaped(true);
        self::assertTrue($tok->isEscaped());

        self::assertTrue($tok->isChecked());

        $tok->uncheck();
        self::assertFalse($tok->isChecked());

        $tok->check();
        self::assertTrue($tok->isChecked());

        $tok->setIsChecked(false);
        self::assertFalse($tok->isChecked());

        $this->createAndTestToken(AttributeEndToken::class);
    }

    protected function createAndTestToken($className)
    {
        $line = mt_rand(0, 100);
        $offset = mt_rand(0, 100);
        $level = mt_rand(0, 8);

        /** @var TokenInterface $tok */
        $tok = new $className(new SourceLocation(null, $line, $offset), $level);

        self::assertInstanceOf($className, $tok, 'instance created');
        self::assertSame($line, $tok->getSourceLocation()->getLine(), "{$className}->getLine");
        self::assertSame($offset, $tok->getSourceLocation()->getOffset(), "{$className}->getOffset");
        self::assertSame($level, $tok->getLevel(), "{$className}->getLevel");

        return $tok;
    }
}
