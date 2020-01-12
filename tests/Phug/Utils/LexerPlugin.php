<?php

namespace Phug\Test\Utils;

use Phug\AbstractPlugin;
use Phug\Lexer\Token\IdToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;

class LexerPlugin extends AbstractPlugin
{
    public function onTextToken(TextToken $text)
    {
        $tag = new TagToken($text->getSourceLocation(), $text->getLevel(), $text->getIndentation());
        $tag->setName('p');

        yield $tag;
        yield $text;
    }

    public function onTagToken(IdToken $tag)
    {
        $tag->setName('joker');
    }
}
