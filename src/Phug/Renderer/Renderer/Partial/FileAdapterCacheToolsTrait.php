<?php

namespace Phug\Renderer\Partial;

use Phug\Renderer;

trait FileAdapterCacheToolsTrait
{
    protected function cacheFileContents($destination, $output, $importsMap = [])
    {
        $imports = file_put_contents(
            $destination.'.imports.serialize.txt',
            serialize($importsMap)
        ) ?: 0;
        $template = file_put_contents($destination, $output);

        return $template && $imports;
    }

    /**
     * @param Renderer $renderer
     * @param array    $events
     *
     * @throws \Phug\RendererException
     *
     * @return \Phug\CompilerInterface
     */
    protected function reInitCompiler(Renderer $renderer, array $events)
    {
        $renderer->initCompiler();
        $compiler = $renderer->getCompiler();
        $compiler->mergeEventListeners($events);

        return $compiler;
    }

    /**
     * Return directories list from a directory string as it is allowed in CLI:
     * - directory1/x/y
     * - [directory1/x/y,directory2/z]
     * as an array of strings.
     *
     * @param string $directory
     *
     * @return string[]
     */
    protected function parseCliDirectoriesInput($directory)
    {
        return array_filter(
            preg_match('/^\[(.*)]$/', $directory, $match)
                ? explode(',', $match[1])
                : [$directory],
            'strlen'
        );
    }
}
