<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\ConditionalToken;
use Phug\Parser\Node\ConditionalNode;
use Phug\Parser\State;

class ConditionalTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = ConditionalToken::class;

    public function handleConditionalToken(ConditionalToken $token, State $state)
    {
        /** @var ConditionalNode $node */
        $node = $state->createNode(ConditionalNode::class, $token);
        $node->setSubject($token->getSubject());
        $node->setName($token->getName());
        $state->setCurrentNode($node);
    }
}
