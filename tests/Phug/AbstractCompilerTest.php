<?php

namespace Phug\Test;

use Exception;
use JsPhpize\JsPhpize;
use Phug\Compiler;
use Phug\CompilerEvent;
use Phug\Test\Utils\AssertRender;
use Phug\Util\TestCase;

abstract class AbstractCompilerTest extends TestCase
{
    use AssertRender;

    protected function expectMessageToBeThrown($message, $type = null)
    {
        if (method_exists($this, 'expectExceptionMessage')) {
            $this->expectExceptionMessage($message);
            $this->expectException($type ?: Exception::class);

            return;
        }

        $this->setExpectedException($type ?: Exception::class, $message, null);
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
}
