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
    private static $extensions = [];

    /**
     * @var Renderer
     */
    private static $renderer = null;

    private static function normalizeFilterName($name)
    {
        return str_replace('-', '', strtolower($name));
    }

    private static function normalizeExtensionClassName($name)
    {
        return ltrim('\\', strtolower($name));
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
        if (!static::$renderer) {
            static::$renderer = new Renderer(array_merge_recursive(
                [
                    'filters' => static::getFilters(),
                ],
                $options
            ));
        } elseif (!empty($options)) {
            static::$renderer->setOptions($options);
        }

        return static::$renderer;
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
        return static::getRenderer($options)->display($path, $parameters);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function hasFilter($name)
    {
        $name = self::normalizeFilterName($name);

        return isset(self::$filters[$name]);
    }

    /**
     * @param string          $name
     * @param callable|string $filter
     */
    public static function addFilter($name, $filter)
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

        self::$filters[$name] = $filter;
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
     * @param string $name
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
