<?php
namespace Hex\Base;

/**
 * Основной класс служащий инициатором для отображения сайта 
 *
 * Class Application
 * @package Base
 */
class Application
{
    /**
     * Массив с глобальными параметрами сайта
     *
     * @var array
     */
    public static $params;

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
        self::$params = self::setSiteParams($siteParams);

        // Определение раздела и подготовка данных ссылки
        self::$router = \Hex\Base\Router::getInstance();

        // Подключение к базе данных
        $db = \Hex\Base\Database::getInstance();
        
        // Определение языка
        //self::$params = \Hex\Base\Language::getCurrentLanguage();
    }

    /**
     * Получение и установка глобальных параметров сайта
     *
     * @param array $siteParams Глобальные параметры сайта
     * @return array
     */
    public static function setSiteParams(array $siteParams) : array
    {
        $params = array(
			"multilang" => true
        );
		
		$params = self::$params = array_merge($params, $siteParams);

        return $params;
    }
}