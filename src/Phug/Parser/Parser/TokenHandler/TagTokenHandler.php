<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\TagToken;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\State;

class TagTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = TagToken::class;

    public function handleTagToken(TagToken $token, State $state)
    {
        $this->createElementNodeIfMissing($token, $state);
        $this->assertCurrentNodeIs($token, $state, [ElementNode::class]);

        /** @var ElementNode $current */
        $current = $state->getCurrentNode();

        if ($current->getName()) {
            $state->throwException(
                'The element already has a tag name',
                0,
                $token
            );
        }

        $current->setName($token->getName());
    }
}
