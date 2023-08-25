<?php

namespace Phug\Lexer\Scanner\Partial;

use Phug\Lexer\Analyzer\LineAnalyzer;
use Phug\Lexer\State;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;

trait TrailingOutdentHandlerTrait
{
    private function yieldTrailingOutdent(LineAnalyzer $analyzer, State $state)
    {
        $reader = $state->getReader();

        if ($reader->hasLength()) {
            yield $state->createToken(NewLineToken::class);

            $state->setLevel($analyzer->getNewLevel())->indent($analyzer->getLevel() + 1);

            while ($state->nextOutdent() !== false) {
                yield $state->createToken(OutdentToken::class);
            }
        }
    }
}
