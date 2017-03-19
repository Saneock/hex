<?php
namespace Exception;

/**
 * Class BaseException
 */
abstract class BaseException extends \Exception implements \Interfaces\IException
{
    protected $message = 'Unknown exception';     // Exception message
    private   $string;                            // Unknown
    protected $code = 0;                       	  // User-defined exception code
    protected $file;                              // Source filename of exception
    protected $line;                              // Source line of exception
    private   $trace;                             // Unknown

    /**
     * BaseException constructor.
     * @param null $message
     * @param int $code
     */
    public function __construct($message = null, $code = 0, $previous = null)
    {
        if (!$message) {
            throw new $this('Unknown '. get_class($this));
        }
   
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->createMessage();
    }

    /**
     * Create default message
     *
     * @return string
     */
    protected function createMessage() : string
    {
        return get_class($this) . ': ' . self::bold($this->message) . ' ' .
        ' in ' . self::bold($this->file) . ' on line ' . self::bold($this->line) . "\n";
    }

    /**
     * Makes text bold
     *
     * @param string $text
     * @return string
     */
    protected function bold($text) : string
    {
        return "<b>$text</b>";
    }

    /**
     * Makes text italic
     *
     * @param string $text
     * @return string
     */
    protected function italic($text) : string
    {
        return "<i>$text</i>";
    }
}