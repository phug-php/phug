<?php

namespace Phug\Compiler;

/**
 * Interface NormalizerInterface.
 *
 * An interface for paths normalization.
 */
interface NormalizerInterface
{
    /**
     * Normalize the string of a relative or absolute path.
     *
     * @param string $path the path to normalize.
     *
     * @return string
     */
    public function normalize($path);
}
