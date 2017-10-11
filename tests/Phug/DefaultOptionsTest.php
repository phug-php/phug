<?php

namespace Phug\Test;

use Closure;
use Phug\Compiler;
use Phug\Compiler\Locator\FileLocator;
use Phug\Compiler\NodeCompiler\AssignmentListNodeCompiler;
use Phug\Compiler\NodeCompiler\AssignmentNodeCompiler;
use Phug\Compiler\NodeCompiler\AttributeListNodeCompiler;
use Phug\Compiler\NodeCompiler\AttributeNodeCompiler;
use Phug\Compiler\NodeCompiler\BlockNodeCompiler;
use Phug\Compiler\NodeCompiler\CaseNodeCompiler;
use Phug\Compiler\NodeCompiler\CodeNodeCompiler;
use Phug\Compiler\NodeCompiler\CommentNodeCompiler;
use Phug\Compiler\NodeCompiler\ConditionalNodeCompiler;
use Phug\Compiler\NodeCompiler\DoctypeNodeCompiler;
use Phug\Compiler\NodeCompiler\DocumentNodeCompiler;
use Phug\Compiler\NodeCompiler\DoNodeCompiler;
use Phug\Compiler\NodeCompiler\EachNodeCompiler;
use Phug\Compiler\NodeCompiler\ElementNodeCompiler;
use Phug\Compiler\NodeCompiler\ExpressionNodeCompiler;
use Phug\Compiler\NodeCompiler\FilterNodeCompiler;
use Phug\Compiler\NodeCompiler\ForNodeCompiler;
use Phug\Compiler\NodeCompiler\ImportNodeCompiler;
use Phug\Compiler\NodeCompiler\KeywordNodeCompiler;
use Phug\Compiler\NodeCompiler\MixinCallNodeCompiler;
use Phug\Compiler\NodeCompiler\MixinNodeCompiler;
use Phug\Compiler\NodeCompiler\TextNodeCompiler;
use Phug\Compiler\NodeCompiler\VariableNodeCompiler;
use Phug\Compiler\NodeCompiler\WhenNodeCompiler;
use Phug\Compiler\NodeCompiler\WhileNodeCompiler;
use Phug\Compiler\NodeCompiler\YieldNodeCompiler;
use Phug\Formatter;
use Phug\Formatter\Element\AssignmentElement;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\CommentElement;
use Phug\Formatter\Element\DoctypeElement;
use Phug\Formatter\Element\DocumentElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\Element\KeywordElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\Element\MixinCallElement;
use Phug\Formatter\Element\MixinElement;
use Phug\Formatter\Element\TextElement;
use Phug\Formatter\Element\VariableElement;
use Phug\Formatter\Format\BasicFormat;
use Phug\Formatter\Format\FramesetFormat;
use Phug\Formatter\Format\HtmlFormat;
use Phug\Formatter\Format\MobileFormat;
use Phug\Formatter\Format\OneDotOneFormat;
use Phug\Formatter\Format\PlistFormat;
use Phug\Formatter\Format\StrictFormat;
use Phug\Formatter\Format\TransitionalFormat;
use Phug\Formatter\Format\XhtmlFormat;
use Phug\Formatter\Format\XmlFormat;
use Phug\Parser;
use Phug\Parser\Node\AssignmentListNode;
use Phug\Parser\Node\AssignmentNode;
use Phug\Parser\Node\AttributeListNode;
use Phug\Parser\Node\AttributeNode;
use Phug\Parser\Node\BlockNode;
use Phug\Parser\Node\CaseNode;
use Phug\Parser\Node\CodeNode;
use Phug\Parser\Node\CommentNode;
use Phug\Parser\Node\ConditionalNode;
use Phug\Parser\Node\DoctypeNode;
use Phug\Parser\Node\DocumentNode;
use Phug\Parser\Node\DoNode;
use Phug\Parser\Node\EachNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\Node\FilterNode;
use Phug\Parser\Node\ForNode;
use Phug\Parser\Node\ImportNode;
use Phug\Parser\Node\KeywordNode;
use Phug\Parser\Node\MixinCallNode;
use Phug\Parser\Node\MixinNode;
use Phug\Parser\Node\TextNode;
use Phug\Parser\Node\VariableNode;
use Phug\Parser\Node\WhenNode;
use Phug\Parser\Node\WhileNode;
use Phug\Parser\Node\YieldNode;
use Phug\Phug;
use Phug\Renderer\Adapter\EvalAdapter;
use Phug\Renderer\Adapter\FileAdapter;
use Phug\Renderer\Adapter\StreamAdapter;
use Phug\Util\AssociativeStorage;
use Phug\Util\OptionInterface;

