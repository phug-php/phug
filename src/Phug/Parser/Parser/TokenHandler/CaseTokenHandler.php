<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\CaseToken;
use Phug\Parser\Node\CaseNode;
use Phug\Parser\State;

class CaseTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = CaseToken::class;

    public function handleCaseToken(CaseToken $token, State $state)
    {
        /** @var CaseNode $node */
        $node = $state->createNode(CaseNode::class, $token);
        $node->setSubject($token->getSubject());
        $state->setCurrentNode($node);
    }
}
