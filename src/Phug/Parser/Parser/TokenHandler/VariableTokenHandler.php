<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\VariableToken;
use Phug\Parser\Node\VariableNode;
use Phug\Parser\State;

class VariableTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = VariableToken::class;

    public function handleVariableToken(VariableToken $token, State $state)
    {
        /** @var VariableNode $node */
        $node = $state->createNode(VariableNode::class, $token);
        $node->setName($token->getName());
        $state->setCurrentNode($node);
    }
}
