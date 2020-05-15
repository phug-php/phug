<?php

/**
 * @example p Text #{'interpolation'} text
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Lexer\Token\InterpolationStartToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\TagInterpolationEndToken;
use Phug\Lexer\Token\TagInterpolationStartToken;
use Phug\Lexer\Token\TextToken;
use RuntimeException;

class InterpolationScanner implements ScannerInterface
{
    protected function scanInterpolation(State $state, $tagInterpolation, $interpolation, $escape)
    {
        if (strpos($interpolation, "\n") !== false) {
            $state->throwException('End of line was reached with no closing bracket for interpolation.');
        }

        if ($tagInterpolation) {
            /** @var TagInterpolationStartToken $start */
            $start = $state->createToken(TagInterpolationStartToken::class);
            /** @var TagInterpolationEndToken $end */
            $end = $state->createToken(TagInterpolationEndToken::class);

            $start->setEnd($end);
            $end->setStart($start);

            $lexer = $state->getLexer();

            yield $start;

            foreach ($lexer->lex($tagInterpolation) as $token) {
                if ($token instanceof NewLineToken) {
                    $state->throwException('End of line was reached with no closing bracket for interpolation.');
                }

                yield $token;
            }

            yield $end;

            return;
        }

        /** @var InterpolationStartToken $start */
        $start = $state->createToken(InterpolationStartToken::class);
        /** @var InterpolationEndToken $end */
        $end = $state->createToken(InterpolationEndToken::class);

        $start->setEnd($end);
        $end->setStart($start);

        /** @var ExpressionToken $token */
        $token = $state->createToken(ExpressionToken::class);
        $token->setValue($interpolation);

        if ($escape === '#') {
            $token->escape();
        }

        yield $start;
        yield $token;
        yield $end;
    }

    protected function needSeparationBlankLine(State $state)
    {
        $reader = $state->getReader();

        if (!$reader->peekNewLine()) {
            return false;
        }

        $indentWidth = $state->getIndentWidth();
        $indentation = $indentWidth > 0 ? $state->getIndentStyle().'{'.$state->getIndentWidth().'}' : '';

        return $reader->match('\n*'.$indentation.'\|');
    }

    public function scan(State $state)
    {
        $reader = $state->getReader();

        //TODO: $state->endToken
        while ($reader->match(
            '(?<text>.*?)'.
            '(?<!\\\\)'.
            '(?<escape>#|!(?=\{))(?<wrap>'.
                '\\[(?<tagInterpolation>'.
                    '(?>"(?:\\\\[\\S\\s]|[^"\\\\])*"|\'(?:\\\\[\\S\\s]|[^\'\\\\])*\'|[^\\[\\]\'"]++|(?-2))*+'.
                ')\\]|'.
                '\\{(?<interpolation>'.
                    '(?>"(?:\\\\[\\S\\s]|[^"\\\\])*"|\'(?:\\\\[\\S\\s]|[^\'\\\\])*\'|[^{}\'"]++|(?-3))*+'.
                ')\\}'.
            ')'
        )) {
            $text = $reader->getMatch('text');
            $text = preg_replace('/\\\\([#!]\\[|#\\{)/', '$1', $text);

            if (mb_strlen($text) > 0) {
                /** @var TextToken $token */
                $token = $state->createToken(TextToken::class);
                $token->setValue($text);

                yield $token;
            }

            foreach ($this->scanInterpolation(
                $state,
                $reader->getMatch('tagInterpolation'),
                $reader->getMatch('interpolation'),
                $reader->getMatch('escape')
            ) as $token) {
                yield $token;
            }

            $reader->consume();

            if ($this->needSeparationBlankLine($state)) {
                /** @var TextToken $token */
                $token = $state->createToken(TextToken::class);
                $token->setValue("\n");
                yield $token;
            }
        }
    }
}
