<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\ForToken;
use Phug\Parser\Node\ForNode;
use Phug\Parser\State;

class ForTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = ForToken::class;

    public function handleForToken(ForToken $token, State $state)
    {
        /** @var ForNode $node */
        $node = $state->createNode(ForNode::class, $token);
        $node->setSubject($token->getSubject());
        $state->setCurrentNode($node);
    }
}
