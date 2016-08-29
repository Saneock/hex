<?php
namespace Exception\Database;

class Query extends \Exception\BaseException
{
	public function __construct($sql_query = null, $code = 0, $e = null)
    {
        $trace = $e->getTrace();
        $this->file = $trace[1]['file'];
        $this->line = $trace[1]['line'];
        
        parent::__construct($e->getMessage().' in query: "'.$sql_query.'"', $code, $e);
    }
}