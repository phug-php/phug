<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\AssignmentToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Parser\Node\AssignmentNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\MixinCallNode;
use Phug\Parser\State;

class AssignmentTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = AssignmentToken::class;

    public function handleAssignmentToken(AssignmentToken $token, State $state)
    {
        $this->onlyOnElement($token, $state);

        /** @var AssignmentNode $node */
        $node = $state->createNode(AssignmentNode::class, $token);
        $node->setName($token->getName());

        /** @var ElementNode|MixinCallNode $current */
        $current = $state->getCurrentNode();
        $node->setOrder($current->getNextAttributeIndex());
        $current->getAssignments()->attach($node);

        if ($state->expectNext([AttributeStartToken::class])) {
            $state->setCurrentNode($node);
            //Will trigger iteration of consecutive attribute tokens
            //in AtttributeStartTokenHandler->handleToken with $node as the target ($currentNode in State)
            $state->handleToken();
            $state->setCurrentNode($current);
        }
    }
}
