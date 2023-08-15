<?php

namespace Phug\Test\Utils;

use Phug\Compiler;

trait AssertRender
{
    /**
     * @var Compiler
     */
    protected $compiler;

    protected function prepareTest()
    {
        if (isset($this->compiler)) {
            return;
        }

        $this->compiler = new Compiler([
            'paths'    => [__DIR__.'/../templates'],
            'patterns' => [
                'expression_in_text' => '%s',
                'expression_in_bool' => '%s',
            ],
        ]);
    }

    protected function implodeLines($str)
    {
        return preg_replace_callback('/(\s+[a-z0-9:_-]+="(?:\\\\[\\S\\s]|[^"\\\\])*")+/', function ($matches) {
            $attributes = [];
            $input = $matches[0];
            while (mb_strlen($input) && preg_match(
                '/^\s+([a-z0-9:_-]+)="((?:\\\\[\\S\\s]|[^"\\\\])*)"/',
                $input,
                $match
            )) {
                if ($match[1] === 'class') {
                    $classes = explode(' ', $match[2]);
                    sort($classes);
                    $match[2] = implode(' ', $classes);
                }
                $attributes[] = trim($match[1]).'="'.$match[2].'"';
                $input = mb_substr($input, mb_strlen($match[0]));
            }
            sort($attributes);

            return ' '.implode(' ', $attributes);
        }, is_string($str) ? $str : implode('', $str));
    }

    protected function assertSameLines($expected, $actual)
    {
        self::assertSame($this->implodeLines($expected), $this->implodeLines($actual));
    }

    protected function assertCompile($expected, $actual, array $options = [])
    {
        $compiler = clone $this->compiler;
        $compiler->setOptionsRecursive($options);

        $this->assertSameLines($expected, $compiler->compile($this->implodeLines($actual)));
    }

    protected function assertCompileFile($expected, $actual)
    {
        $this->assertSameLines($expected, $this->compiler->compileFile($actual));
    }

    protected function getRenderedHtml($php, array $variables = [])
    {
        if (getenv('LOG_COMPILE')) {
            file_put_contents('temp.php', $php);
        }
        extract($variables);
        ob_start();
        eval('?>'.$php);
        $actual = ob_get_contents();
        ob_end_clean();

        return $actual;
    }

    protected function render($actual, array $options = [], array $variables = [])
    {
        $compiler = $this->compiler;
        $compiler->setOptionsRecursive($options);
        $php = $compiler->compile($this->implodeLines($actual));

        return $this->getRenderedHtml($php, $variables);
    }

    protected function assertRender($expected, $actual, array $options = [], array $variables = [])
    {
        $actual = $this->render($actual, $options, $variables);

        $this->assertSameLines($expected, $actual);
    }

    protected function assertRenderFile($expected, $actual, array $options = [], array $variables = [])
    {
        $compiler = $this->compiler;
        $compiler->setOptionsRecursive($options);
        $php = $compiler->compileFile($actual);
        $actual = preg_replace(
            '/\s(class|id)=""/',
            '',
            $this->getRenderedHtml($php, $variables)
        );

        $this->assertSameLines($expected, $actual);
    }
}
