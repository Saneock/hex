<?php
namespace Exception\Database;

class Connect extends \Exception\BaseException
{
	public function __construct($message = null, $code = 0, $ex = null)
    {        
        parent::__construct($message.($ex ? ' '.$ex->getMessage() : ''), $code);
    }
}