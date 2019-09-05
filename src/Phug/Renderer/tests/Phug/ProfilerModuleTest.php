<?php

namespace Phug\Test;

use PHPUnit\Framework\TestCase;
use Phug\Renderer;
use Phug\Renderer\Profiler\ProfilerException;
use Phug\Renderer\Profiler\ProfilerModule;
use Phug\RendererException;

/**
 * @coversDefaultClass Phug\Renderer\Profiler\ProfilerModule
 */
class ProfilerModuleTest extends TestCase
{
    /**
     * @group profiler
     * @covers ::record
     * @covers ::renderProfile
     * @covers ::cleanupProfilerNodes
     * @covers ::appendParam
     * @covers ::appendNode
     * @covers ::getCompilerEventListeners
     * @covers ::getFormatterEventListeners
     * @covers ::getParserEventListeners
     * @covers ::getLexerEventListeners
     * @covers ::<public>
     * @covers \Phug\Renderer\Profiler\TokenDump::<public>
     * @covers \Phug\Renderer\Profiler\LinkDump::<public>
     * @covers \Phug\Renderer\Profiler\LinkDump::initProperties
     * @covers \Phug\Renderer\Profiler\Profile::<public>
     * @covers \Phug\Renderer\Profiler\Profile::calculateIndex
     * @covers \Phug\Renderer\Profiler\Profile::getProcesses
     * @covers \Phug\Renderer\Profiler\Profile::getDuration
     * @covers \Phug\Renderer\Profiler\LinkedProcesses::<public>
     * @covers \Phug\Renderer\Profiler\LinkedProcesses::getEventLink
     * @covers \Phug\Renderer\Profiler\LinkedProcesses::getProfilerEvent
     * @covers \Phug\Renderer::__construct
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::enableModules
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::enableModule
     * @covers \Phug\Renderer\Partial\RendererOptionsTrait::getDefaultOptions
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::initDebugOptions
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::reInitOptions
     */
    public function testRenderProfiler()
    {
        $renderer = new Renderer([
            'enable_profiler' => true,
        ]);
        $render = $renderer->render('div');

        self::assertRegExp('/div lexing\s*<br>\s*[\.\d]+[µm]?s/', $render);
        self::assertContains('title="div lexing:', $render);
        self::assertRegExp('/div parsing\s*<br>\s*[\.\d]+[µm]?s/', $render);
        self::assertContains('title="div parsing:', $render);
        self::assertRegExp('/div compiling\s*<br>\s*[\.\d]+[µm]?s/', $render);
        self::assertContains('title="div compiling:', $render);
        self::assertRegExp('/div formatting\s*<br>\s*[\.\d]+[µm]?s/', $render);
        self::assertContains('title="div formatting:', $render);
        self::assertRegExp('/div rendering\s*<br>\s*[\.\d]+[µm]?s/', $render);
        self::assertContains('title="div rendering:', $render);

        $renderer = new Renderer([
            'enable_profiler' => true,
            'profiler'        => [
                'time_precision' => 7,
                'dump_event'     => function () {
                    return '-void-dump-';
                },
            ],
        ]);
        $render = $renderer->render("mixin foo\n  p&attributes(\$attributes)\n    | Hello\n+foo(a='b')");

        self::assertRegExp('/\+foo\s+parsing\s*<br>\s*[\.\d]+µs/', $render);
        self::assertRegExp('/text\s+parsing\s*<br>\s*[\.\d]+µs/', $render);
        self::assertRegExp('/mixin\s+foo\s+parsing\s*<br>\s*[\.\d]+µs/', $render);

        $renderer->reInitOptions([
            'debug' => false,
        ]);

        self::assertFalse($renderer->getOption('enable_profiler'));
    }

