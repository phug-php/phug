<?php

/**
 * @example &attributes($var)
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\AssignmentToken;

class AssignmentScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        foreach ($state->scanToken(
            AssignmentToken::class,
            '&(?<name>[a-zA-Z_][a-zA-Z0-9\-_]*)'
        ) as $token) {
            yield $token;

            foreach ($state->scan(AttributeScanner::class, ['allow_name' => false]) as $attributeToken) {
                yield $attributeToken;
            }
        }
    }
}
