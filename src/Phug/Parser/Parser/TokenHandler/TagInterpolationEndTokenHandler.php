<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\TagInterpolationEndToken;
use Phug\Parser\State;

class TagInterpolationEndTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = TagInterpolationEndToken::class;

    public function handleTagInterpolationEndToken(TagInterpolationEndToken $token, State $state)
    {
        $state->popInterpolationNode();
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
