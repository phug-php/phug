<?php

/**
 * @example mixin my-mixin()
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\MixinToken;
use Phug\Util\OptionInterface;
use Phug\Util\Partial\OptionTrait;

class MixinScanner implements ScannerInterface, OptionInterface
{
    use OptionTrait;

    const KEYWORD_NAME = 'mixin';

    public function scan(State $state)
    {
        $keywordName = $this->hasOption('mixin_keyword') ? $this->getOption('mixin_keyword') : static::KEYWORD_NAME;

        if (is_array($keywordName)) {
            $keywordName = '(?:'.implode('|', $keywordName).')';
        }

        foreach ($state->scanToken(
            MixinToken::class,
            $keywordName."[\t ]+(?<name>[a-zA-Z_][a-zA-Z0-9\-_]*)"
        ) as $token) {
            yield $token;

            $reader = $state->getReader();
            if ($reader->match('[\t ]+(?=\()')) {
                $reader->consume();
            }

            foreach ($state->scan(SubScanner::class) as $subToken) {
                yield $subToken;
            }
        }
    }
}
