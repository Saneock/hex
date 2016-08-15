<?php
namespace Hex\Kernel;

/**
 * Основной класс служащий инициатором для отображения сайта 
 *
 * Class Application
 * @package Kernel
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
     * Инициация сайта
     *
     * @param array $siteParams Глобальные параметры сайта
     * @return void
     *
     * @todo Сделать Overrides
     */
    public static function init(array $siteParams)
    {
        // Установка глобальных параметров сайта
        $params = self::setSiteParams($siteParams);
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