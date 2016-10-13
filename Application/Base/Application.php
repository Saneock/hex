<?php
namespace Hex\Base;

use Hex\Base\Router;
use Hex\Base\Cache;
use Hex\Base\Database;

/**
 * Основной класс служащий инициатором для отображения сайта 
 *
 * Class Application
 * @package Base
 */
class ApplicationCore
{
    /**
     * Массив с глобальными параметрами сайта
     *
     * @var array
     */
    protected static $params;

    /**
     * Главный роутер сайта
     *
     * @var array
     */
    public static $router;

    /**
     * Данные текущего раздела
     *
     * @var array
     */
    public static $section;

    /**
     * Массив с данными выбранного языка
     *
     * @var array
     */
    public static $language;

    /**
     * Объект базы данных
     *
     * @var Database
     */
    public static $db;

    /**
     * Объект кэша
     *
     * @var Cache
     */
    public static $cache;
    


    /**
     * Инициализация сайта
     *
     * @param array $siteParams Глобальные параметры сайта
     * @return void
     *
     * @todo Сделать Overrides
     */
    public static function init(array $siteParams)
    {
        // Установка глобальных параметров сайта
        self::setSiteParams($siteParams);

        // Определение раздела и подготовка данных ссылки
        self::$router = Router::getInstance();

        // Подключение к базе данных
        self::$db = Database::getInstance();

        // Инициализация кэша
        Cache::getInstance();
        
        // Определение языка
        //self::$params = \Hex\Base\Language::getCurrentLanguage();

        // Запуск роутера
		self::$router->runRouter();
    }

    /**
     * Установка глобальных параметров сайта
     *
     * @param array $params Глобальные параметры сайта
     * @return void
     */
    protected static function setSiteParams(array $params)
    {
        $defaultParams = array(
			"multilang" => true,
			"use_cache" => true
        );
		
		self::$params = array_merge($defaultParams, $params);

        self::setIniParams();
    }

    /**
     * Получение параметра сайта
     *
     * @param string $param
     * @return mixed
     */
    public static function getParam(string $param)
    {
		return (isset(self::$params[$param])) ? self::$params[$param] : false;
    }

    /**
     * Установка параметров php.ini
     *
     * @return void
     */
    public static function setIniParams()
    {
		// Установка кодировки mb_ функций
        if (extension_loaded("mb_string")) {
            mb_internal_encoding(ENCODING);
        }
        
		// Установка часового пояса
		date_default_timezone_set(TIMEZONE);
    }
}