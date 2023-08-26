<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\CommentToken;
use Phug\Parser\Node\CommentNode;
use Phug\Parser\State;

class CommentTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = CommentToken::class;

    public function handleCommentToken(CommentToken $token, State $state)
    {
        /** @var CommentNode $node */
        $node = $state->createNode(CommentNode::class, $token);
        $node->setIsVisible($token->isVisible());
        $state->setCurrentNode($node);
    }
}
