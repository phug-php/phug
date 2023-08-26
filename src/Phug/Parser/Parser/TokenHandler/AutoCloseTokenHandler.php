<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\AutoCloseToken;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\State;

class AutoCloseTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = AutoCloseToken::class;

    public function handleAutoCloseToken(AutoCloseToken $token, State $state)
    {
        $this->assertCurrentNodeIs($token, $state, [ElementNode::class]);

        $state->getCurrentNode()->autoClose();
    }
}
