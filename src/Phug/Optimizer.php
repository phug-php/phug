<?php

namespace Phug;

use Phug\Compiler\Locator\FileLocator;

class Optimizer
{
    const FACADE = Phug::class;

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
        $this->locator = new FileLocator();
        $this->paths = isset($options['paths']) ? $options['paths'] : [];
        if (isset($options['base_dir'])) {
            $this->paths[] = $options['base_dir'];
            unset($options['base_dir']);
            $options['paths'] = $this->paths;
        }
        if (isset($options['basedir'])) {
            $this->paths[] = $options['basedir'];
            unset($options['basedir']);
            $options['paths'] = $this->paths;
        }
        if (isset($options['cache']) && !isset($options['cache_dir'])) {
            $options['cache_dir'] = $options['cache'];
        }
        $this->options = $options;
        $this->cacheDirectory = isset($options['cache_dir']) ? $options['cache_dir'] : '';
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
            return true;
        }

        return $this->hasExpiredImport($sourcePath, $cachePath);
    }

    public function displayFile($__pug_file, array $__pug_parameters = [])
    {
        if ($this->isExpired($__pug_file, $__pug_cache_file)) {
            if (isset($this->options['render'])) {
                call_user_func($this->options['render'], $__pug_file, $__pug_parameters, $this->options);

                return;
            }
            if (isset($this->options['renderer'])) {
                $this->options['renderer']->displayFile($__pug_file, $__pug_parameters);

                return;
            }
            if (isset($this->options['renderer_class'])) {
                $className = $this->options['renderer_class'];
                $renderer = new $className($this->options);
                $renderer->displayFile($__pug_file, $__pug_parameters);

                return;
            }
            $facade = isset($this->options['facade']) ? $this->options['facade'] : static::FACADE;
            if (method_exists($facade, 'displayFile')) {
                (static::FACADE)::displayFile($__pug_file, $__pug_parameters, $this->options);

                return;
            }

            throw new \RuntimeException(
                'No valid render method, renderer engine, renderer class or facade provided.'
            );
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
