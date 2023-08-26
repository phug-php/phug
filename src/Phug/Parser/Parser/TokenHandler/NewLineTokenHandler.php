<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\NewLineToken;
use Phug\Parser\State;

class NewLineTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = NewLineToken::class;

    public function handleNewLineToken(NewLineToken $token, State $state)
    {
        $state->store();
    }
}
