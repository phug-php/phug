<?php

namespace Phug\Compiler\Event;

use Phug\CompilerEvent;
use Phug\Event;

class OutputEvent extends Event
{
    private $compileEvent;
    private $output;

    /**
     * OutputEvent constructor.
     *
     * @param CompileEvent $compileEvent
     * @param string       $output
     */
    public function __construct(CompileEvent $compileEvent, $output)
    {
        parent::__construct(CompilerEvent::OUTPUT);

        $this->compileEvent = $compileEvent;
        $this->output = $output;
    }

    /**
     * @return CompileEvent
     */
    public function getCompileEvent()
    {
        return $this->compileEvent;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param string $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Prepend PHP code before the output (but after the namespace statement if present).
     *
     * @param string $code PHP code (whihout <?php ?> tags)
     */
    public function prependCode($code)
    {
        if (preg_match('/^(<\?(?:php)?\s+namespace\s\S.*)(((?:;|\n|\?>)[\s\S]*)?)$/U', $this->output, $matches)) {
            if (substr($matches[2], 0, 1) === ';') {
                $matches[1] .= ';';
                $matches[2] = substr($matches[2], 1);
            }

            $this->output = $matches[1].$code.$matches[2];

            return;
        }

        $this->output = "<?php$code?>".$this->output;
    }
}
