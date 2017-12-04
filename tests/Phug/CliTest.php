<?php

namespace Phug\Test;

use PHPUnit\Framework\TestCase;
use Phug\Cli;
use Phug\Phug;

/**
 * @coversDefaultClass \Phug\Cli
 */
class CliTest extends TestCase
{
    /**
     * @var Cli
     */
    protected $cli;

    public function setUp()
    {
        $this->cli = new Cli(Phug::class, [
            'render',
            'renderFile',
            'renderDirectory',
            'compile',
            'compileFile',
            'compileDirectory' => 'textualCacheDirectory',
            'display' => 'render',
            'displayFile' => 'renderFile',
            'displayDirectory' => 'renderDirectory',
            'cacheDirectory' => 'textualCacheDirectory',
            'testFacade' => function ($facade, $arguments) {
                return $facade.' - '.$arguments[0];
            },
        ]);
    }

    /**
     * @group cli
     * @covers ::convertToKebabCase
     * @covers ::convertToCamelCase
     * @covers ::<public>
     */
    public function testRun()
    {
        ob_start();
        $this->cli->run(['_', 'render', 'p Hello world!']);
        $html = ob_get_contents();
        ob_end_clean();

        self::assertSame('<p>Hello world!</p>', $html);

        $file = sys_get_temp_dir().'/foo'.mt_rand(0, 9999999).'.html';
        ob_start();
        $this->cli->run(['_', 'render-file', __DIR__.'/../templates/test.pug', '-o', $file]);
        $html = ob_get_contents();
        ob_end_clean();

        self::assertStringEqualsFile($file, '<p>Hello world!</p>');

        unlink($file);
    }

    /**
     * @group cli
     * @covers ::convertToKebabCase
     * @covers ::convertToCamelCase
     * @covers ::<public>
     */
    public function testListAvailableMethods()
    {
        ob_start();
        $this->cli->run(['_', 'bad-action']);
        $text = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            "The method bad-action is not available as CLI command in the Phug\Phug facade.\n".
            "Available methods are:\n".
            " - render\n".
            " - render-file\n".
            " - render-directory\n".
            " - compile\n".
            " - compile-file\n".
            " - compile-directory\n".
            " - display (render alias)\n".
            " - display-file (render-file alias)\n".
            " - display-directory (render-directory alias)\n".
            " - cache-directory (compile-directory alias)\n".
            " - test-facade\n",
            $text
        );

        ob_start();
        $this->cli->run(['_']);
        $text = ob_get_contents();
        ob_end_clean();

        self::assertSame(
            "You must provide a method.\n".
            "Available methods are:\n".
            " - render\n".
            " - render-file\n".
            " - render-directory\n".
            " - compile\n".
            " - compile-file\n".
            " - compile-directory\n".
            " - display (render alias)\n".
            " - display-file (render-file alias)\n".
            " - display-directory (render-directory alias)\n".
            " - cache-directory (compile-directory alias)\n".
            " - test-facade\n",
            $text
        );
    }

    /**
     * @group cli
     * @covers ::convertToKebabCase
     * @covers ::convertToCamelCase
     * @covers ::<public>
     */
    public function testCallableActions()
    {
        ob_start();
        $this->cli->run(['_', 'test-facade', 'foobar']);
        $text = ob_get_contents();
        ob_end_clean();

        self::assertSame('Phug\\Phug - foobar', $text);
    }

    /**
     * @group cli
     * @covers ::convertToKebabCase
     * @covers ::convertToCamelCase
     * @covers ::<public>
     */
    public function testOptions()
    {
        ob_start();
        $this->cli->run(['_', 'render', 'a(link=$link)', '{"link": "abc"}', '{"attributes_mapping": {"link": "href"}}']);
        $html = ob_get_contents();
        ob_end_clean();

        self::assertSame('<a href="abc"></a>', $html);
    }
}
