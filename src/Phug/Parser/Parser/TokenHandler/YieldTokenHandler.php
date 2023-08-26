<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\YieldToken;
use Phug\Parser\Node\YieldNode;
use Phug\Parser\State;

class YieldTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = YieldToken::class;

    public function handleYieldToken(YieldToken $token, State $state)
    {
        $state->setCurrentNode($state->createNode(YieldNode::class, $token));
    }
}
