<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\ExpressionToken;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\State;

class ExpressionTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = ExpressionToken::class;

    public function handleExpressionToken(ExpressionToken $token, State $state)
    {
        /** @var ExpressionNode $node */
        $node = $state->createNode(ExpressionNode::class, $token);
        $node->setIsEscaped($token->isEscaped());
        $node->setIsChecked($token->isChecked());
        $node->setValue($token->getValue());

        $state->append($node);
    }
}
