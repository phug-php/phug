<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Parser\Node\AssignmentNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\FilterNode;
use Phug\Parser\Node\ImportNode;
use Phug\Parser\Node\MixinCallNode;
use Phug\Parser\Node\MixinNode;
use Phug\Parser\Node\VariableNode;
use Phug\Parser\State;

class AttributeStartTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = AttributeStartToken::class;

    public function handleAttributeStartToken(AttributeStartToken $token, State $state)
    {
        $this->createElementNodeIfMissing($token, $state);
        $this->assertCurrentNodeIs($token, $state, [
            ElementNode::class, AssignmentNode::class,
            ImportNode::class, VariableNode::class,
            MixinNode::class, MixinCallNode::class,
            FilterNode::class,
        ]);

        foreach ($state->lookUpNext([AttributeToken::class]) as $subToken) {
            $state->handleToken($subToken);
        }

        if (!$state->expect([AttributeEndToken::class])) {
            $state->throwException(
                'Attribute list not closed',
                0,
                $token
            );
        }

        if ($state->currentNodeIs([MixinCallNode::class])) {
            /** @var MixinCallNode $mixinCall */
            $mixinCall = $state->getCurrentNode();

            $mixinCall->markArgumentsAsComplete();
        }
    }
}
