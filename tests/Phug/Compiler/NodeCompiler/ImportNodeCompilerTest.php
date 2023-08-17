<?php

namespace Phug\Test\Compiler\NodeCompiler;

use Phug\Compiler;
use Phug\Compiler\NodeCompiler\ImportNodeCompiler;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;
use Phug\Test\TestCompiler;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\ImportNodeCompiler
 */
class ImportNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers            ::<public>
     *
     * @expectedException \Phug\CompilerException
     */
    public function testException()
    {
        $this->expectMessageToBeThrown(
            'Unexpected Phug\Parser\Node\ElementNode '.
            'given to import compiler.'
        );

        $importCompiler = new ImportNodeCompiler(new Compiler());
        $importCompiler->compileNode(new ElementNode());
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileNamedBlock
     * @covers \Phug\Compiler\Element\BlockElement::<public>
     * @covers \Phug\Compiler\NodeCompiler\FilterNodeCompiler::compileNode
     */
    public function testInclude()
    {
        $this->assertCompile(
            '<section><div>sample</div></section>',
            'section: include /inc.pug'
        );
        $this->compiler->setOption(['filters', 'upper'], function ($contents) {
            return strtoupper($contents);
        });
        $this->assertCompile(
            '<section>UPPER</section>',
            'section: include:upper /lower.txt'
        );
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Compiler::reset
     * @covers \Phug\Compiler::__clone
     * @covers \Phug\Compiler::setLayout
     * @covers \Phug\Compiler::getBlocksByName
     * @covers \Phug\Compiler\Util\YieldHandlerTrait::setImportNode
     * @covers \Phug\Compiler\Util\YieldHandlerTrait::isImportNodeYielded
     * @covers \Phug\Compiler::importBlocks
     * @covers \Phug\Compiler::compileBlocks
     * @covers \Phug\Compiler::compile
     * @covers \Phug\Compiler::replaceBlock
     * @covers \Phug\Compiler::compileDocument
     * @covers \Phug\Compiler::compileFile
     * @covers \Phug\Compiler::compileFileIntoElement
     * @covers \Phug\Compiler::getPath
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileNamedBlock
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::hasBlockParent
     * @covers \Phug\Compiler\Element\BlockElement::<public>
     * @covers \Phug\Compiler\Layout::<public>
     */
    public function testReplaceBlocks()
    {
        $this->assertCompileFile(
            '<section><div>Bye</div><div>Bye</div></section>',
            __DIR__.'/../../../templates/replace.pug'
        );
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Compiler::reset
     * @covers \Phug\Compiler::__clone
     * @covers \Phug\Compiler::setLayout
     * @covers \Phug\Compiler::getBlocksByName
     * @covers \Phug\Compiler\Util\YieldHandlerTrait::setImportNode
     * @covers \Phug\Compiler\Util\YieldHandlerTrait::isImportNodeYielded
     * @covers \Phug\Compiler::importBlocks
     * @covers \Phug\Compiler::compileBlocks
     * @covers \Phug\Compiler::compile
     * @covers \Phug\Compiler::compileDocument
     * @covers \Phug\Compiler::compileFile
     * @covers \Phug\Compiler::compileFileIntoElement
     * @covers \Phug\Compiler::getPath
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileNamedBlock
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::hasBlockParent
     * @covers \Phug\Compiler\Element\BlockElement::<public>
     * @covers \Phug\Compiler\Layout::<public>
     */
    public function testExtends()
    {
        $this->assertCompileFile(
            "<section>1\nA2\nA</section>",
            __DIR__.'/../../../templates/page.pug'
        );

        $list = &$this->compiler->getBlocksByName('foo');
        self::assertCount(2, $list);
        /* @var \Phug\Compiler\Element\BlockElement $copyBlock */
        $copyBlock = clone $list[0];
        self::assertCount(3, $list);
        self::assertSame('foo', $copyBlock->getName());
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Compiler\Util\YieldHandlerTrait::setYieldNode
     * @covers \Phug\Compiler\Util\YieldHandlerTrait::unsetYieldNode
     * @covers \Phug\Compiler\Util\YieldHandlerTrait::getYieldNode
     * @covers \Phug\Compiler\NodeCompiler\YieldNodeCompiler::<public>
     * @covers \Phug\Compiler\Element\BlockElement::<public>
     */
    public function testDoubleInheritance()
    {
        $this->assertCompile(
            [
                'The message is ""',
            ],
            [
                '| The message is "'."\n",
                'yield'."\n",
                '| "'."\n",
            ]
        );
        $this->assertRenderFile(
            [
                '<div class="window">',
                '<a href="#" class="close">Close</a>',
                '<div class="dialog">',
                '<h1>Alert!</h1>',
                '<p>I\'m an alert!</p>',
                '</div>',
                '</div>',
            ],
            __DIR__.'/../../../templates/inheritance.alert-dialog.pug'
        );
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Compiler\Element\BlockElement::<public>
     */
    public function testExtendsInInclude()
    {
        $this->assertCompileFile(
            "<section>1\nA2A</section><section>1\nA2A</section>",
            __DIR__.'/../../../templates/inc-page.pug'
        );
    }

    /**
     * @covers ::<public>
     */
    public function testAutoYield()
    {
        $this->assertRenderFile(
            '<div>switch</div><section><div><img />img</div></section>',
            __DIR__.'/../../../templates/auto-yield.pug'
        );
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Compiler\NodeCompiler\YieldNodeCompiler::<public>
     */
    public function testNestedYield()
    {
        $expected = file_get_contents(__DIR__.'/../../../templates/yield-in-sub-include.html');

        $this->assertCompileFile(
            str_replace(["\r", "\n"], '', $expected),
            __DIR__.'/../../../templates/yield-in-sub-include.pug'
        );
    }

    /**
     * @covers ::<public>
     */
    public function testMixinsPropagation()
    {
        $expected = file_get_contents(__DIR__.'/../../../templates/inheritance.extend.mixins.html');

        $this->assertRenderFile(
            preg_replace('/\r?\n\s*/', '', $expected),
            __DIR__.'/../../../templates/inheritance.extend.mixins.pug'
        );
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Compiler\NodeCompiler\YieldNodeCompiler::<public>
     * @covers \Phug\Compiler\Util\YieldHandlerTrait::getImportNode
     * @covers \Phug\Compiler\Util\YieldHandlerTrait::setYieldNode
     * @covers \Phug\Compiler\Util\YieldHandlerTrait::unsetYieldNode
     */
    public function testYieldInInclude()
    {
        $this->assertCompileFile(
            '<div>foo<p>Hello</p>bar</div>',
            __DIR__.'/../../../templates/inc-yield.pug'
        );
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileNamedBlock
     * @covers \Phug\Compiler\NodeCompiler\ImportNodeCompiler::isPugImport
     * @covers \Phug\Compiler\Element\BlockElement::<public>
     */
    public function testIncludeNoExtension()
    {
        $this->assertCompileFile(
            '<p>Pug</p>',
            __DIR__.'/../../../templates/inc-no-extension.pug'
        );
    }

    /**
     * @covers \Phug\Compiler\NodeCompiler\ImportNodeCompiler::isPugImport
     */
    public function testAllowCompositeExtensions()
    {
        $compiler = new Compiler([
            'extensions' => ['', '.blade.pug'],
        ]);

        $this->assertSame(
            '<p>Bar</p>b Foop Just text',
            str_replace(["\r", "\n"], '', trim(
                $compiler->compileFile(__DIR__.'/../../../composite-extensions/bar.blade.pug')
            ))
        );

        $compiler = new Compiler([
            'extensions'                 => ['', '.blade.pug'],
            'allow_composite_extensions' => true,
        ]);

        $this->assertSame(
            '<p>Bar</p><b>Foo</b>p Just text',
            str_replace(["\r", "\n"], '', trim(
                $compiler->compileFile(__DIR__.'/../../../composite-extensions/bar.blade.pug')
            ))
        );
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileNamedBlock
     * @covers \Phug\Compiler\Element\BlockElement::<public>
     */
    public function testIncludeChildren()
    {
        $this->assertCompileFile(
            '<section><div>sample<p>A</p><p>B</p></div></section>',
            __DIR__.'/../../../templates/inc-children.pug'
        );
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileNamedBlock
     * @covers \Phug\Compiler\Element\BlockElement::<public>
     */
    public function testIncludeRawText()
    {
        $this->assertCompileFile(
            '<pre><code>var x = "\n here is some \n new lined text";'."\n</code></pre>",
            __DIR__.'/../../../templates/includes-with-ext-js.pug'
        );
    }

    /**
     * @covers ::<public>
     */
    public function testNestedIncludedMixins()
    {
        $this->assertRenderFile(
            file_get_contents(__DIR__.'/../../../templates/issue26/error-test.html'),
            __DIR__.'/../../../templates/issue26/error-test.pug'
        );
    }

    /**
     * @covers            \Phug\Compiler::compileIntoElement
     *
     * @expectedException \Phug\CompilerException
     */
    public function testCompileIntoElementException()
    {
        $this->expectMessageToBeThrown(
            'Phug\Parser\Node\DocumentNode '.
            'compiled into a value that does not '.
            'implement ElementInterface: string'
        );

        require_once __DIR__.'/../../TestCompiler.php';
        $compiler = new TestCompiler();
        $compiler->compile('extends layout');
    }

    /**
     * @expectedException \Phug\CompilerException
     */
    public function testFileNotFoundException()
    {
        $this->expectMessageToBeThrown(
            'Source file /missing not found'
        );

        $compiler = new Compiler([
            'paths' => [__DIR__.'/../../../templates'],
        ]);
        $compiler->compile('include /missing');
    }

    /**
     * @covers \Phug\Compiler::resolve
     * @covers \Phug\Compiler::getFileContents
     */
    public function testNotFoundTemplate()
    {
        $compiler = new Compiler([
            'not_found_template' => 'div Page not found',
            'paths'              => [__DIR__.'/../../../templates'],
        ]);
        $php = $compiler->compile(implode("\n", [
            'p A',
            'include /missing',
            'p B',
        ]));

        self::assertSame('<p>A</p><div>Page not found</div><p>B</p>', $php);
    }

    /**
     * @covers            \Phug\Compiler::throwException
     *
     * @expectedException \Phug\CompilerException
     */
    public function testFileNotFoundInFileException()
    {
        $base = __DIR__.'/../../../templates';
        $file = realpath($base.DIRECTORY_SEPARATOR.'include-wrong-path.pug');
        $this->expectMessageToBeThrown(
            'Path: '.$file
        );
        $compiler = new Compiler([
            'paths' => [$base],
        ]);
        $compiler->compileFile($file);
    }
}
