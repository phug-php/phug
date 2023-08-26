<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\KeywordToken;
use Phug\Parser\Node\KeywordNode;
use Phug\Parser\State;

class KeywordTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = KeywordToken::class;

    public function handleKeywordToken(KeywordToken $token, State $state)
    {
        /** @var KeywordNode $node */
        $node = $state->createNode(KeywordNode::class, $token);
        $node->setName($token->getName());
        $node->setValue($token->getValue());
        $state->setCurrentNode($node);
    }
}
