<?php

namespace Phug\Renderer\Adapter;

use Phug\Renderer\AbstractAdapter;

class EvalAdapter extends AbstractAdapter
{
    public function display($__pug_php, array $__pug_parameters)
    {
        $execution = function () use ($__pug_php, &$__pug_parameters) {
            extract($__pug_parameters);
            eval('?>'.$__pug_php);
        };

        if (isset($__pug_parameters['this'])) {
            $execution = $execution->bindTo($__pug_parameters['this']);
            unset($__pug_parameters['this']);
        }

        $__pug_parameters['__pug_adapter'] = $this;

        $execution();
    }
}
