<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\ExpansionToken;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\State;

class ExpansionTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = ExpansionToken::class;

    public function handleExpansionToken(ExpansionToken $token, State $state)
    {
        if (!$state->getCurrentNode()) {
            $state->throwException(
                'Expansion needs an element to work on',
                0,
                $token
            );
        }

        if ($state->getInterpolationNode()) {
            //Make sure to keep the expansion
            $newNode = $state->createNode(ElementNode::class, $token);
            $newNode->setOuterNode($state->getCurrentNode());
            $state->setCurrentNode($newNode);

            return;
        }

        //Make sure to keep the expansion saved
        if ($state->getOuterNode()) {
            $state->getCurrentNode()->setOuterNode($state->getOuterNode());
        }

        $state->setOuterNode($state->getCurrentNode());
        $state->setCurrentNode(null);
    }
}
