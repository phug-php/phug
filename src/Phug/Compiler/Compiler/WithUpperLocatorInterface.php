<?php

namespace Phug\Compiler;

/**
 * Interface WithUpperLocatorInterface.
 *
 * An interface for object than can have an upper locator.
 */
interface WithUpperLocatorInterface
{
    /**
     * Set a master locator to use before the internal one.
     *
     * @param LocatorInterface|null $upperLocator
     */
    public function setUpperLocator($upperLocator);
}
