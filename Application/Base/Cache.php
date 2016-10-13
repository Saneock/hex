<?php
namespace Hex\Base;

/**
 * Работа с кэшем
 *
 * Class Cache
 * @package Base
 */
class CacheCore extends \Abstracts\Singleton
{
    protected static $instance;

	/**
     * Объект кэша с использованием оперативной памяти
     */
    public static $memory;

	protected function __construct()
    {
		if (self::ifCacheEnabled()) {
			self::$memory = $this->memcacheConnect();
		}
    }

	/**
     * Проверка работы кэша
     *
     * @return bool
     */
	protected function ifCacheEnabled()
	{	
		return (bool)\Hex\Base\Application::getParam('use_cache');
	}

	/**
     * Подключение серверу кэширования
     *
     * @return Memcache
     */
	protected function memcacheConnect()
	{	
		$cache = new \Memcache();

		if ($cache->connect('127.0.0.1', 11211) == false) {
			throw new Excaption('Could not connect to Memcached host');
		}
		
		$cacheDriver = new \Doctrine\Common\Cache\MemcacheCache();
		$cacheDriver->setMemcache($cache);

		return $cacheDriver;
	}
}