class DefaultOptionsTest extends AbstractPhugTest
{
    private static function isCallable($expected)
    {
        return is_callable($expected) ||
            $expected instanceof Closure ||
            (is_array($expected) && method_exists($expected[0], $expected[1]));
    }

    private static function assertOptionValue($expected, $actual, $name)
    {
        $message = "$name option should be ".var_export($expected, true).' by default.';
        if (self::isCallable($actual) && self::isCallable($expected)) {
            self::assertTrue(true, $message);

            return;
        }

        self::assertSame($expected, $actual, $message);
    }

    private static function assertOptions(array $options, OptionInterface $instance)
    {
        foreach ($options as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $subName => $subValue) {
                    self::assertOptionValue($subValue, $instance->getOption($name)[$subName], "$name.$subName");
                }

                continue;
            }

            self::assertOptionValue($value, $instance->getOption($name), $name);
        }
    }

    public function testRendererOptions()
    {
        self::assertOptions([
            'debug'               => false,
            'enable_profiler'     => false,
            'up_to_date_check'    => true,
            'keep_base_name'      => false,
            'error_handler'       => null,
            'html_error'          => php_sapi_name() !== 'cli',
            'color_support'       => null,
            'error_context_lines' => 7,
            'adapter_class_name'  => EvalAdapter::class,
            'shared_variables'    => [],
            'modules'             => [],
            'compiler_class_name' => Compiler::class,
            'self'                => false,
            'on_render'           => null,
            'on_html'             => null,
            'filters'             => [
                'cdata' => function () {
                },
            ],
        ], Phug::getRenderer(['debug' => false]));

        Phug::reset();

        self::assertOptions([
            'debug'               => true,
            'enable_profiler'     => true,
            'memory_limit'        => 0x3200000,
            'execution_max_time'  => 30000,
            'profiler'            => [
                'display' => false,
                'log'     => false,
            ],
            'up_to_date_check'    => true,
            'keep_base_name'      => false,
            'error_handler'       => null,
            'html_error'          => php_sapi_name() !== 'cli',
            'color_support'       => null,
            'error_context_lines' => 7,
            'adapter_class_name'  => EvalAdapter::class,
            'shared_variables'    => [],
            'modules'             => [],
            'compiler_class_name' => Compiler::class,
            'self'                => false,
            'on_render'           => null,
            'on_html'             => null,
            'filters'             => [
                'cdata' => function () {
                },
            ],
        ], Phug::getRenderer());

        self::assertOptions([
            'stream_name'   => 'pug',
            'stream_suffix' => '.stream',
        ], new StreamAdapter(Phug::getRenderer(), []));

        self::assertOptions([
            'cache_dir'         => null,
            'tmp_dir'           => sys_get_temp_dir(),
            'tmp_name_function' => 'tempnam',
            'up_to_date_check'  => true,
            'keep_base_name'    => false,
        ], new FileAdapter(Phug::getRenderer(), []));
    }

    public function testCompilerOptions()
    {
        self::assertOptions([
            'paths'                => [],
            'default_tag'          => 'div',
            'default_doctype'      => 'html',
            'extensions'           => ['', '.pug', '.jade'],
            'get_file_contents'    => 'file_get_contents',
            'on_compile'           => null,
            'on_output'            => null,
            'on_node'              => null,
            'on_element'           => null,
            'filters'              => [],
            'filter_resolvers'     => [],
            'includes'             => [],
            'parser_class_name'    => Parser::class,
            'formatter_class_name' => Formatter::class,
            'locator_class_name'   => FileLocator::class,
            'mixins_storage_mode'  => AssociativeStorage::REPLACE,
            'compiler_modules'     => [],
            'node_compilers'       => [
                AssignmentListNode::class => AssignmentListNodeCompiler::class,
                AssignmentNode::class     => AssignmentNodeCompiler::class,
                AttributeListNode::class  => AttributeListNodeCompiler::class,
                AttributeNode::class      => AttributeNodeCompiler::class,
                BlockNode::class          => BlockNodeCompiler::class,
                YieldNode::class          => YieldNodeCompiler::class,
                CaseNode::class           => CaseNodeCompiler::class,
                CodeNode::class           => CodeNodeCompiler::class,
                CommentNode::class        => CommentNodeCompiler::class,
                ConditionalNode::class    => ConditionalNodeCompiler::class,
                DoctypeNode::class        => DoctypeNodeCompiler::class,
                DocumentNode::class       => DocumentNodeCompiler::class,
                DoNode::class             => DoNodeCompiler::class,
                EachNode::class           => EachNodeCompiler::class,
                KeywordNode::class        => KeywordNodeCompiler::class,
                ElementNode::class        => ElementNodeCompiler::class,
                ExpressionNode::class     => ExpressionNodeCompiler::class,
                FilterNode::class         => FilterNodeCompiler::class,
                ForNode::class            => ForNodeCompiler::class,
                ImportNode::class         => ImportNodeCompiler::class,
                MixinCallNode::class      => MixinCallNodeCompiler::class,
                MixinNode::class          => MixinNodeCompiler::class,
                TextNode::class           => TextNodeCompiler::class,
                VariableNode::class       => VariableNodeCompiler::class,
                WhenNode::class           => WhenNodeCompiler::class,
                WhileNode::class          => WhileNodeCompiler::class,
            ],
        ], Phug::getRenderer()->getCompiler());
    }

    public function testFormatterOptions()
    {
        self::assertOptions([
            'dependencies_storage'        => 'pugModule',
            'dependencies_storage_getter' => null,
            'default_format'              => BasicFormat::class,
            'formats'                     => [
                'basic'        => BasicFormat::class,
                'frameset'     => FramesetFormat::class,
                'html'         => HtmlFormat::class,
                'mobile'       => MobileFormat::class,
                '1.1'          => OneDotOneFormat::class,
                'plist'        => PlistFormat::class,
                'strict'       => StrictFormat::class,
                'transitional' => TransitionalFormat::class,
                'xml'          => XmlFormat::class,
            ],
            'formatter_modules'     => [],
            'on_format'             => null,
            'on_new_format'         => null,
            'on_dependency_storage' => null,
        ], Phug::getRenderer()->getCompiler()->getFormatter());

        self::assertOptions([
            'pattern'            => function () {
            },
            'patterns'           => [
                'class_attribute'        => XhtmlFormat::CLASS_ATTRIBUTE,
                'string_attribute'       => XhtmlFormat::STRING_ATTRIBUTE,
                'expression_in_text'     => XhtmlFormat::EXPRESSION_IN_TEXT,
                'html_expression_escape' => XhtmlFormat::HTML_EXPRESSION_ESCAPE,
                'html_text_escape'       => XhtmlFormat::HTML_TEXT_ESCAPE,
                'pair_tag'               => XhtmlFormat::PAIR_TAG,
                'transform_expression'   => XhtmlFormat::TRANSFORM_EXPRESSION,
                'transform_code'         => XhtmlFormat::TRANSFORM_CODE,
                'transform_raw_code'     => XhtmlFormat::TRANSFORM_RAW_CODE,
                'php_handle_code'        => XhtmlFormat::PHP_HANDLE_CODE,
                'php_display_code'       => XhtmlFormat::PHP_DISPLAY_CODE,
                'php_block_code'         => XhtmlFormat::PHP_BLOCK_CODE,
                'php_nested_html'        => XhtmlFormat::PHP_NESTED_HTML,
                'display_comment'        => XhtmlFormat::DISPLAY_COMMENT,
                'doctype'                => XhtmlFormat::DOCTYPE,
                'custom_doctype'         => XhtmlFormat::CUSTOM_DOCTYPE,
                'debug_comment'          => function () {
                }, // XhtmlFormat::DEBUG_COMMENT overridden
                'debug'                  => function () {
                },
            ],
            'pretty'             => false,
            'element_handlers'   => [
                AssignmentElement::class => function () {
                },
                AttributeElement::class  => function () {
                },
                CodeElement::class       => function () {
                },
                CommentElement::class    => function () {
                },
                ExpressionElement::class => function () {
                },
                DoctypeElement::class    => function () {
                },
                DocumentElement::class   => function () {
                },
                KeywordElement::class    => function () {
                },
                MarkupElement::class     => function () {
                },
                MixinCallElement::class  => function () {
                },
                MixinElement::class      => function () {
                },
                TextElement::class       => function () {
                },
                VariableElement::class   => function () {
                },
            ],
            'php_token_handlers' => [
                T_VARIABLE => function () {
                },
            ],
            'mixin_merge_mode'   => 'replace',
        ], Phug::getRenderer()->getCompiler()->getFormatter()->getFormatInstance());
    }
}
