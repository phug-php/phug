<?php

namespace Phug\Partial;

use Exception;
use Phug\Invoker;
use Phug\Util\Collection;
use ReflectionException;

trait TokenGeneratorTrait
{
    /**
     * Get a iterable list of output tokens from a list of interceptors and input tokens.
     *
     * @param callable[] $callbacks list of callable interceptors
     * @param iterable   $tokens    input tokens
     *
     * @throws Exception
     * @throws ReflectionException
     *
     * @return iterable
     */
    protected function getTokenGenerator($callbacks, $tokens)
    {
        if (count($callbacks) === 0) {
            return $tokens;
        }

        $callback = array_shift($callbacks);

        foreach ($tokens as $token) {
            $result = is_a($token, Invoker::getCallbackType($callback)) ? $callback($token) : null;
            $result = (new Collection($result ?: $token))->getIterable();

            return $this->getTokenGenerator($callbacks, $result);
        }
    }
}
