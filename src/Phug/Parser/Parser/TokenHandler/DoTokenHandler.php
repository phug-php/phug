<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\DoToken;
use Phug\Parser\Node\DoNode;
use Phug\Parser\State;

class DoTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = DoToken::class;

    public function handleDoToken(DoToken $token, State $state)
    {
        $node = $state->createNode(DoNode::class, $token);
        $state->setCurrentNode($node);
    }
}
