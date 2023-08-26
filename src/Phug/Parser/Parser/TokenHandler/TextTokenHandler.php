<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\TextToken;
use Phug\Parser\Node\TextNode;
use Phug\Parser\State;

class TextTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = TextToken::class;

    public function handleTextToken(TextToken $token, State $state)
    {
        /** @var TextNode $node */
        $node = $state->createNode(TextNode::class, $token);
        $node->setValue($token->getValue());
        $node->setLevel($token->getLevel());
        $node->setIsEscaped($token->isEscaped());
        $node->setIndent($token->getIndentation());

        $state->append($node);
    }
}
