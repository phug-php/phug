<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\FilterToken;
use Phug\Parser\Node\FilterNode;
use Phug\Parser\Node\ImportNode;
use Phug\Parser\State;

class FilterTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = FilterToken::class;

    public function handleFilterToken(FilterToken $token, State $state)
    {
        /** @var FilterNode $node */
        $node = $state->createNode(FilterNode::class, $token);
        $node->setName($token->getName());
        $current = $state->getCurrentNode();
        if ($current instanceof ImportNode) {
            $current->setFilter($node);
            $node->setImport($current);
            $state->store();
        }
        $state->setCurrentNode($node);
    }
}
