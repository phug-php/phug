<?php

namespace Phug\Test;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;

class MockScanner implements ScannerInterface
{
    protected $state;
    protected $lexer;
    protected $sendBadTokens = false;

    public function setLexer($lexer)
    {
        $this->lexer = $lexer;
    }

    public function scan(State $state)
    {
        if ($this->lexer) {
            $this->state = $this->lexer->getState();
        }

        if ($this->sendBadTokens) {
            return [(object) []];
        }

        return [];
    }

    public function getState()
    {
        return $this->state;
    }

    public function badTokens()
    {
        $this->sendBadTokens = true;
    }
}