    /**
     * @group profiler
     * @covers ::record
     * @covers ::renderProfile
     * @covers ::recordDisplayEvent
     */
    public function testLogProfiler()
    {
        $log = sys_get_temp_dir().DIRECTORY_SEPARATOR.'profiler'.mt_rand(0, 9999999).'.log';
        $renderer = new Renderer([
            'enable_profiler' => true,
            'profiler'        => [
                'log'     => $log,
                'display' => false,
            ],
        ]);
        $renderer->render('div');
        $render = file_get_contents($log);
        /* @var ProfilerModule $profiler */
        $profiler = array_filter($renderer->getModules(), function ($module) {
            return $module instanceof ProfilerModule;
        })[0];
        $count = count($profiler->getEvents());
        $profiler->recordDisplayEvent(1);
        self::assertCount($count, $profiler->getEvents());
        self::assertRegExp('/div lexing\s*<br>\s*[\.\d]+[µm]?s/', $render);
        self::assertContains('title="div lexing:', $render);
        self::assertRegExp('/div parsing\s*<br>\s*[\.\d]+[µm]?s/', $render);
        self::assertContains('title="div parsing:', $render);
        self::assertRegExp('/div compiling\s*<br>\s*[\.\d]+[µm]?s/', $render);
        self::assertContains('title="div compiling:', $render);
        self::assertRegExp('/div formatting\s*<br>\s*[\.\d]+[µm]?s/', $render);
        self::assertContains('title="div formatting:', $render);
        self::assertRegExp('/div rendering\s*<br>\s*[\.\d]+[µm]?s/', $render);
        self::assertContains('title="div rendering:', $render);
    }

    /**
     * @group profiler
     * @covers ::renderProfile
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::initDebugOptions
     */
    public function testDebugDefaultOptions()
    {
        $renderer = new Renderer([
            'debug' => true,
        ]);

        self::assertGreaterThan(0, $renderer->getOption('memory_limit'));
        self::assertGreaterThan(0, $renderer->getOption('execution_max_time'));

        $renderer = new Renderer([
            'debug' => false,
        ]);

        self::assertLessThan(0, $renderer->getOption('memory_limit'));
        self::assertLessThan(0, $renderer->getOption('execution_max_time'));

        $renderer = new Renderer([
            'enable_profiler' => false,
            'memory_limit'    => 2000000,
        ]);

        self::assertTrue($renderer->getOption('enable_profiler'));
        self::assertFalse($renderer->getOption('profiler.display'));
        self::assertFalse($renderer->getOption('profiler.log'));

        $render = $renderer->render('div');

        self::assertSame('<div></div>', $render);
    }

    /**
     * @group profiler
     * @covers ::record
     * @covers ::renderProfile
     * @covers ::recordDisplayEvent
     * @covers ::throwException
     */
    public function testExecutionMaxTime()
    {
        $renderer = new Renderer([
            'execution_max_time' => 3,
            'filters'            => [
                'verbatim' => function ($string) {
                    // Pollute memory
                    usleep(10);

                    return $string;
                },
            ],
        ]);
        $message = '';

        try {
            for ($i = 0; $i < 10; $i++) {
                $renderer->renderFile(__DIR__.'/../cases/includes.pug');
            }
        } catch (ProfilerException $exception) {
            // Short time should imply not located exception
            $message = $exception->getMessage();
        } catch (RendererException $exception) {
            // Should not happen (security for HHVM test)
            $message = $exception->getMessage();
        }

        self::assertContains('execution_max_time of 3ms exceeded.', $message);
    }

    /**
     * @group profiler
     * @covers ::record
     * @covers ::renderProfile
     * @covers ::recordDisplayEvent
     * @covers ::throwException
     */
    public function testMemoryLimit()
    {
        if (defined('HHVM_VERSION')) {
            self::markTestSkipped('Memory limit test skipped on HHVM.');

            return;
        }

        $GLOBALS['LAkjdJHSmlakSJHGdjAJGdjGAHgsjHDAD'] = null;
        $limit = 500000;
        $factor = 1;
        $renderer = new Renderer([
            'memory_limit' => $limit,
            'filters'      => [
                'verbatim' => function ($string) use ($limit, &$factor) {
                    // Pollute memory
                    $GLOBALS['LAkjdJHSmlakSJHGdjAJGdjGAHgsjHDAD'] = str_repeat(
                        'a',
                        $limit * $factor
                    );

                    return $string;
                },
            ],
        ]);
        $message = '';

        try {
            for ($i = 0; $i < 10; $i++) {
                $factor = $i + 1.3;
                $renderer->renderFile(__DIR__.'/../cases/includes.pug');
            }
        } catch (ProfilerException $exception) {
            // Should not happen
            $message = $exception->getMessage();
        } catch (RendererException $exception) {
            // 500000B should only be exceeded on verbatim call
            $message = $exception->getMessage();
        }
        unset($GLOBALS['LAkjdJHSmlakSJHGdjAJGdjGAHgsjHDAD']);

        self::assertContains('memory_limit of '.$limit.'B exceeded.', $message);
    }

