<?php
namespace Hex\Log;

use Hex;
use Hex\Base\Object;

/**
 * Logger records logged messages in memory and sends them to different targets
 */
class Logger extends Object
{
    public $name = 'hex';

    public $dispatchers;

    /**
     * @var logger class
     */
    protected $logger;

    protected static $_startTime;

    
    public function init()
    {
        parent::init();

        $this->logger = new \Monolog\Logger($this->name);

        if (is_array($this->dispatchers)) {
            self::$_startTime = microtime(true);

            foreach ($this->dispatchers as $dispatcher) {
                $this->logger->pushHandler($dispatcher);
            }
        }
                
        $this->logger->pushProcessor(function ($record) {
            $record['extra']['timestamp'] = $record['context']['timestamp'] = microtime(true) - self::$_startTime;
            return $record;
        });

        $this->logger->pushProcessor(new \Monolog\Processor\MemoryPeakUsageProcessor(true, false));
    }

    public function __call($name, $arguments)
    {
        call_user_func_array([$this->logger, $name], $arguments);
    }
}
