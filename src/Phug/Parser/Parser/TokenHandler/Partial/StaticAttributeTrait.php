<?php

namespace Phug\Parser\TokenHandler\Partial;

use Phug\Lexer\Token\ClassToken;
use Phug\Lexer\Token\IdToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\AttributeNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\MixinCallNode;
use Phug\Parser\State;

trait StaticAttributeTrait
{
    /**
     * @param string             $name
     * @param IdToken|ClassToken $token
     * @param State              $state
     *
     * @return void
     */
    private function attachStaticAttribute($name, TokenInterface $token, State $state)
    {
        $this->onlyOnElement($token, $state);

        /** @var AttributeNode $attr */
        $attr = $state->createNode(AttributeNode::class, $token);
        $attr->setName($name);
        $attr->setValue(var_export($token->getName(), true));
        $attr->unescape()->uncheck();

        /** @var ElementNode|MixinCallNode $current */
        $current = $state->getCurrentNode();
        $current->getAttributes()->attach($attr);
    }
}
