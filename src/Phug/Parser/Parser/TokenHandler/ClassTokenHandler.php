<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\ClassToken;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\Partial\StaticAttributeTrait;

class ClassTokenHandler extends AbstractTokenHandler
{
    use StaticAttributeTrait;

    const TOKEN_TYPE = ClassToken::class;

    public function handleClassToken(ClassToken $token, State $state)
    {
        $this->attachStaticAttribute('class', $token, $state);
    }
}
