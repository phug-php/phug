<?php

namespace Phug\Test;

/**
 * @coversDefaultClass \Phug\AbstractExtension
 */
class ExtensionTest extends AbstractPhugTest
{
    /**
     * @covers ::<public>
     */
    public function testGetters()
    {
        static::assertTrue(is_array($this->verbatim->getParameters()));
        static::assertTrue(is_array($this->verbatim->getMixins()));
        static::assertTrue(is_array($this->verbatim->getBlocks()));
        static::assertTrue(is_array($this->verbatim->getTokens()));
        static::assertTrue(is_array($this->verbatim->getScanners()));
        static::assertTrue(is_array($this->verbatim->getNodes()));
        static::assertTrue(is_array($this->verbatim->getFilters()));
        static::assertTrue(is_array($this->verbatim->getHandlers()));
        static::assertTrue(is_array($this->verbatim->getElements()));
        static::assertTrue(is_array($this->verbatim->getFormats()));
        static::assertTrue(is_array($this->verbatim->getAssignmentHandlers()));
        static::assertTrue(is_array($this->verbatim->getPatterns()));
        static::assertTrue(is_array($this->verbatim->getAttributeHandlers()));
        static::assertTrue(is_array($this->verbatim->getPathResolvers()));
        static::assertTrue(is_array($this->verbatim->getTranslators()));
        static::assertTrue(is_array($this->verbatim->getAdapters()));
    }
}
