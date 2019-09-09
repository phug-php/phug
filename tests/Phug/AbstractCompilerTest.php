<?php

namespace Phug\Test;

use Exception;
use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;
use Phug\Compiler;
use Phug\CompilerEvent;

abstract class AbstractCompilerTest extends TestCase
{
    /**
     * @var Compiler
     */
    protected $compiler;

    protected function expectMessageToBeThrown($message)
    {
        if (method_exists($this, 'expectExceptionMessage')) {
            $this->expectExceptionMessage($message);

            return;
        }

        $this->setExpectedException(Exception::class, $message, null);
    }

    public function setUp()
    {
        $this->compiler = new Compiler([
            'paths'    => [__DIR__.'/../templates'],
            'patterns' => [
                'expression_in_text' => '%s',
            ],
        ]);
    }

    protected function enableJsPhpize()
    {
        $compiler = $this->compiler;

        $compiler = new Compiler([
            'paths'                       => [__DIR__.'/../templates'],
            'checked_variable_exceptions' => [
                'js-phpize' => function ($variable, $index, $tokens) {
                    return $index > 2 &&
                        $tokens[$index - 1] === '(' &&
                        $tokens[$index - 2] === ']' &&
                        is_array($tokens[$index - 3]) &&
                        $tokens[$index - 3][0] === T_CONSTANT_ENCAPSED_STRING &&
                        preg_match('/_with_ref\'$/', $tokens[$index - 3][1]);
                },
            ],
            'patterns'                    => [
                'expression_in_text'   => '%s',
                'transform_expression' => function ($jsCode) use (&$compiler) {
                    /** @var JsPhpize $jsPhpize */
                    $jsPhpize = $compiler->getOption('jsphpize_engine');

                    try {
                        $phpCode = trim($jsPhpize->compile($jsCode));
                        $phpCode = preg_replace('/\{\s*\}$/', '', $phpCode);
                        $phpCode = preg_replace(
                            '/^(?<!\$)\$+(\$[a-zA-Z\\\\\\x7f-\\xff][a-zA-Z0-9\\\\_\\x7f-\\xff]*\s*[=;])/',
                            '$1',
                            $phpCode
                        );

                        return rtrim(trim($phpCode), ';');
                    } catch (Exception $exception) {
                        if ($exception instanceof \JsPhpize\Lexer\Exception ||
                            $exception instanceof \JsPhpize\Parser\Exception ||
                            $exception instanceof \JsPhpize\Compiler\Exception
                        ) {
                            return $jsCode;
                        }

                        throw $exception;
                    }
                },
            ],
        ]);

        $compiler->attach(CompilerEvent::COMPILE, function () use ($compiler) {
            $compiler->setOption('jsphpize_engine', new JsPhpize([
                'catchDependencies' => true,
            ]));
        });

        $compiler->attach(CompilerEvent::OUTPUT, function (Compiler\Event\OutputEvent $event) use ($compiler) {

            /** @var JsPhpize $jsPhpize */
            $jsPhpize = $compiler->getOption('jsphpize_engine');
            $dependencies = $jsPhpize->compileDependencies();
            if ($dependencies !== '') {
                $event->setOutput($compiler->getFormatter()->handleCode($dependencies).$event->getOutput());
            }
            $jsPhpize->flushDependencies();
            $compiler->unsetOption('jsphpize_engine');
        });

        $this->compiler = $compiler;
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

        return $this->assertSameLines($expected, $compiler->compile($this->implodeLines($actual)));
    }

    protected function assertCompileFile($expected, $actual)
    {
        return $this->assertSameLines($expected, $this->compiler->compileFile($actual));
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

        return $this->assertSameLines($expected, $actual);
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

        return $this->assertSameLines($expected, $actual);
    }
}
