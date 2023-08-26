<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\DoctypeToken;
use Phug\Parser\Node\DoctypeNode;
use Phug\Parser\State;

class DoctypeTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = DoctypeToken::class;

    public function handleDoctypeToken(DoctypeToken $token, State $state)
    {
        /** @var DoctypeNode $node */
        $node = $state->createNode(DoctypeNode::class, $token);
        $node->setName($token->getName());
        $state->setCurrentNode($node);
    }
}
