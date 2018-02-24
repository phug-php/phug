<?php

namespace Phug\Test\Extension;

use Phug\AbstractExtension;

class TwigExtension extends AbstractExtension
{
    public function getOptions()
    {
        return [
            'debug' => false,
        ];
    }

    public function getPhpTokenHandlers()
    {
        return [
            T_VARIABLE => function ($variable) {
                return $variable;
            },
        ];
    }

    public function getPatterns()
    {
        return [
            'expression_in_text'     => '%s',
            'php_display_code'       => '{{ %s }}',
            'php_handle_code'        => '{%% %s %%}',
            'display_comment'        => '{# %s #}',
            'html_expression_escape' => '%s|e',
        ];
    }
}
