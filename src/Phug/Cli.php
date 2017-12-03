<?php

namespace Phug;

class Cli
{
    /**
     * @var string
     */
    protected $facade;

    /**
     * @var array
     */
    protected $methods;

    public function __construct($facade, array $methods)
    {
        $this->facade = $facade;
        $this->methods = $methods;
    }

    protected function convertToKebabCase($string)
    {
        return preg_replace_callback('/[A-Z]/', function ($match) {
            return '-'.strtolower($match[0]);
        }, $string);
    }

    protected function convertToCamelCase($string)
    {
        return preg_replace_callback('/-([a-z])/', function ($match) {
            return strtoupper($match[1]);
        }, $string);
    }

    public function run($arguments)
    {
        list(, $action) = array_pad($arguments, 2, null);
        $arguments = array_slice($arguments, 2);
        $facade = $this->facade;
        $method = $this->convertToCamelCase($action);

        if (!$action) {
            echo "You must provide a method.\n";
            $this->listAvailableMethods();

            return false;
        }

        if (!in_array($method, iterator_to_array($this->getAvailableMethods()))) {
            echo "The method $action is not available as CLI command in the $facade facade.\n";
            $this->listAvailableMethods();

            return false;
        }

        $callable = [$facade, $method];
        $arguments = array_map(function ($argument) {
            return in_array(substr($argument, 0, 1), ['[', '{'])
                ? json_decode($argument, true)
                : $argument;
        }, $arguments);
        if (isset($this->methods[$method])) {
            $method = $this->methods[$method];
            if (!is_string($method)) {
                $callable = $method;
                $arguments = [$facade, $arguments];
            }
        }

        echo call_user_func_array($callable, $arguments);

        return true;
    }

    public function getAvailableMethods()
    {
        foreach ($this->methods as $method => $action) {
            yield is_int($method) ? $action : $method;
        }
    }

    public function listAvailableMethods()
    {
        echo "Available methods are:\n";
        foreach ($this->getAvailableMethods() as $method) {
            if (substr($method, 0, 2) !== '__') {
                $action = $this->convertToKebabCase($method);
                $target = isset($this->methods[$method]) ? $this->methods[$method] : $method;
                $key = array_search($target, $this->methods);
                if (is_int($key)) {
                    $key = $this->methods[$key];
                }

                echo ' - '.$action.($key && $key !== $method ? ' ('.$this->convertToKebabCase($key).' alias)' : '')."\n";
            }
        }
    }
}
