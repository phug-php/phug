<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\BlockToken;
use Phug\Parser\Node\BlockNode;
use Phug\Parser\State;

class BlockTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = BlockToken::class;

    public function handleBlockToken(BlockToken $token, State $state)
    {
        /** @var BlockNode $node */
        $node = $state->createNode(BlockNode::class, $token);
        $node->setName($token->getName());
        $node->setMode($token->getMode());
        $state->setCurrentNode($node);
    }
}
