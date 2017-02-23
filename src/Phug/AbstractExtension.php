<?php

namespace Phug;

abstract class AbstractExtension implements ExtensionInterface
{
    public function __construct(Phug $phug)
    {
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getMixins()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getBlocks()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getScanners()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getNodes()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getHandlers()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getElements()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getFormats()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAssignmentHandlers()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getPatterns()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAttributeHandlers()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getPathResolvers()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getTranslators()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAdapters()
    {
        return [];
    }
}
