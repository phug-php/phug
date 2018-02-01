<?php

namespace Phug;

use Phug\Compiler\Locator\FileLocator;

class Optimizer
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $paths;

    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * @var FileLocator
     */
    private $locator;

    /**
     * Return a hashed print from input file or content.
     *
     * @param string $input
     *
     * @return string
     */
    private function hashPrint($input)
    {
        // Get the stronger hashing algorithm available to minimize collision risks
        $algorithms = hash_algos();
        $algorithm = $algorithms[0];
        $number = 0;
        foreach ($algorithms as $hashAlgorithm) {
            if (strpos($hashAlgorithm, 'md') === 0) {
                $hashNumber = substr($hashAlgorithm, 2);
                if ($hashNumber > $number) {
                    $number = $hashNumber;
                    $algorithm = $hashAlgorithm;
                }
                continue;
            }
            if (strpos($hashAlgorithm, 'sha') === 0) {
                $hashNumber = substr($hashAlgorithm, 3);
                if ($hashNumber > $number) {
                    $number = $hashNumber;
                    $algorithm = $hashAlgorithm;
                }
                continue;
            }
        }

        return rtrim(strtr(base64_encode(hash($algorithm, $input, true)), '+/', '-_'), '=');
    }

    /**
     * Returns true if the path has an expired imports linked.
     *
     * @param $path
     *
     * @return bool
     */
    private function hasExpiredImport($sourcePath, $cachePath)
    {
        $importsMap = $cachePath.'.imports.serialize.txt';

        if (!file_exists($importsMap)) {
            return true;
        }

        $importPaths = unserialize(file_get_contents($importsMap)) ?: [];
        $importPaths[] = $sourcePath;
        $time = filemtime($cachePath);
        foreach ($importPaths as $importPath) {
            if (!file_exists($importPath) || filemtime($importPath) >= $time) {
                // If only one file has changed, expires
                return true;
            }
        }

        // If only no files changed, it's up to date
        return false;
    }

    public function __construct(array $options = [])
    {
        $this->options = $options;
        $this->locator = new FileLocator();
        $this->paths = isset($options['paths']) ? $options['paths'] : [];
        if (isset($options['base_dir'])) {
            $this->paths[] = $options['base_dir'];
        }
        if (isset($options['basedir'])) {
            $this->paths[] = $options['basedir'];
        }
        $this->cacheDirectory = isset($options['cache_dir'])
            ? $options['cache_dir']
            : (isset($options['cache']) ? $options['cache'] : '')
        ;
    }

    public function resolve($file)
    {
        return $this->locator->locate(
            $file,
            $this->paths,
            isset($this->options['extensions'])
                ? $this->options['extensions']
                : ['', '.pug', '.jade']
        );
    }

    public function isExpired($file, &$cachePath)
    {
        if (isset($this->options['up_to_date_check']) && !$this->options['up_to_date_check']) {
            return false;
        }

        if (!$this->cacheDirectory) {
            return true;
        }

        $sourcePath = $this->resolve($file);
        $cachePath = rtrim($this->cacheDirectory, '\\/').DIRECTORY_SEPARATOR.$this->hashPrint($sourcePath).'.php';

        if (!file_exists($cachePath)) {
            return false;
        }

        return $this->hasExpiredImport($sourcePath, $cachePath);
    }

    public function displayFile($__pug_file, array $__pug_parameters = [])
    {
        if ($this->isExpired($__pug_file, $__pug_cache_file)) {
            exit('ici');
            Phug::displayFile($__pug_file, $__pug_parameters, $this->options);

            return;
        }

        extract($__pug_parameters);
        include $__pug_cache_file;
    }

    public function renderFile($file, array $parameters = [])
    {
        ob_start();
        $this->displayFile($file, $parameters);

        return ob_get_flush();
    }
}
