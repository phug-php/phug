<?php

namespace Phug\Test;

use Phug\Formatter;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\Format\XhtmlFormat;

class TwigFormat extends XhtmlFormat
{
    public function __construct(Formatter $formatter = null)
    {
        parent::__construct($formatter);

        $nestedCodes = [];
        $codeBlocks = [];
        $this
            ->setOptionsDefaults([
                'php_token_handlers' => [
                    T_VARIABLE => function ($string) {
                        return $string;
                    },
                ],
            ])
            ->setPatterns([
                'class_attribute'        => '%s',
                'string_attribute'       => '%s',
                'expression_in_text'     => '%s',
                'html_expression_escape' => '%s | e',
                'php_handle_code'        => function ($input) use (&$formatter, &$nestedCodes, &$codeBlocks) {
                    $pugModuleName = '$'.$formatter->getOption('dependencies_storage');
                    if (strpos($input, $pugModuleName) !== false) {
                        $input = preg_replace_callback('/\{\[block:(\d+)\]\}/', function ($match) use (&$codeBlocks) {
                            return ' ?>'.$codeBlocks[intval($match[1])].'<?php ';
                        }, $input);

                        return "<?php $input ?>";
                    }

                    list($statement, $input) = explode(' ', $input, 2);
                    $statement = $statement === 'each' ? 'for' : $statement;
                    $input = $statement.' '.$input;
                    $hasBlocks = false;
                    $input = preg_replace_callback('/\{\[block:(\d+)\]\}/', function ($match) use (&$codeBlocks, &$hasBlocks) {
                        $hasBlocks = true;

                        return ' %}'.$codeBlocks[intval($match[1])].'{% ';
                    }, $input);
                    if ($hasBlocks) {
                        $input .= 'end'.$statement;
                    }

                    return "{% $input %}";
                },
                'php_nested_html' => '%s',
                'php_block_code'  => function ($input) use (&$codeBlocks) {
                    $id = count($codeBlocks);
                    $codeBlocks[] = $input;

                    return '{[block:'.$id.']}';
                },
                'php_display_code' => function ($input) use (&$formatter) {
                    $pugModuleName = '$'.$formatter->getOption('dependencies_storage');
                    if (strpos($input, $pugModuleName) !== false) {
                        return "<?= $input ?>";
                    }

                    return "{{ $input }}";
                },
                'display_comment' => '{# %s #}',
            ]);
    }

    protected function formatAttributes(MarkupElement $element)
    {
        $code = '';

        foreach ($element->getAttributes() as $attribute) {
            $code .= $this->format($attribute);
        }

        return $code;
    }
}
