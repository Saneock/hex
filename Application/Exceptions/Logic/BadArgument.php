<?php
namespace Exception\Logic;

class BadArgument extends \Exception\BaseException
{
	public function __construct($argument = null, $argument_value = null, $message = null)
    {
        $trace = $this->getTrace();

        parent::__construct("Bad argument. [Argument: $argument] [Value: $argument_value] [Method: {$trace[0]['class']}{$trace[0]['type']}{$trace[0]['function']}] [Used in {$trace[0]['file']} on line {$trace[0]['line']}] $message");
    }
}