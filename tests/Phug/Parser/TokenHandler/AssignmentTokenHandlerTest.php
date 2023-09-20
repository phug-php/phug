<?php

namespace Phug\Test\Parser\TokenHandler;

use Phug\Lexer;
use Phug\Lexer\Token\AssignmentToken;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Parser;
use Phug\Parser\Node\DocumentNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\AssignmentTokenHandler;
use Phug\Parser\TokenHandler\AttributeEndTokenHandler;
use Phug\Parser\TokenHandler\AttributeStartTokenHandler;
use Phug\Parser\TokenHandler\AttributeTokenHandler;
use Phug\Test\Utils\ExceptionAnnotationReader;
use Phug\Util\TestCase;

/**
 * @coversDefaultClass \Phug\Parser\TokenHandler\AssignmentTokenHandler
 */
class AssignmentTokenHandlerTest extends TestCase
{
    /**
     * @covers ::handleAssignmentToken
     */
    public function testHandleToken()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('&attributes($a)'), [
            'token_handlers' => [
                AssignmentToken::class     => AssignmentTokenHandler::class,
                AttributeStartToken::class => AttributeStartTokenHandler::class,
                AttributeEndToken::class   => AttributeEndTokenHandler::class,
                AttributeToken::class      => AttributeTokenHandler::class,
            ],
        ]);

        $state->handleToken();
        $assignments = [];
        foreach ($state->getCurrentNode()->getAssignments() as $assignment) {
            $assignments[] = $assignment;
        }

        self::assertSame(1, count($assignments));
        self::assertSame('attributes', $assignments[0]->getName());
    }

    /**
     * @covers ::handleAssignmentToken
     */
    public function testHandleTokenWithNothingNext()
    {
        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('&attributes'), [
            'token_handlers' => [
                AssignmentToken::class     => AssignmentTokenHandler::class,
                AttributeStartToken::class => AttributeStartTokenHandler::class,
                AttributeEndToken::class   => AttributeEndTokenHandler::class,
                AttributeToken::class      => AttributeTokenHandler::class,
            ],
        ]);

        $state->handleToken();
        $assignments = [];
        foreach ($state->getCurrentNode()->getAssignments() as $assignment) {
            $assignments[] = $assignment;
        }

        self::assertSame(1, count($assignments));
        self::assertSame('attributes', $assignments[0]->getName());
    }

    /**
     * @covers                   ::handleAssignmentToken
     * @covers                   \Phug\Parser\TokenHandler\AbstractTokenHandler::handleToken
     *
     * @expectedException        \RuntimeException
     *
     * @expectedExceptionMessage You can only pass assignment tokens to AssignmentTokenHandler
     */
    public function testHandleTokenTokenException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'));
        $handler = new AssignmentTokenHandler();
        $handler->handleToken(new AttributeToken(), $state);
    }

    /**
     * @covers                   ::handleAssignmentToken
     *
     * @expectedException        \Phug\ParserException
     *
     * @expectedExceptionMessage Failed to parse: Assignments can only happen on elements and mixin-calls
     */
    public function testHandleTokenElementTagsException()
    {
        ExceptionAnnotationReader::read($this, __METHOD__);

        $lexer = new Lexer();
        $state = new State(new Parser(), $lexer->lex('div'), [
            'token_handlers' => [
                AssignmentToken::class     => AssignmentTokenHandler::class,
                AttributeStartToken::class => AttributeStartTokenHandler::class,
                AttributeEndToken::class   => AttributeEndTokenHandler::class,
                AttributeToken::class      => AttributeTokenHandler::class,
            ],
        ]);

        $assignment = new AssignmentToken();
        $assignment->setName('foo');
        $state->setCurrentNode(new DocumentNode());
        $state->handleToken($assignment);
    }
}
