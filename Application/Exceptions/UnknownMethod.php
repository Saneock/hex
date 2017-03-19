<?php
namespace Exception;

class UnknownMethod extends BaseException
{
    public function __construct($message = null, $code = 0, $previous = null)
    {
        $trace = $this->getTrace();
        $this->file = $trace[0]['file'];
        $this->line = $trace[0]['line'];

        parent::__construct($message, $code, $previous);
    }
}