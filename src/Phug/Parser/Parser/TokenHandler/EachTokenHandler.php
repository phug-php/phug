<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\EachToken;
use Phug\Parser\Node\EachNode;
use Phug\Parser\State;

class EachTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = EachToken::class;

    public function handleEachToken(EachToken $token, State $state)
    {
        /** @var EachNode $node */
        $node = $state->createNode(EachNode::class, $token);
        $node->setSubject($token->getSubject());
        $node->setItem($token->getItem());
        $node->setKey($token->getKey());
        $state->setCurrentNode($node);
    }
}
