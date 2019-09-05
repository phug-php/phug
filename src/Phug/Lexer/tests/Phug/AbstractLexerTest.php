<?php

namespace Phug\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use Phug\Lexer;

abstract class AbstractLexerTest extends TestCase
{
    /** @var Lexer */
    protected $lexer;

    public function setUp()
    {
        parent::setUp();

        $this->lexer = $this->createLexer();
    }

    protected function expectMessageToBeThrown($message)
    {
        if (method_exists($this, 'expectExceptionMessage')) {
            $this->expectExceptionMessage($message);

            return;
        }

        $this->setExpectedException(Exception::class, $message, null);
    }

    protected function createLexer()
    {
        return new Lexer();
    }

    protected function filterTokenClass($className)
    {
        $className = ltrim($className, '\\');
        switch ($className) {
            case 'Phug\\Lexer\\Token\\IndentToken':
                return '[->]';
            case 'Phug\\Lexer\\Token\\OutdentToken':
                return '[<-]';
            case 'Phug\\Lexer\\Token\\NewLineToken':
                return '[\\n]';
            default:
                return preg_replace('/^(Phug\\\\.+)Token$/', '[$1]', $className);
        }
    }

    protected function assertTokens($expression, array $classNames, Lexer $lexer = null)
    {
        $lexer = $lexer ?: $this->lexer;
        $tokens = iterator_to_array($lexer->lex($expression));

        $table = str_pad('expected', 80, ' ', STR_PAD_RIGHT).'got';
        $lines = max(count($classNames), count($tokens));

        for ($i = 0; $i < $lines; $i++) {
            $expected = isset($classNames[$i]) ? $this->filterTokenClass($classNames[$i]) : '';
            $table .= "\n".(isset($tokens[$i])
                ? str_pad($expected, 80, ' ', STR_PAD_RIGHT).str_replace(
                    "\n",
                    "\n".str_repeat(' ', 80),
                    trim($this->lexer->dump($tokens[$i]))
                )
                : $expected
            );
        }

        self::assertSame(
            count($tokens),
            count($classNames),
            "\n"
            .'expected ('
            .implode(', ', array_map([$this, 'filterTokenClass'], $classNames))
            .'), '
            ."\n"
            .'got      ('
            .implode(', ', array_map('trim', array_map([$this->lexer, 'dump'], $tokens)))
            .")\n$table\n"
        );

        foreach ($tokens as $i => $token) {
            $isset = isset($classNames[$i]);
            self::assertTrue($isset, "Classname at $i exists");

            if ($isset) {
                self::assertInstanceOf($classNames[$i], $token, "token[$i] should be {$classNames[$i]}");
            }
        }

        return $tokens;
    }
}
