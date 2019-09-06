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
use Phug\Lexer;
use Phug\Lexer\Scanner\AssignmentScanner;
use Phug\Lexer\Scanner\AttributeScanner;
use Phug\Lexer\Scanner\BlockScanner;
use Phug\Lexer\Scanner\CaseScanner;
use Phug\Lexer\Scanner\ClassScanner;
use Phug\Lexer\Scanner\CodeScanner;
use Phug\Lexer\Scanner\CommentScanner;
use Phug\Lexer\Scanner\ConditionalScanner;
use Phug\Lexer\Scanner\DoctypeScanner;
use Phug\Lexer\Scanner\DoScanner;
use Phug\Lexer\Scanner\DynamicTagScanner;
use Phug\Lexer\Scanner\EachScanner;
use Phug\Lexer\Scanner\ExpansionScanner;
use Phug\Lexer\Scanner\ExpressionScanner;
use Phug\Lexer\Scanner\FilterScanner;
use Phug\Lexer\Scanner\ForScanner;
use Phug\Lexer\Scanner\IdScanner;
use Phug\Lexer\Scanner\ImportScanner;
use Phug\Lexer\Scanner\IndentationScanner;
use Phug\Lexer\Scanner\KeywordScanner;
use Phug\Lexer\Scanner\MarkupScanner;
use Phug\Lexer\Scanner\MixinCallScanner;
use Phug\Lexer\Scanner\MixinScanner;
use Phug\Lexer\Scanner\NewLineScanner;
use Phug\Lexer\Scanner\TagScanner;
use Phug\Lexer\Scanner\TextBlockScanner;
use Phug\Lexer\Scanner\TextLineScanner;
use Phug\Lexer\Scanner\VariableScanner;
use Phug\Lexer\Scanner\WhenScanner;
use Phug\Lexer\Scanner\WhileScanner;
use Phug\Lexer\Scanner\YieldScanner;
use Phug\Lexer\Token\AssignmentToken;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\AutoCloseToken;
use Phug\Lexer\Token\BlockToken;
use Phug\Lexer\Token\CaseToken;
use Phug\Lexer\Token\ClassToken;
use Phug\Lexer\Token\CodeToken;
use Phug\Lexer\Token\CommentToken;
use Phug\Lexer\Token\ConditionalToken;
use Phug\Lexer\Token\DoctypeToken;
use Phug\Lexer\Token\DoToken;
use Phug\Lexer\Token\EachToken;
use Phug\Lexer\Token\ExpansionToken;
use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\FilterToken;
use Phug\Lexer\Token\ForToken;
use Phug\Lexer\Token\IdToken;
use Phug\Lexer\Token\ImportToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Lexer\Token\InterpolationStartToken;
use Phug\Lexer\Token\KeywordToken;
use Phug\Lexer\Token\MixinCallToken;
use Phug\Lexer\Token\MixinToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TagInterpolationEndToken;
use Phug\Lexer\Token\TagInterpolationStartToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Lexer\Token\VariableToken;
use Phug\Lexer\Token\WhenToken;
use Phug\Lexer\Token\WhileToken;
use Phug\Lexer\Token\YieldToken;
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
use Phug\Parser\State;
use Phug\Parser\TokenHandler\AssignmentTokenHandler;
use Phug\Parser\TokenHandler\AttributeEndTokenHandler;
use Phug\Parser\TokenHandler\AttributeStartTokenHandler;
use Phug\Parser\TokenHandler\AttributeTokenHandler;
use Phug\Parser\TokenHandler\AutoCloseTokenHandler;
use Phug\Parser\TokenHandler\BlockTokenHandler;
use Phug\Parser\TokenHandler\CaseTokenHandler;
use Phug\Parser\TokenHandler\ClassTokenHandler;
use Phug\Parser\TokenHandler\CodeTokenHandler;
use Phug\Parser\TokenHandler\CommentTokenHandler;
use Phug\Parser\TokenHandler\ConditionalTokenHandler;
use Phug\Parser\TokenHandler\DoctypeTokenHandler;
use Phug\Parser\TokenHandler\DoTokenHandler;
use Phug\Parser\TokenHandler\EachTokenHandler;
use Phug\Parser\TokenHandler\ExpansionTokenHandler;
use Phug\Parser\TokenHandler\ExpressionTokenHandler;
use Phug\Parser\TokenHandler\FilterTokenHandler;
use Phug\Parser\TokenHandler\ForTokenHandler;
use Phug\Parser\TokenHandler\IdTokenHandler;
use Phug\Parser\TokenHandler\ImportTokenHandler;
use Phug\Parser\TokenHandler\IndentTokenHandler;
use Phug\Parser\TokenHandler\InterpolationEndTokenHandler;
use Phug\Parser\TokenHandler\InterpolationStartTokenHandler;
use Phug\Parser\TokenHandler\KeywordTokenHandler;
use Phug\Parser\TokenHandler\MixinCallTokenHandler;
use Phug\Parser\TokenHandler\MixinTokenHandler;
use Phug\Parser\TokenHandler\NewLineTokenHandler;
use Phug\Parser\TokenHandler\OutdentTokenHandler;
use Phug\Parser\TokenHandler\TagInterpolationEndTokenHandler;
use Phug\Parser\TokenHandler\TagInterpolationStartTokenHandler;
use Phug\Parser\TokenHandler\TagTokenHandler;
use Phug\Parser\TokenHandler\TextTokenHandler;
use Phug\Parser\TokenHandler\VariableTokenHandler;
use Phug\Parser\TokenHandler\WhenTokenHandler;
use Phug\Parser\TokenHandler\WhileTokenHandler;
use Phug\Parser\TokenHandler\YieldTokenHandler;
use Phug\Phug;
use Phug\Renderer\Adapter\EvalAdapter;
use Phug\Renderer\Adapter\FileAdapter;
use Phug\Renderer\Adapter\StreamAdapter;
use Phug\Test\Utils\TCompiler;
use Phug\Test\Utils\TParser;
use Phug\Util\OptionInterface;

