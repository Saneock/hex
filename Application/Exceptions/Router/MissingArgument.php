<?php
namespace Exception\Router;

class MissingArgument extends \Exception\BaseException
{
	public function __construct($argument = null, $pattern = null, $type = false)
    {
        $trace = $this->getTrace();

        parent::__construct("Missing argument in".($type ? ' '.$type : '')." request. [Argument: $argument]".($pattern ? " [Pattern: $pattern]" : ''));
    }
}