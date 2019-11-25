<?php

namespace Phug\Renderer\Partial;

trait RegistryTrait
{
    protected function getRegistryPathChunks($source, $directoryIndex = null)
    {
        $paths = explode('/', $source);
        $lastIndex = count($paths) - 1;

        foreach ($paths as $index => $path) {
            yield ($index < $lastIndex ? 'd:' : 'f:').$path;
        }

        if ($directoryIndex !== null) {
            yield 'i:'.$directoryIndex;
        }
    }

    /**
     * Return the first value indexed with "i:" prefix as raw cash path.
     *
     * @param array|null $registry registry result
     *
     * @return string|false
     */
    protected function getFirstRegistryIndex($registry)
    {
        foreach (((array) $registry) as $index => $value) {
            if (substr($index, 0, 2) === 'i:') {
                return $value;
            }
        }

        return false;
    }

    /**
     * Find the path of a cached file for a given path in a given registry.
     *
     * @param string $path
     * @param array  $registry
     *
     * @return string|false
     */
    protected function findCachePathInRegistry($path, $registry)
    {
        foreach ($this->getRegistryPathChunks($path) as $key) {
            if (!isset($registry[$key])) {
                return false;
            }

            $registry = $registry[$key];
        }

        if (is_string($registry)) {
            return $registry;
        }

        return $this->getFirstRegistryIndex($registry);
    }

    /**
     * Find the path of a cached file for a given path in a given registry file (that may not exist).
     *
     * @param string $path
     * @param string $registryFile
     *
     * @return string|false
     */
    protected function findCachePathInRegistryFile($path, $registryFile)
    {
        if (!file_exists($registryFile)) {
            return false;
        }

        return $this->findCachePathInRegistry($path, include $registryFile);
    }
}
