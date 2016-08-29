<?php
namespace Exception\Database;

class Fetch extends \Exception\BaseException
{
	public function __construct($message = null, $code = 0, $e = null)
    {
        $trace = $e->getTrace();
        $this->file = $trace[1]['file'];
        
        parent::__construct($message, $code, $e);
    }
}