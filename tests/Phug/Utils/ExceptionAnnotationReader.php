<?php

namespace Phug\Test\Utils;

use ReflectionMethod;

class ExceptionAnnotationReader
{
    private static $phpDocs = [];

    public static function read($test, $method)
    {
        list($class, $method) = explode('::', $method);

        foreach (self::readAnnotation($class, $method, 'expectedException') as $exceptionClass) {
            $test->expectException($exceptionClass);
        }

        foreach (self::readAnnotation($class, $method, 'expectedExceptionCode') as $exceptionCode) {
            $test->expectExceptionCode($exceptionCode);
        }

        foreach (self::readAnnotation($class, $method, 'expectedExceptionMessage') as $exceptionMessage) {
            $test->expectExceptionMessage($exceptionMessage);
        }
    }

    private static function readAnnotation($class, $method, $annotation)
    {
        if (!method_exists($class, str_replace('expected', 'expect', $annotation))) {
            return [];
        }

        if (!isset(self::$phpDocs[$class])) {
            self::$phpDocs[$class] = [];
        }

        if (!isset(self::$phpDocs[$class][$method])) {
            $methodReflexion = new ReflectionMethod($class, $method);
            self::$phpDocs[$class][$method] = $methodReflexion->getDocComment();
        }

        return array_map(
            static function ($content) {
                return trim(explode("\n", $content, 2)[0]);
            },
            array_slice(explode('@'.$annotation.' ', self::$phpDocs[$class][$method]), 1)
        );
    }
}
