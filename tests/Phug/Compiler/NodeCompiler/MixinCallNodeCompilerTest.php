<?php

namespace Phug\Test\Compiler\NodeCompiler;

use InvalidArgumentException;
use Phug\Compiler;
use Phug\Compiler\NodeCompiler\MixinCallNodeCompiler;
use Phug\CompilerException;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\NodeCompiler\MixinCallNodeCompiler
 */
class MixinCallNodeCompilerTest extends AbstractCompilerTest
{
    /**
     * @group  mixins
     *
     * @covers ::<public>
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::<public>
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileAnonymousBlock
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileNamedBlock
     * @covers \Phug\Compiler\NodeCompiler\MixinNodeCompiler::<public>
     */
    public function testCompile()
    {
        $this->assertRenderFile(
            [
                '<section>',
                '<div class="ab">',
                '<div class="a"></div>',
                '<div class="b"></div>',
                '</div>',
                '<div>',
                '</div>',
                '</section>',
                '<div>bar</div>',
                '<article>append</article>',
                '<div class="ab">',
                '<div class="a">a</div>',
                '<div class="b">b</div>',
                '</div>',
                '<div>',
                '<h1>1</h1>',
                '</div>',
                '<article>prepend</article>',
                '<p>footer-foo</p>',
                '<p class="biz">bar</p>',
                '<div>footer</div>',
            ],
            __DIR__.'/../../../templates/mixins-test.pug'
        );
    }

    /**
     * @group  mixins
     *
     * @covers ::<public>
     */
    public function testCompileNestedMixins()
    {
        $this->assertRenderFile(
            [
                '<p>1</p>',
                '<p>2</p>',
                '<p>2</p>',
            ],
            __DIR__.'/../../../templates/nested-mixins-local-init.pug'
        );
        $this->assertRenderFile(
            [
                '<p>1</p>',
                '<p>2</p>',
                '<p>2</p>',
            ],
            __DIR__.'/../../../templates/nested-mixins.pug',
            [],
            [
                'text' => '2',
            ]
        );
    }

    /**
     * @group  mixins
     *
     * @covers ::<public>
     */
    public function testCompileVariadicMixin()
    {
        $this->assertRender(
            [
                '<p>1</p>',
                '<i>2</i>',
                '<i>3</i>',
            ],
            [
                'mixin variadicMixin($a, ...$b)'."\n",
                '  p=$a'."\n",
                '  each $c in $b'."\n",
                '    i=$c'."\n",
                '+variadicMixin(1, 2, 3)',
            ]
        );
    }

    /**
     * @group  mixins
     *
     * @covers ::<public>
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::<public>
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileAnonymousBlock
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileNamedBlock
     * @covers \Phug\Compiler\NodeCompiler\MixinNodeCompiler::<public>
     */
    public function testDoubleBlock()
    {
        $compiler = new Compiler();
        $this->assertRenderFile(
            [
                '<header>HelloHello</header>',
                '<footer>ByeBye</footer>',
            ],
            __DIR__.'/../../../templates/mixin-double-block.pug'
        );
    }

    /**
     * @group  mixins
     *
     * @covers ::<public>
     * @covers \Phug\Compiler::compileDocument
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileAnonymousBlock
     */
    public function testDynamicMixins()
    {
        $this->assertRender(
            [
                '<div>bar</div>',
            ],
            [
                'mixin bar'."\n",
                '  div bar'."\n",
                '+#{$foo}',
            ],
            [],
            [
                'foo' => 'bar',
            ]
        );
        $this->setUp();
        $this->assertRender(
            [
                '<div class="foo" bar="biz">',
                '1#2#3-4<em>Message</em>',
                '</div>',
                '<div bar="biz">',
                '1#2#3-4<em>Message</em>',
                '</div>',
                '<p>42</p>',
            ],
            [
                '- $bar = 40'."\n",
                'mixin bar(a, b, ...c)'."\n",
                '  - $bar++'."\n",
                '  div&attributes($attributes)'."\n",
                '    =$a."#".$b."#".implode("-", $c)'."\n",
                '    block'."\n",
                '+#{$foo}(1, 2, 3, 4).foo(bar="biz")'."\n",
                '  em Message'."\n",
                '+#{$foo}(1, 2, 3, 4)&attributes(["bar" => "biz" ])'."\n",
                '  em Message'."\n",
                'p=$bar',
            ],
            [],
            [
                'foo' => 'bar',
            ]
        );
    }

