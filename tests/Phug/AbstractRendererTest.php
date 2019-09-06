<?php

namespace Phug\Test;

use cebe\markdown\GithubMarkdown;
use JsPhpize\JsPhpizePhug;
use NodejsPhpFallback\CoffeeScript;
use NodejsPhpFallback\Less;
use NodejsPhpFallback\Stylus;
use NodejsPhpFallback\Uglify;
use PHPUnit\Framework\TestCase;
use Phug\Renderer;
use stdClass;

abstract class AbstractRendererTest extends TestCase
{
    /**
     * @var Renderer
     */
    protected $renderer;

    public function setUp()
    {
        ini_set('memory_limit', '512M');
        include_once __DIR__.'/Utils/Date.php';

        $uglify = function ($contents) {
            $engine = new Uglify($contents);

            return "\n".$engine->getResult()."\n";
        };
        $markdown = function ($contents) {
            $engine = new GithubMarkdown();

            return $engine->parse($contents);
        };
        $coffee = function ($contents, $options) use ($uglify) {
            $engine = new CoffeeScript($contents, false);
            $result = $engine->getResult();
            if (isset($options['minify']) && $options['minify']) {
                // @TODO fix it when https://github.com/pugjs/pug/issues/2829 answered
                return "\n(function(){}).call(this);\n";

                //return $uglify($result);
            }

            return "\n".$result."\n";
        };
        $custom = function ($contents) {
            return 'BEGIN'.$contents.'END';
        };
        $less = function ($contents) {
            $engine = new Less($contents);

            return "\n".$engine->getResult()."\n";
        };
        $stylus = function ($contents) {
            $engine = new Stylus($contents);

            return "\n".$engine->getCss()."\n";
        };
        $verbatim = function ($contents) {
            return $contents;
        };
        $this->renderer = new Renderer([
            'debug'              => false,
            'execution_max_time' => 60000,
            'basedir'            => __DIR__.'/../cases',
            'pretty'             => true,
            'modules'            => [JsPhpizePhug::class],
            'filters'            => [
                'custom'        => $custom,
                'coffee-script' => $coffee,
                'less'          => $less,
                'markdown-it'   => $markdown,
                'markdown'      => $markdown,
                'stylus'        => $stylus,
                'uglify-js'     => $uglify,
                'minify'        => $uglify,
                'verbatim'      => $verbatim,
            ],
        ]);
        $this->renderer->share([
            'title'  => 'Pug',
            'Object' => [
                'create' => function () {
                    return new stdClass();
                },
            ],
        ]);
    }

    protected static function emptyDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $file) {
            if ($file !== '.' && $file !== '..') {
                $path = $dir.'/'.$file;
                if (is_dir($path)) {
                    static::emptyDirectory($path);
                } else {
                    unlink($path);
                }
            }
        }
    }

    public static function flatContent($content)
    {
        return implode('', array_map(function ($line) {
            $line = trim($line);
            $line = preg_replace_callback('/(\s+[a-z0-9:_-]+="(?:\\\\[\\S\\s]|[^"\\\\])*"){2,}/', function ($matches) {
                $attributes = [];
                $input = $matches[0];
                while (mb_strlen($input) && preg_match('/^\s+[a-z0-9:_-]+="(?:\\\\[\\S\\s]|[^"\\\\])*"/', $input, $match)) {
                    $attributes[] = trim($match[0]);
                    $input = mb_substr($input, mb_strlen($match[0]));
                }
                sort($attributes);

                return ' '.implode(' ', $attributes);
            }, $line);

            return $line;
        }, preg_split('/\r|\n/', self::standardLines($content))));
    }

    protected static function loopReplace($from, $to, $content)
    {
        $newContent = null;
        do {
            $newContent = $newContent ?: $content;
            $content = $newContent;
            $newContent = preg_replace($from, $to, $newContent);
        } while ($newContent !== $content);

        return $content;
    }

    public static function standardLines($content)
    {
        $content = str_replace(
            ["\r\n", '/><', ' />', '> /<', ';"'],
            ["\n", "/>\n<", '/>', '>/<', '"'],
            trim($content)
        );
        foreach (['ul', 'div', 'body'] as $tag) {
            $newContent = preg_replace('/(<'.$tag.'([^>]+|"(?:\\\\[\\s\\S]|[^"\\\\])*")*?>)(?!\n|<\/'.$tag.'>)/', "$1\n", $content);
            if (preg_last_error()) {
                break;
            }

            $content = $newContent;
        }
        // Tags used in tests where inside end whitespaces does not matter
        foreach (['p', 'foo', 'form', 'audio', 'style', 'li'] as $tag) {
            $content = preg_replace_callback(
                '/(<'.$tag.'[^>]*>)([\S\s]*?)(<\/'.$tag.'>)/',
                function ($match) {
                    return $match[1].trim($match[2]).$match[3];
                },
                $content
            );
            $content = preg_replace_callback(
                '/<'.$tag.'[^>]*>([\s\S]*?)<\/'.$tag.'>/',
                function ($match) {
                    return str_replace("\n", ' ', $match[0]);
                },
                $content
            );
        }
        // Comment squeeze
        $content = preg_replace('/\s*<!--\s*(\S[\s\S]*?\S)\s*-->/', '<!--$1-->', $content);

        $content = preg_replace('/\s(class|id|src)\s*=\s*(""|\'\')/', '', $content);
        $content = preg_replace_callback('/class=([\'"])\s*(([^\'"\s]+)(\s+[^\'"\s]+)+)\s*\\1/U', function ($match) {
            $classes = preg_split('/\s+/', $match[2]);
            sort($classes);

            return 'class='.$match[1].implode(' ', $classes).$match[1];
        }, $content);
        $content = static::loopReplace('/(\S)[ \t]*(<\/?(p|script|h\d|div)[^>]*>)/', "\\1\n\\2", $content);
        $content = preg_replace('/(?<!\s)[ \t]{2,}(?=\S)/', ' ', $content);
        $content = preg_replace('/<script[^>]*>(?=\S)/', "\\0\n", $content);
        $content = str_replace('(){return ', '(){', $content);
        $content = static::loopReplace('/\((function\(\)\{[\s\S]*?\})\)/', '!$1', $content);

        return str_replace(['/><', ' />'], ["/>\n<", '/>'], trim($content));
    }

    public static function assertSameLines($expected, $actual, $message = null)
    {
        $message = $message ?: 'Contents should have the same HTML lines.';
        $flatExpected = self::flatContent($expected);
        $flatActual = self::flatContent($actual);
        if ($flatExpected === $flatActual) {
            self::assertSame($flatExpected, $flatActual, $message);

            return;
        }
        $expected = self::standardLines($expected);
        $actual = self::standardLines($actual);

        if (is_callable($message)) {
            $message = $message();
        }

        self::assertSame($expected, $actual, $message);
    }

    protected static function getReadOnlyDirectory()
    {
        $dir = __DIR__;
        while (is_writable($dir)) {
            $parent = realpath($dir.'/..');
            if ($parent === $dir) {
                $dir = 'C:';
                if (!file_exists($dir) || is_writable($dir)) {
                    self::markTestSkipped('No read-only directory found to do the test');

                    return;
                }
                break;
            }
            $dir = $parent;
        }

        return $dir;
    }
}
