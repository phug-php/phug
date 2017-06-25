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
        return new Renderer(array_merge_recursive(
            [
                'compiler_options' => [
                    'filters' => static::getFilters(),
                ],
            ],
            $options
        ));
    }

    /**
     * @param string $path       path to template
     * @param array  $parameters variables values
     * @param array  $options    custom options
     *
     * @return string
     */
    public static function render($path, array $parameters = [], array $options = [])
    {
        return static::getRenderer($options)->render($path, $parameters);
    }

    /**
     * @param string $input      pug source
     * @param array  $parameters variables values
     * @param array  $options    custom options
     *
     * @return string
     */
    public static function renderString($input, array $parameters = [], array $options = [])
    {
        return static::getRenderer($options)->renderString($input, $parameters);
    }

    /**
     * @param string $path       path to template
     * @param array  $parameters variables values
     * @param array  $options    custom options
     */
    public static function display($path, array $parameters = [], array $options = [])
    {
        return static::getRenderer($options)->display($path, $parameters);
    }

    /**
     * @param string $input      pug source
     * @param array  $parameters variables values
     * @param array  $options    custom options
     */
    public static function displayString($input, array $parameters = [], array $options = [])
    {
        return static::getRenderer($options)->displayString($input, $parameters);
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
}
