<?php

namespace Phug\Test;

use Phug\Cli;
use Phug\Phug;
use Phug\Test\Utils\CustomOptionFacade;
use Phug\Util\TestCase;

/**
 * @coversDefaultClass \Phug\Cli
 */
class CliTest extends TestCase
{
    /**
     * @var Cli
     */
    protected $cli;

    protected function prepareTest()
    {
        Phug::reset();
        $this->cli = new Cli(Phug::class, [
            'render',
            'renderFile',
            'renderDirectory',
            'compile',
            'compileFile',
            'compileDirectory' => 'textualCacheDirectory',
            'display'          => 'render',
            'displayFile'      => 'renderFile',
            'displayDirectory' => 'renderDirectory',
            'cacheDirectory'   => 'textualCacheDirectory',
            'testFacade'       => function ($facade, $arguments) {
                return $facade.' - '.$arguments[0];
            },
        ]);
    }

    public function finishTest()
    {
        Phug::reset();
    }

    /**
     * @group cli
     *
     * @covers ::getCustomMethods
     */
    public function testGetCustomMethods()
    {
        $cli = new Cli(CustomOptionFacade::class, []);

        CustomOptionFacade::setOptions1([
            'commands' => [
                'b' => 13,
            ],
        ]);

        self::assertSame([
            'b' => 13,
        ], $cli->getCustomMethods());

        CustomOptionFacade::setOptions2([
            'commands' => [
                'a' => 42,
            ],
        ]);

        self::assertSame([
            'a' => 42,
        ], $cli->getCustomMethods());
    }

    /**
     * @group cli
     *
     * @covers ::convertToKebabCase
     * @covers ::convertToCamelCase
     * @covers ::getNamedArgumentBySpaceDelimiter
     * @covers ::getNamedArgumentByEqualOperator
     * @covers ::getNamedArgument
     * @covers ::execute
     * @covers ::getCustomMethods
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
        $this->cli->run(['_', 'render-file', __DIR__.'/../views/test.pug', '-o', $file]);
        $html = ob_get_contents();
        ob_end_clean();

        self::assertStringEqualsFile($file, '<p>Hello world!</p>');

        unlink($file);
    }

    /**
     * @group cli
     *
     * @covers ::convertToKebabCase
     * @covers ::convertToCamelCase
     * @covers ::execute
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
     *
     * @covers ::convertToKebabCase
     * @covers ::convertToCamelCase
     * @covers ::execute
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
     *
     * @covers ::convertToKebabCase
     * @covers ::convertToCamelCase
     * @covers ::getNamedArgumentBySpaceDelimiter
     * @covers ::getNamedArgumentByEqualOperator
     * @covers ::getNamedArgument
     * @covers ::execute
     * @covers ::<public>
     */
    public function testOptions()
    {
        $options = '{"attributes_mapping": {"link": "href"}}';
        ob_start();
        $this->cli->run(['_', 'render', 'a(link=$link)', '{"link": "abc"}', $options]);
        $html = ob_get_contents();
        ob_end_clean();

        self::assertSame('<a href="abc"></a>', $html);
    }

    /**
     * @group cli
     *
     * @covers ::convertToKebabCase
     * @covers ::convertToCamelCase
     * @covers ::getNamedArgumentBySpaceDelimiter
     * @covers ::getNamedArgumentByEqualOperator
     * @covers ::getNamedArgument
     * @covers ::execute
     * @covers ::<public>
     * @covers \Phug\Phug::cacheDirectory
     * @covers \Phug\Phug::textualCacheDirectory
     */
    public function testCacheDirectory()
    {
        $expected = __DIR__.'/../views/cache';

        $this->createEmptyDirectory($expected);
        file_put_contents("$expected/junk", 'junk');
        ob_start();
        $this->cli->run(['_', 'compile-directory', __DIR__.'/../views', $expected]);
        $text = ob_get_contents();
        ob_end_clean();

        self::assertFileExists($expected);
        self::assertFileNotExists("$expected/junk");

        $registryFile = "$expected/registry.php";

        self::assertFileExists($registryFile);

        $registry = include $registryFile;

        self::assertArrayHasKey('d:dir1', $registry);
        self::assertArrayHasKey('f:file1.pug', $registry['d:dir1']);
        self::assertArrayHasKey('i:0', $registry['d:dir1']['f:file1.pug']);

        $file = $expected.'/'.$registry['d:dir1']['f:file1.pug']['i:0'];

        self::assertFileExists($file);

        ob_start();
        include $file;
        $html = trim(ob_get_contents());
        ob_end_clean();

        self::assertSame('<p>A</p>', $html);

        $this->removeFile($expected);

        self::assertSame("3 templates cached.\n0 templates failed to be cached.\n", $text);

        $expected = __DIR__.'/../errorTemplates/cache';
        ob_start();
        $this->cli->run(['_', 'compile-directory', __DIR__.'/../errorTemplates', $expected]);
        $text = ob_get_contents();
        ob_end_clean();

        $this->removeFile($expected);

        self::assertRegExp('/Inconsistent indentation./', $text);
    }

