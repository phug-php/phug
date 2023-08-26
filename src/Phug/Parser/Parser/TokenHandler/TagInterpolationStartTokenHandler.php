<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\TagInterpolationStartToken;
use Phug\Parser\Node\CodeNode;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\Node\TextNode;
use Phug\Parser\State;

class TagInterpolationStartTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = TagInterpolationStartToken::class;

    public function handleTagInterpolationStartToken(TagInterpolationStartToken $token, State $state)
    {
        $node = $state->getCurrentNode();

        if ($state->currentNodeIs([
            TextNode::class,
            CodeNode::class,
            ExpressionNode::class,
        ])) {
            $node = $node->getParent();
        }

        if ($node) {
            $state->pushInterpolationNode($node);
        }

        $state->getInterpolationStack()->attach($token->getEnd(), (object) [
            'currentNode' => $node,
            'parentNode'  => $state->getParentNode(),
        ]);
        $state->store();
    }
}
