<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\WhenToken;
use Phug\Parser\Node\WhenNode;
use Phug\Parser\State;

class WhenTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = WhenToken::class;

    public function handleWhenToken(WhenToken $token, State $state)
    {
        /** @var WhenNode $node */
        $node = $state->createNode(WhenNode::class, $token);
        $node->setSubject($token->getSubject());
        $node->setName($token->getName());
        $state->setCurrentNode($node);
    }
}