/**
 * @coversDefaultClass \Phug\Partial\FacadeOptionsTrait
 */
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
        if (is_string($expected) && is_string($actual)) {
            foreach (['expected', 'actual'] as $varName) {
                $$varName = trim(preg_replace('/\s+/', ' ', $$varName));
            }
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
            'dependencies_storage' => 'pugModule',
            'default_format'       => BasicFormat::class,
            'formats'              => [
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
            'short_open_tag_fix' => 'auto',
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

    public function testParserOptions()
    {
        self::assertOptions([
            'lexer_class_name'        => Lexer::class,
            'parser_state_class_name' => State::class,
            'parser_modules'          => [],
            'keywords'                => [],
            'detailed_dump'           => false,
            'token_handlers'          => [
                AssignmentToken::class             => AssignmentTokenHandler::class,
                AttributeEndToken::class           => AttributeEndTokenHandler::class,
                AttributeStartToken::class         => AttributeStartTokenHandler::class,
                AttributeToken::class              => AttributeTokenHandler::class,
                AutoCloseToken::class              => AutoCloseTokenHandler::class,
                BlockToken::class                  => BlockTokenHandler::class,
                YieldToken::class                  => YieldTokenHandler::class,
                CaseToken::class                   => CaseTokenHandler::class,
                ClassToken::class                  => ClassTokenHandler::class,
                CodeToken::class                   => CodeTokenHandler::class,
                CommentToken::class                => CommentTokenHandler::class,
                ConditionalToken::class            => ConditionalTokenHandler::class,
                DoToken::class                     => DoTokenHandler::class,
                DoctypeToken::class                => DoctypeTokenHandler::class,
                EachToken::class                   => EachTokenHandler::class,
                ExpansionToken::class              => ExpansionTokenHandler::class,
                ExpressionToken::class             => ExpressionTokenHandler::class,
                FilterToken::class                 => FilterTokenHandler::class,
                ForToken::class                    => ForTokenHandler::class,
                IdToken::class                     => IdTokenHandler::class,
                InterpolationStartToken::class     => InterpolationStartTokenHandler::class,
                InterpolationEndToken::class       => InterpolationEndTokenHandler::class,
                ImportToken::class                 => ImportTokenHandler::class,
                IndentToken::class                 => IndentTokenHandler::class,
                MixinCallToken::class              => MixinCallTokenHandler::class,
                MixinToken::class                  => MixinTokenHandler::class,
                NewLineToken::class                => NewLineTokenHandler::class,
                OutdentToken::class                => OutdentTokenHandler::class,
                TagInterpolationStartToken::class  => TagInterpolationStartTokenHandler::class,
                TagInterpolationEndToken::class    => TagInterpolationEndTokenHandler::class,
                KeywordToken::class                => KeywordTokenHandler::class,
                TagToken::class                    => TagTokenHandler::class,
                TextToken::class                   => TextTokenHandler::class,
                VariableToken::class               => VariableTokenHandler::class,
                WhenToken::class                   => WhenTokenHandler::class,
                WhileToken::class                  => WhileTokenHandler::class,
            ],
            'on_parse'       => null,
            'on_document'    => null,
            'on_state_enter' => null,
            'on_state_leave' => null,
            'on_state_store' => null,
        ], Phug::getRenderer()->getCompiler()->getParser());
    }

    public function testLexerOptions()
    {
        self::assertOptions([
            'lexer_state_class_name' => Lexer\State::class,
            'level'                  => 0,
            'indent_style'           => null,
            'indent_width'           => null,
            'allow_mixed_indent'     => true,
            'encoding'               => null,
            'lexer_modules'          => [],
            'keywords'               => [],
            'scanners'               => [
                'new_line'    => NewLineScanner::class,
                'indent'      => IndentationScanner::class,
                'import'      => ImportScanner::class,
                'block'       => BlockScanner::class,
                'yield'       => YieldScanner::class,
                'conditional' => ConditionalScanner::class,
                'each'        => EachScanner::class,
                'case'        => CaseScanner::class,
                'when'        => WhenScanner::class,
                'do'          => DoScanner::class,
                'while'       => WhileScanner::class,
                'for'         => ForScanner::class,
                'mixin'       => MixinScanner::class,
                'mixin_call'  => MixinCallScanner::class,
                'doctype'     => DoctypeScanner::class,
                'keyword'     => KeywordScanner::class,
                'tag'         => TagScanner::class,
                'class'       => ClassScanner::class,
                'id'          => IdScanner::class,
                'attribute'   => AttributeScanner::class,
                'assignment'  => AssignmentScanner::class,
                'variable'    => VariableScanner::class,
                'comment'     => CommentScanner::class,
                'filter'      => FilterScanner::class,
                'expression'  => ExpressionScanner::class,
                'code'        => CodeScanner::class,
                'markup'      => MarkupScanner::class,
                'expansion'   => ExpansionScanner::class,
                'dynamic_tag' => DynamicTagScanner::class,
                'text_block'  => TextBlockScanner::class,
                'text_line'   => TextLineScanner::class,
            ],
            'on_lex'     => null,
            'on_lex_end' => null,
            'on_token'   => null,
        ], Phug::getRenderer()->getCompiler()->getParser()->getLexer());
    }

    /**
     * @covers \Phug\Phug::__callStatic
     * @covers ::resetFacadeOptions
     * @covers ::callOption
     * @covers ::isOptionMethod
     * @covers ::getFacadeOptions
     */
    public function testInitialOption()
    {
        include_once __DIR__.'/Utils/TCompiler.php';
        include_once __DIR__.'/Utils/TParser.php';
        Phug::reset();
        Phug::setOption('parser_class_name', TParser::class);
        Phug::setOption('compiler_class_name', TCompiler::class);
        self::assertInstanceOf(TCompiler::class, Phug::getRenderer()->getCompiler());
        self::assertInstanceOf(TParser::class, Phug::getRenderer()->getCompiler()->getParser());
    }
}
