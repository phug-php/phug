<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\MixinCallNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

abstract class AbstractTokenHandler implements TokenHandlerInterface
{
    const TOKEN_TYPE = TokenInterface::class;

    public function handleToken(TokenInterface $token, State $state)
    {
        if (!is_a($token, static::TOKEN_TYPE)) {
            $name = $this->getTypeName(static::TOKEN_TYPE);
            $handler = $this->getClassLastPart(static::class);

            throw new \RuntimeException(
                "You can only pass $name tokens to $handler"
            );
        }

        $method = 'handle'.$this->getClassLastPart(get_class($token));
        $this->$method($token, $state);
    }

    protected function onlyOnElement(TokenInterface $token, State $state)
    {
        $this->createElementNodeIfMissing($token, $state);
        $this->assertCurrentNodeIs($token, $state, [ElementNode::class, MixinCallNode::class]);
    }

    protected function createElementNodeIfMissing(TokenInterface $token, State $state)
    {
        if (!$state->getCurrentNode()) {
            $state->setCurrentNode($state->createNode(ElementNode::class, $token));
        }
    }

    protected function assertCurrentNodeIs(TokenInterface $token, State $state, array $nodeTypes)
    {
        if (!$state->currentNodeIs($nodeTypes)) {
            $nodeTypes = array_values($nodeTypes);
            $list = $this->getPluralTypeName(array_shift($nodeTypes));
            $count = count($nodeTypes);

            foreach ($nodeTypes as $i => $nodeType) {
                $list .= ($i === $count - 1 ? ' and ' : ', ').$this->getPluralTypeName($nodeType);
            }

            $state->throwException(
                ucfirst($this->getPluralTypeName(get_class($token))).' can only happen on '.$list,
                0,
                $token
            );
        }
    }

    private function getClassLastPart($class)
    {
        $parts = explode('\\', $class);

        return end($parts);
    }

    private function getTypeName($class)
    {
        $name = preg_replace('/(Token|Node)$/', '', $this->getClassLastPart($class));
        $name = preg_replace('/([a-z])([A-Z])/', '$1-$2', $name);

        return strtolower($name);
    }

    private function getPluralTypeName($class)
    {
        $name = $this->getTypeName($class);

        if ($name === 'class') {
            return 'classes';
        }

        return $name.'s';
    }
}
