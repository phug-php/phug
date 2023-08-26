<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\AttributeToken;
use Phug\Parser\Node\AssignmentNode;
use Phug\Parser\Node\AttributeNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\MixinCallNode;
use Phug\Parser\State;
use Phug\Util\AttributesOrderInterface;
use Phug\Util\OrderableInterface;

class AttributeTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = AttributeToken::class;

    public function handleAttributeToken(AttributeToken $token, State $state)
    {
        $this->createElementNodeIfMissing($token, $state);

        /** @var AttributeNode $node */
        $node = $state->createNode(AttributeNode::class, $token);
        $name = $token->getName();
        $value = $token->getValue();
        $node->setName($name);
        $node->setValue($value);
        $node->setIsEscaped($token->isEscaped());
        $node->setIsChecked($token->isChecked());
        $node->setIsVariadic($token->isVariadic());

        // Mixin calls and assignments take the first
        // expression set as the name as the value
        if (($value === '' || $value === null) &&
            (
                $state->currentNodeIs([AssignmentNode::class]) ||
                ($state->currentNodeIs([MixinCallNode::class]) && !$state->getCurrentNode()->areArgumentsCompleted())
            )
        ) {
            $node->setValue($name);
            $node->setName(null);
        }

        /** @var ElementNode|MixinCallNode|AssignmentNode $current */
        $current = $state->getCurrentNode();

        if ($current instanceof AttributesOrderInterface) {
            $node->setOrder($current->getNextAttributeIndex());
        } elseif ($current instanceof OrderableInterface) {
            $node->setOrder($current->getOrder());
        }

        $current->getAttributes()->attach($node);
    }
}