    /**
     * @group profiler
     * @covers \Phug\Renderer\Profiler\TokenDump::<public>
     */
    public function testTokenDump()
    {
        $renderer = new Renderer([
            'enable_profiler' => true,
            'profiler'        => [
                'time_precision' => 7,
                'dump_event'     => function () {
                    return '-void-dump-';
                },
            ],
        ]);
        $render = $renderer->render("a(href='a')\n  | Hello\ndiv");

        self::assertContains('↩', $render);
        self::assertContains('new line', $render);
        self::assertContains('→', $render);
        self::assertContains('indent', $render);
        self::assertContains('←', $render);
        self::assertContains('outdent', $render);
        self::assertContains('(', $render);
        self::assertContains('attributes start', $render);
        self::assertContains(')', $render);
        self::assertContains('attributes end', $render);
    }

    /**
     * @group profiler
     * @covers ::record
     * @covers ::renderProfile
     * @covers ::cleanupProfilerNodes
     * @covers ::appendParam
     * @covers ::appendNode
     * @covers ::getCompilerEventListeners
     * @covers ::getFormatterEventListeners
     * @covers ::getParserEventListeners
     * @covers ::getLexerEventListeners
     * @covers ::<public>
     * @covers \Phug\Renderer\Profiler\TokenDump::<public>
     * @covers \Phug\Renderer\Profiler\LinkDump::<public>
     * @covers \Phug\Renderer\Profiler\LinkDump::initProperties
     * @covers \Phug\Renderer\Profiler\Profile::<public>
     * @covers \Phug\Renderer\Profiler\Profile::calculateIndex
     * @covers \Phug\Renderer\Profiler\Profile::getProcesses
     * @covers \Phug\Renderer\Profiler\Profile::getDuration
     * @covers \Phug\Renderer\Profiler\LinkedProcesses::<public>
     * @covers \Phug\Renderer\Profiler\LinkedProcesses::getEventLink
     * @covers \Phug\Renderer\Profiler\LinkedProcesses::getProfilerEvent
     * @covers \Phug\Renderer::__construct
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::initDebugOptions
     */
    public function testDisplayProfiler()
    {
        $renderer = new Renderer([
            'enable_profiler' => true,
            'profiler'        => [
                'dump_event' => function () {
                    return '-void-dump-';
                },
            ],
        ]);
        ob_start();
        $renderer->display('div');
        $contents = ob_get_contents();
        ob_end_clean();

        self::assertRegExp('/div lexing\s*<br>\s*[\.\d]+[µm]?s/', $contents);
        self::assertContains('title="div lexing:', $contents);
        self::assertRegExp('/div parsing\s*<br>\s*[\.\d]+[µm]?s/', $contents);
        self::assertContains('title="div parsing:', $contents);
        self::assertRegExp('/div compiling\s*<br>\s*[\.\d]+[µm]?s/', $contents);
        self::assertContains('title="div compiling:', $contents);
        self::assertRegExp('/div formatting\s*<br>\s*[\.\d]+[µm]?s/', $contents);
        self::assertContains('title="div formatting:', $contents);
        self::assertRegExp('/div rendering\s*<br>\s*[\.\d]+[µm]?s/', $contents);
        self::assertContains('title="div rendering:', $contents);
        self::assertContains('-void-dump-', $contents);
    }

