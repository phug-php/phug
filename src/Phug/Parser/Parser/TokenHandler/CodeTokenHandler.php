<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\CodeToken;
use Phug\Lexer\Token\TextToken;
use Phug\Parser\Node\CodeNode;
use Phug\Parser\State;

class CodeTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = CodeToken::class;

    public function handleCodeToken(CodeToken $token, State $state)
    {
        /** @var CodeNode $node */
        $node = $state->createNode(CodeNode::class, $token);

        if ($state->getCurrentNode()) {
            $token = $state->expectNext([TextToken::class]);
            if (!$token) {
                $state->throwException(
                    'Unexpected token `blockcode` expected `text`, `interpolated-code` or `code`',
                    0,
                    $token
                );
            }
            $node->setValue($token->getValue());
        }

        $state->append($node);
    }
}
