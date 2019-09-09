<?php

namespace Phug\Test;

use DateTimeImmutable;

/**
 * @coversDefaultClass \Phug\Renderer
 */
class CasesTest extends AbstractRendererTest
{
    public function caseProvider()
    {
        return array_map(function ($file) {
            $file = realpath($file);
            $pugFile = substr($file, 0, -5).'.pug';

            return [$file, $pugFile, basename($pugFile).' should render '.basename($file)];
        }, glob(__DIR__.'/../cases/*.html'));
    }

    /**
     * @group cases
     * @dataProvider caseProvider
     * @covers ::compileFile
     * @covers ::render
     */
    public function testRender($expected, $actual, $message)
    {
        $debug = $this->renderer->getOption('debug');
        $this->renderer->setOption('debug', true);
        $render = $this->renderer->renderFile($actual);
        $this->renderer->setOption('debug', $debug);

        self::assertSameLines(
            file_get_contents($expected),
            $render,
            $message
        );
    }

    /**
     * @group update
     */
    public function testIfCasesAreUpToDate()
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: PHP',
                ],
            ],
        ]);
        $pugJs = @file_get_contents(
            'https://api.github.com/repos/pugjs/pug/commits?path=packages/pug/test/cases',
            false,
            $context
        );
        $pugPhp = @file_get_contents(
            'https://api.github.com/repos/phug-php/renderer/commits?path=tests/cases',
            false,
            $context
        );
        if (!$pugJs || !$pugPhp) {
            self::markTestSkipped('Test case update skipped because api.github.com was not available.');
        }

        $json = json_decode($pugJs);
        $lastCommit = new DateTimeImmutable($json[0]->commit->author->date);
        $json = json_decode($pugPhp);
        $upToDate = new DateTimeImmutable($json[0]->commit->author->date);

        self::assertTrue(
            $lastCommit <= $upToDate,
            'Cases should be updated with php tests/update.php, '.
            'then you should commit the new cases.'
        );
    }
}