    /**
     * @group cli
     *
     * @covers ::convertToKebabCase
     * @covers ::convertToCamelCase
     * @covers ::getNamedArgumentBySpaceDelimiter
     * @covers ::getNamedArgumentByEqualOperator
     * @covers ::getNamedArgument
     * @covers ::execute
     * @covers ::<public>
     * @covers \Phug\Phug::cacheDirectory
     * @covers \Phug\Phug::textualCacheDirectory
     */
    public function testBootstrap()
    {
        if (file_exists('phugBootstrap.php')) {
            rename('phugBootstrap.php', '__phugBootstrap.php');
        }

        $bootstrap = __DIR__.'/cliBootstrap.php';
        ob_start();
        $this->cli->run(['_', 'render', '-b='.$bootstrap, 'p(dad="Charlie")']);
        $html = ob_get_contents();
        ob_end_clean();

        self::assertSame('<p mum="Charlie"></p>', $html);

        chdir(sys_get_temp_dir());
        copy($bootstrap, 'phugBootstrap.php');
        ob_start();
        $this->cli->run(['_', 'render', 'p(dad="Fred")']);
        $html = ob_get_contents();
        ob_end_clean();

        self::assertSame('<p mum="Fred"></p>', $html);

        if (file_exists('__phugBootstrap.php')) {
            rename('__phugBootstrap.php', 'phugBootstrap.php');
        }
    }

    /**
     * @group cli
     *
     * @covers ::convertToKebabCase
     * @covers ::convertToCamelCase
     * @covers ::getNamedArgumentBySpaceDelimiter
     * @covers ::getNamedArgumentByEqualOperator
     * @covers ::getNamedArgument
     * @covers ::execute
     * @covers ::<public>
     * @covers \Phug\Phug::cacheDirectory
     * @covers \Phug\Phug::textualCacheDirectory
     */
    public function testCacheDirectoryWithViewsDirInOptions()
    {
        $expected = __DIR__.'/../views/cache';

        $this->createEmptyDirectory($expected);
        file_put_contents("$expected/junk", 'junk');
        ob_start();
        $this->cli->run(['_', 'compile-directory', __DIR__.'/../views', null, json_encode(['cache_dir' => $expected])]);
        $text = ob_get_contents();
        ob_end_clean();

        self::assertFileExists($expected);
        self::assertFileNotExists("$expected/junk");

        $registryFile = "$expected/registry.php";

        self::assertFileExists($registryFile);

        $registry = include $registryFile;

        self::assertArrayHasKey('d:dir1', $registry);
        self::assertArrayHasKey('f:file1.pug', $registry['d:dir1']);
        self::assertArrayHasKey('i:0', $registry['d:dir1']['f:file1.pug']);

        $file = $expected.'/'.$registry['d:dir1']['f:file1.pug']['i:0'];

        self::assertFileExists($file);

        ob_start();
        include $file;
        $html = trim(ob_get_contents());
        ob_end_clean();

        self::assertSame('<p>A</p>', $html);
        self::assertSame("3 templates cached.\n0 templates failed to be cached.\n", $text);
    }
}
