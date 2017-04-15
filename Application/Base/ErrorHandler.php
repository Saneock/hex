<?php
namespace Hex\Base;

use Hex;
use Hex\Base\Object;

/**
 * ErrorHandler manages the errors
 */
class ErrorHandler extends Object
{    
    public $handler;

    public function register()
    {
        $this->handler = \PhpConsole\Handler::getInstance();
        $this->handler->start(); // start handling PHP errors & exceptions
        $this->handler->getConnector()->setSourcesBasePath($_SERVER['DOCUMENT_ROOT']); // so files paths on client will be shorter (optional)
        PhpConsole\Helper::register();
    }
}
