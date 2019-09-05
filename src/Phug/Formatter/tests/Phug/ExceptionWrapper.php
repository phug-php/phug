<?php

namespace Phug\Test;

class ExceptionWrapper
{
    protected $code;
    protected $file;
    protected $line;
    protected $message;
    protected $trace;
    protected $exception;

    public function __construct()
    {
        include_once __DIR__.'/OpenThrowable.php';
        $this->exception = new OpenThrowable();
    }

    public function setCode($code)
    {
        $this->code = [$code];
    }

    public function getCode()
    {
        return $this->code ? $this->code[0] : $this->exception->getCode();
    }

    public function setFile($file)
    {
        $this->file = [$file];
    }

    public function getFile()
    {
        return $this->file ? $this->file[0] : $this->exception->getFile();
    }

    public function setLine($line)
    {
        $this->line = [$line];
    }

    public function getLine()
    {
        return $this->line ? $this->line[0] : $this->exception->getLine();
    }

    public function setMessage($message)
    {
        $this->message = [$message];
    }

    public function getMessage()
    {
        return $this->message ? $this->message[0] : $this->exception->getMessage();
    }

    public function setTrace($trace)
    {
        $this->trace = [$trace];
    }

    public function getTrace()
    {
        return $this->trace ? $this->trace[0] : $this->exception->getTrace();
    }

    public function getException()
    {
        return $this->exception;
    }

    public function __toString()
    {
        return $this->exception->getTraceAsString();
    }
}
