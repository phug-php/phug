<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\ImportToken;
use Phug\Parser\Node\CommentNode;
use Phug\Parser\Node\ImportNode;
use Phug\Parser\Node\MixinNode;
use Phug\Parser\NodeInterface;
use Phug\Parser\State;

class ImportTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = ImportToken::class;

    public function handleImportToken(ImportToken $token, State $state)
    {
        if ($token->getName() === 'extend' && !$this->isEmptyDocument($state->getDocumentNode())) {
            $state->throwException(
                'extends should be the very first statement in a document',
                0,
                $token
            );
        }

        /** @var ImportNode $node */
        $node = $state->createNode(ImportNode::class, $token);
        $node->setName($token->getName());
        $node->setPath($token->getPath());
        $state->setCurrentNode($node);
    }

    protected function isEmptyDocument(NodeInterface $document)
    {
        foreach ($document->getChildren() as $child) {
            if (!($child instanceof MixinNode || ($child instanceof CommentNode && !$child->isVisible()))) {
                return false;
            }
        }

        return true;
    }
}
