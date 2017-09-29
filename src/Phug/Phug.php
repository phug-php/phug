<?php

namespace Phug;

class Phug
{
    /**
     * @var array
     */
    private static $filters = [];

    /**
     * @var array
     */
    private static $keywords = [];

    /**
     * @var array
     */
    private static $extensions = [];

    /**
     * @var Renderer
     */
    private static $renderer = null;

    private static function normalizeFilterName($name)
    {
        return str_replace(' ', '-', strtolower($name));
    }

    private static function normalizeKeywordName($name)
    {
        return str_replace(' ', '-', strtolower($name));
    }

    private static function normalizeExtensionClassName($name)
    {
        return ltrim('\\', strtolower($name));
    }

    private static function getExtensionsGetters()
    {
        return [
            'includes'            => 'getIncludes',
            'scanners'            => 'getScanners',
            'token_handlers'      => 'getTokenHandlers',
            'node_compilers'      => 'getCompilers',
            'formats'             => 'getFormats',
            'patterns'            => 'getPatterns',
            'filters'             => 'getFilters',
            'keywords'            => 'getKeywords',
            'element_handlers'    => 'getElementHandlers',
            'php_token_handlers'  => 'getPhpTokenHandlers',
            'assignment_handlers' => 'getAssignmentHandlers',
        ];
    }

    private static function getOptions(array $options = [])
    {
        $extras = [];
        foreach (['filters', 'keywords'] as $option) {
            $method = 'get'.ucfirst($option);
            $extras[$option] = static::$method();
        }
        $methods = static::getExtensionsGetters();
        foreach (self::$extensions as $extensionClassName) {
            $extension = new $extensionClassName();
            foreach (['getOptions', 'getEvents'] as $method) {
                $value = $extension->$method();
                if (!empty($value)) {
                    $extras = array_merge_recursive($extras, $value);
                }
            }
            foreach ($methods as $option => $method) {
                $value = $extension->$method();
                if (!empty($value)) {
                    $extras = array_merge_recursive($extras, [$option => $value]);
                }
            }
        }

        return array_merge_recursive($extras, $options);
    }

    public static function removeOptions($path, $options)
    {
        if (self::$renderer && (empty($path) || self::$renderer->hasOption($path))) {
            if (is_array($options)) {
                foreach ($options as $key => $value) {
                    static::removeOptions(array_merge($path, [$key]), $value);
                }

                return;
            }

            self::$renderer->unsetOption($path);
        }
    }

    /**
     * Reset all static options, filters and extensions.
     */
    public static function reset()
    {
        self::$renderer = null;
        self::$extensions = [];
        self::$filters = [];
        self::$keywords = [];
    }

    /**
     * Get a renderer with global options and argument options merged.
     *
     * @param array $options
     *
     * @return Renderer
     */
    public static function getRenderer(array $options = [])
    {
        $options = static::getOptions($options);

        if (!self::$renderer) {
            self::$renderer = new Renderer($options);
        } elseif (!empty($options)) {
            self::$renderer->setOptions($options);
            self::$renderer->getCompiler()->getFormatter()->initFormats();
        }

        return self::$renderer;
    }

    /**
     * @param string $input      pug source
     * @param array  $parameters variables values
     * @param array  $options    custom options
     *
     * @return string
     */
    public static function render($input, array $parameters = [], array $options = [])
    {
        return static::getRenderer($options)->render($input, $parameters);
    }

    /**
     * @param string $path       path to template
     * @param array  $parameters variables values
     * @param array  $options    custom options
     *
     * @return string
     */
    public static function renderFile($path, array $parameters = [], array $options = [])
    {
        return static::getRenderer($options)->renderFile($path, $parameters);
    }

    /**
     * @param string $input      pug source
     * @param array  $parameters variables values
     * @param array  $options    custom options
     */
    public static function display($input, array $parameters = [], array $options = [])
    {
        return static::getRenderer($options)->display($input, $parameters);
    }

