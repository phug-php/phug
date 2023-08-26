<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Lexer\Token\InterpolationStartToken;
use Phug\Parser\Node\CodeNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\Node\TextNode;
use Phug\Parser\State;

class InterpolationStartTokenHandler extends AbstractTokenHandler
{
    const TOKEN_TYPE = InterpolationStartToken::class;

    public function handleInterpolationStartToken(InterpolationStartToken $token, State $state)
    {
        $node = $state->getCurrentNode();

        if (!$node && !($state->getPreviousToken() instanceof InterpolationEndToken)) {
            $this->handleExpressionTokens($token, $state);

            return;
        }

        if ($state->currentNodeIs([
            TextNode::class,
            CodeNode::class,
            ExpressionNode::class,
        ])) {
            $node = $node->getParent();
        }

        $state->getInterpolationStack()->attach($token->getEnd(), (object) [
            'currentNode' => $node,
            'parentNode'  => $state->getParentNode(),
        ]);

        $state->store();
    }

    private function handleExpressionTokens(InterpolationStartToken $token, State $state)
    {
        /** @var ElementNode $element */
        $element = $state->createNode(ElementNode::class, $token);
        $state->setCurrentNode($element);

        foreach ($state->lookUpNext([ExpressionToken::class]) as $expression) {
            /** @var ExpressionNode $expressionNode */
            $expressionNode = $state->createNode(ExpressionNode::class, $expression);
            $expressionNode->check();
            $expressionNode->unescape();
            $expressionNode->setValue($expression->getValue());
            $element->setName($expressionNode);
        }

        if (!$state->expect([InterpolationEndToken::class])) {
            $state->throwException(
                'Interpolation not properly closed',
                0,
                $token
            );
        }
    }
}
