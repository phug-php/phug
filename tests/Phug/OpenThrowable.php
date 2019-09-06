<?php

namespace Phug\Test;

use Exception;

class OpenThrowable extends Exception
{
    public function setCode($code)
    {
        $this->code = $code;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function setLine($line)
    {
        $this->line = $line;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function __toString()
    {
        return $this->getTraceAsString();
    }
}
