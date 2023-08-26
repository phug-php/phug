<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\IndentToken;
use Phug\Parser\State;

class IndentTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = IndentToken::class;

    public function handleIndentToken(IndentToken $token, State $state)
    {
        $state->enter();
    }
}
