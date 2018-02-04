<?php

namespace Phug\Test\Util;

use Phug\Renderer;

class CustomRenderer extends Renderer
{
    private $output;

    public function __construct($output)
    {
        $this->output = is_array($output) ? 'array' : $output;
        parent::__construct();
    }

    public function displayFile($path, array $parameters = [])
    {
        echo $this->output;
    }
}
