<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\OutdentToken;
use Phug\Parser\State;

class OutdentTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = OutdentToken::class;

    public function handleOutdentToken(OutdentToken $token, State $state)
    {
        $state->leave();
    }
}