    /**
     * @group  mixins
     *
     * @covers ::<public>
     * @covers \Phug\Compiler\NodeCompiler\MixinNodeCompiler::<public>
     */
    public function testOuterNodes()
    {
        $this->assertRender(
            [
                '<div></div>',
                '<footer><footer><p>bar</p><span>i</span></footer></footer>',
            ],
            [
                'div: mixin bar'."\n",
                '  p bar'."\n",
                '  block'."\n",
                'footer'."\n",
                '  footer: +#{$foo}: span i',
            ],
            [],
            [
                'foo' => 'bar',
            ]
        );
        $this->setUp();
        $this->assertRender(
            [
                '<div class="foo" bar="biz">',
                '1#2#3-4<em>Message</em>',
                '</div>',
                '<div bar="biz">',
                '1#2#3-4<em>Message</em>',
                '</div>',
                '<p>42</p>',
            ],
            [
                '- $bar = 40'."\n",
                'mixin bar(a, b, ...c)'."\n",
                '  - $bar++'."\n",
                '  div&attributes($attributes)'."\n",
                '    =$a."#".$b."#".implode("-", $c)'."\n",
                '    block'."\n",
                '+#{$foo}(1, 2, 3, 4).foo(bar="biz")'."\n",
                '  em Message'."\n",
                '+#{$foo}(1, 2, 3, 4)&attributes(["bar" => "biz" ])'."\n",
                '  em Message'."\n",
                'p=$bar',
            ],
            [],
            [
                'foo' => 'bar',
            ]
        );
    }

    /**
     * @group mixins
     */
    public function testMissingMixin()
    {
        $code = [
            'div'."\n",
            '  p: +yolo()',
        ];
        $exception = null;

        try {
            $this->assertRender(
                [],
                $code,
                [
                    'debug' => true,
                ]
            );
        } catch (\InvalidArgumentException $e) {
            ob_end_clean();
            $exception = $e;
        } catch (\Exception $e) {
            ob_end_clean();
            $exception = $e;
        } catch (\Throwable $e) {
            ob_end_clean();
            $exception = $e;
        }

        self::assertInstanceOf(\InvalidArgumentException::class, $exception);
        self::assertSame('Unknown yolo mixin called.', $exception->getMessage());

        $php = $this->compiler->compile(implode("\n", $code));
        $lines = explode("\n", $php);
        $before = implode("\n", array_slice($lines, 0, $exception->getLine()));
        $pos = strrpos($before, '// PUG_DEBUG:');
        $after = str_replace('?><?php', '', substr($php, $pos));

        self::assertSame('</p></div>', explode('?>', $after)[1]);
    }

    /**
     * @group  mixins
     *
     * @covers ::<public>
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::<public>
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileAnonymousBlock
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileNamedBlock
     * @covers \Phug\Compiler\NodeCompiler\MixinNodeCompiler::<public>
     */
    public function testMixinAttributes()
    {
        $this->enableJsPhpize();
        $this->assertRenderFile(
            preg_replace(
                '/(\S)\/>/',
                '$1 />',
                preg_replace(
                    '/\n\s*/',
                    '',
                    file_get_contents(__DIR__.'/../../../templates/mixin.attrs.html')
                )
            ),
            __DIR__.'/../../../templates/mixin.attrs.pug'
        );
    }

    /**
     * @group  mixins
     *
     * @covers ::<public>
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::<public>
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileAnonymousBlock
     * @covers \Phug\Compiler\NodeCompiler\BlockNodeCompiler::compileNamedBlock
     * @covers \Phug\Compiler\NodeCompiler\MixinNodeCompiler::<public>
     */
    public function testMixinBlocks()
    {
        $this->enableJsPhpize();
        $this->assertRenderFile(
            preg_replace(
                '/(\S)\/>/',
                '$1 />',
                preg_replace(
                    '/\n\s*/',
                    '',
                    file_get_contents(__DIR__.'/../../../templates/mixin.blocks.html')
                )
            ),
            __DIR__.'/../../../templates/mixin.blocks.pug'
        );
    }

    /**
     * @group mixins
     *
     * @covers ::<public>
     */
    public function testException()
    {
        $this->expectMessageToBeThrown(
            'Unexpected Phug\Parser\Node\ElementNode '.
            'given to mixin call compiler.',
            CompilerException::class
        );

        $mixinCallCompiler = new MixinCallNodeCompiler(new Compiler());
        $mixinCallCompiler->compileNode(new ElementNode());
    }

    /**
     * @group mixins
     *
     * @covers ::<public>
     */
    public function testUnknownMixin()
    {
        $this->expectMessageToBeThrown(
            'Unknown undef mixin called.',
            InvalidArgumentException::class
        );

        $php = (new Compiler([
            'debug' => true,
        ]))->compile('+undef()');
        eval('?>'.$php);
    }
}
