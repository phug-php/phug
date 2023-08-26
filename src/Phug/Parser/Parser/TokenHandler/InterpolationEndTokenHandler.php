<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Parser\State;

class InterpolationEndTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = InterpolationEndToken::class;

    public function handleInterpolationEndToken(InterpolationEndToken $token, State $state)
    {
        $node = $state->getCurrentNode();
        $nodes = $state->getInterpolationStack()->offsetGet($token);
        $state->setCurrentNode($nodes->currentNode);
        $state->setParentNode($nodes->parentNode);
        if ($node) {
            $state->getInterpolationStack()->attach($node, $token);
            $state->append($node);
        }
        $state->store();
        $state->setCurrentNode($nodes->currentNode);
    }
}