    /**
     * @group profiler
     * @covers ::reset
     * @covers ::initialize
     * @covers ::getFunctionDump
     * @covers ::getCompilerEventListeners
     * @covers ::getFormatterEventListeners
     * @covers ::getParserEventListeners
     * @covers ::getLexerEventListeners
     * @covers ::<public>
     * @covers \Phug\Renderer\Profiler\TokenDump::<public>
     * @covers \Phug\Renderer\Profiler\LinkDump::<public>
     * @covers \Phug\Renderer\Profiler\LinkDump::initProperties
     * @covers \Phug\Renderer\Profiler\Profile::<public>
     * @covers \Phug\Renderer\Profiler\Profile::calculateIndex
     * @covers \Phug\Renderer\Profiler\Profile::getProcesses
     * @covers \Phug\Renderer\Profiler\LinkedProcesses::<public>
     * @covers \Phug\Renderer\Profiler\LinkedProcesses::getEventLink
     * @covers \Phug\Renderer\Profiler\LinkedProcesses::getProfilerEvent
     * @covers \Phug\Renderer\Profiler\Profile::getDuration
     * @covers \Phug\Renderer::__construct
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::initDebugOptions
     */
    public function testCustomDump()
    {
        $renderer = new Renderer([
            'enable_profiler' => true,
        ]);
        $renderer->setOption('profiler.dump_event', 'get_class');
        /* @var ProfilerModule $profiler */
        $profiler = array_filter($renderer->getModules(), function ($module) {
            return $module instanceof ProfilerModule;
        })[0];

        self::assertInstanceOf(ProfilerModule::class, $profiler);

        $renderer->render('p');

        self::assertGreaterThan(1, count($profiler->getEvents()));

        $profiler->reset();

        self::assertCount(0, $profiler->getEvents());

        $render = $renderer->render('div');

        self::assertContains('Phug\\Compiler\\Event\\NodeEvent', $render);
    }

    /**
     * @group profiler
     * @covers ::reset
     * @covers ::initialize
     * @covers ::getFunctionDump
     * @covers ::getCompilerEventListeners
     * @covers ::getFormatterEventListeners
     * @covers ::getParserEventListeners
     * @covers ::getLexerEventListeners
     * @covers ::<public>
     * @covers \Phug\Renderer\Profiler\TokenDump::<public>
     * @covers \Phug\Renderer\Profiler\LinkDump::<public>
     * @covers \Phug\Renderer\Profiler\LinkDump::initProperties
     * @covers \Phug\Renderer\Profiler\Profile::<public>
     * @covers \Phug\Renderer\Profiler\Profile::calculateIndex
     * @covers \Phug\Renderer\Profiler\Profile::getProcesses
     * @covers \Phug\Renderer\Profiler\Profile::getDuration
     * @covers \Phug\Renderer\Profiler\LinkedProcesses::<public>
     * @covers \Phug\Renderer\Profiler\LinkedProcesses::getEventLink
     * @covers \Phug\Renderer\Profiler\LinkedProcesses::getProfilerEvent
     * @covers \Phug\Renderer::__construct
     * @covers \Phug\Renderer\Partial\Debug\DebuggerTrait::initDebugOptions
     */
    public function testEventVarDump()
    {
        if (defined('HHVM_VERSION')) {
            self::markTestSkipped('var_dump test disabled for HHVM.');

            return;
        }

        $renderer = new Renderer([
            'enable_profiler' => true,
        ]);
        $renderer->setOption('profiler.dump_event', 'var_dump');
        /* @var ProfilerModule $profiler */
        $profiler = array_filter($renderer->getModules(), function ($module) {
            return $module instanceof ProfilerModule;
        })[0];

        self::assertInstanceOf(ProfilerModule::class, $profiler);

        $renderer->render('p');

        self::assertGreaterThan(1, count($profiler->getEvents()));

        $profiler->reset();

        self::assertCount(0, $profiler->getEvents());

        $render = $renderer->render('div');

        self::assertRegExp('/class\\s+Phug\\\\Parser\\\\Node\\\\DocumentNode#\\d+\\s+\\(\\d+\\)\\s+\\{/', $render);
    }
}
