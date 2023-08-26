<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\MixinToken;
use Phug\Parser\Node\MixinNode;
use Phug\Parser\State;

class MixinTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = MixinToken::class;

    public function handleMixinToken(MixinToken $token, State $state)
    {
        /** @var MixinNode $node */
        $node = $state->createNode(MixinNode::class, $token);
        $node->setName($token->getName());
        $state->setCurrentNode($node);
    }
}
