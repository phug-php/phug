<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\WhileToken;
use Phug\Parser\Node\WhileNode;
use Phug\Parser\State;

class WhileTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = WhileToken::class;

    public function handleWhileToken(WhileToken $token, State $state)
    {
        /** @var WhileNode $node */
        $node = $state->createNode(WhileNode::class, $token);
        $node->setSubject($token->getSubject());
        $state->setCurrentNode($node);
    }
}
