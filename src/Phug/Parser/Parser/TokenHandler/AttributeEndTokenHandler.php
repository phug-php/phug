<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\AttributeEndToken;

class AttributeEndTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = AttributeEndToken::class;

    public function handleAttributeEndToken()
    {
        // noop
    }
}
