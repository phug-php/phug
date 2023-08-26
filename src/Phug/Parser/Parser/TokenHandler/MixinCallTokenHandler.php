<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\MixinCallToken;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\Node\MixinCallNode;
use Phug\Parser\State;

class MixinCallTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = MixinCallToken::class;

    public function handleMixinCallToken(MixinCallToken $token, State $state)
    {
        /** @var MixinCallNode $node */
        $node = $state->createNode(MixinCallNode::class, $token);
        $name = $token->getName();
        if (preg_match('/^#\\{(.+)\\}$/', $name, $match)) {
            /** @var ExpressionNode $name */
            $name = $state->createNode(ExpressionNode::class);
            $name->setValue($match[1]);
            $name->setIsChecked(false);
            $name->setIsEscaped(false);
        }
        $node->setName($name);
        $state->setCurrentNode($node);
    }
}
