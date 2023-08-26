<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\IdToken;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\Partial\StaticAttributeTrait;

class IdTokenHandler extends AbstractTokenHandler
{
    use StaticAttributeTrait;

    const TOKEN_TYPE = IdToken::class;

    public function handleIdToken(IdToken $token, State $state)
    {
        $this->attachStaticAttribute('id', $token, $state);
    }
}