    /**
     * @param string $path       path to template
     * @param array  $parameters variables values
     * @param array  $options    custom options
     */
    public static function displayFile($path, array $parameters = [], array $options = [])
    {
        return static::getRenderer($options)->displayFile($path, $parameters);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function hasFilter($name)
    {
        return isset(self::$filters[self::normalizeFilterName($name)]);
    }

    /**
     * @param string $name
     *
     * @return callable
     */
    public static function getFilter($name)
    {
        return self::$filters[self::normalizeFilterName($name)];
    }

    /**
     * @param string          $name
     * @param callable|string $filter
     */
    public static function setFilter($name, $filter)
    {
        if (!(
            is_callable($filter) ||
            class_exists($filter) ||
            method_exists($filter, 'parse')
        )) {
            throw new PhugException(
                'Invalid '.$name.' filter given: '.
                'it must be a callable or a class name.'
            );
        }

        self::$filters[self::normalizeFilterName($name)] = $filter;

        if (self::$renderer) {
            self::$renderer->setOptionsRecursive(static::getOptions());
        }
    }

    /**
     * @param string          $name
     * @param callable|string $filter
     */
    public static function addFilter($name, $filter)
    {
        $key = self::normalizeFilterName($name);

        if (isset(self::$filters[$key])) {
            throw new PhugException(
                'Filter '.$name.' is already set.'
            );
        }

        self::setFilter($name, $filter);
    }

    /**
     * @param string          $name
     * @param callable|string $filter
     */
    public static function replaceFilter($name, $filter)
    {
        $key = self::normalizeFilterName($name);

        if (!isset(self::$filters[$key])) {
            throw new PhugException(
                'Filter '.$name.' is not set.'
            );
        }

        self::setFilter($name, $filter);
    }

    /**
     * @param string $name
     */
    public static function removeFilter($name)
    {
        $key = self::normalizeFilterName($name);

        if (isset(self::$filters[$key])) {
            unset(self::$filters[$key]);

            if (self::$renderer) {
                self::$renderer->unsetOption(['filters', $key]);
            }
        }
    }

    /**
     * @return array
     */
    public static function getFilters()
    {
        return self::$filters;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function hasKeyword($name)
    {
        return isset(self::$keywords[self::normalizeKeywordName($name)]);
    }

    /**
     * @param string $name
     *
     * @return callable
     */
    public static function getKeyword($name)
    {
        return self::$keywords[self::normalizeKeywordName($name)];
    }

    /**
     * @param string          $name
     * @param callable|string $keyword
     */
    public static function setKeyword($name, $keyword)
    {
        if (!is_callable($keyword)) {
            throw new PhugException(
                'Invalid '.$name.' keyword given: '.
                'it must be a callable or a class name.'
            );
        }

        self::$keywords[self::normalizeKeywordName($name)] = $keyword;

        if (self::$renderer) {
            self::$renderer->setOptionsRecursive(static::getOptions());
        }
    }

    /**
     * @param string          $name
     * @param callable|string $keyword
     */
    public static function addKeyword($name, $keyword)
    {
        $key = self::normalizeKeywordName($name);

        if (isset(self::$keywords[$key])) {
            throw new PhugException(
                'Keyword '.$name.' is already set.'
            );
        }

        self::setKeyword($name, $keyword);
    }

    /**
     * @param string          $name
     * @param callable|string $keyword
     */
    public static function replaceKeyword($name, $keyword)
    {
        $key = self::normalizeKeywordName($name);

        if (!isset(self::$keywords[$key])) {
            throw new PhugException(
                'Keyword '.$name.' is not set.'
            );
        }

        self::setKeyword($name, $keyword);
    }

    /**
     * @param string $name
     */
    public static function removeKeyword($name)
    {
        $key = self::normalizeKeywordName($name);

        if (isset(self::$keywords[$key])) {
            unset(self::$keywords[$key]);

            if (self::$renderer) {
                self::$renderer->unsetOption(['keywords', $key]);
            }
        }
    }

    /**
     * @return array
     */
    public static function getKeywords()
    {
        return self::$keywords;
    }

    /**
     * @param string $extensionClassName
     *
     * @return bool
     */
    public static function hasExtension($extensionClassName)
    {
        return in_array(
            self::normalizeExtensionClassName($extensionClassName),
            array_map(
                [self::class, 'normalizeExtensionClassName'],
                self::$extensions
            )
        );
    }

    /**
     * @param string $extensionClassName
     */
    public static function addExtension($extensionClassName)
    {
        if (!class_exists($extensionClassName)) {
            throw new PhugException(
                'Invalid '.$extensionClassName.' extension given: '.
                'it must be a class name.'
            );
        }

        if (!static::hasExtension($extensionClassName)) {
            self::$extensions[] = $extensionClassName;

            if (self::$renderer) {
                self::$renderer->setOptionsRecursive(static::getOptions());
            }
        }
    }

    /**
     * @param string $extensionClassName
     */
    public static function removeExtension($extensionClassName)
    {
        if (static::hasExtension($extensionClassName)) {
            if (self::$renderer) {
                $extension = new $extensionClassName();
                foreach (['getOptions', 'getEvents'] as $method) {
                    static::removeOptions([], $extension->$method());
                }
                foreach (static::getExtensionsGetters() as $option => $method) {
                    static::removeOptions([$option], $extension->$method());
                }
                self::$renderer->setOptionsDefaults((new Renderer())->getOptions());
            }

            self::$extensions = array_diff(self::$extensions, [$extensionClassName]);
        }
    }

    /**
     * @return array
     */
    public static function getExtensions()
    {
        return self::$extensions;
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([static::getRenderer(), $name], $arguments);
    }
}
