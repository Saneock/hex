<?php
namespace Exception;

class InvalidCall extends BaseException
{
	public function __construct($message = null)
    {
        parent::__construct($message);
    }
}