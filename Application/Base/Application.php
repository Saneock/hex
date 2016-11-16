<?php
namespace Hex\Base;

/**
 * Основной класс служащий инициатором для отображения сайта 
 *
 * Class ApplicationCore
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
     * Кодировка
     *
     * @var string
     */
    public static $charset = ENCODING;

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
     * Объект шаблонизатора
     *
     * @var bool|object
     */
    public static $view;

    /**
     * @var Controller
     */
    public static $requestedController;

    /**
     * @var Action
     */
    public static $requestedAction;

    /**
     * @var array
     */
    public static $requestedParams;

    /**
     * @var Request
     */
    public static $request;

    /**
     * @var Response
     */
    public static $response;
    


    /**
     * Инициализация сайта
     *
     * @param array $siteParams Глобальные параметры сайта
     * @return void
     */
    public static function init()
    {
        // Установка глобальных параметров сайта
        self::setSiteParams();

        // Определение запроса
        self::$request = new Request();

        // Определение ответа
        self::$response = new Response();

        // Определение раздела и подготовка данных ссылки
        Application::$router = Router::getInstance();

        // Обработка запроса
        list($route, $params) = self::$request->resolve();

        // Инициализация компонентов
        self::initComponents();
                
        // Инициализация ответа
        self::$response->init();

        // Маршрутизация приложения
        self::$response = self::route($route, $params);
        
        // Отправка данных пользователю
        self::$response->send();
    }

    /**
     * Установка глобальных параметров сайта
     *
     * @param array $params Глобальные параметры сайта
     * @return void
     */
    protected static function setSiteParams()
    {
		self::$params = require(DIR_CONFIG.'/params.php');

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

    /**
     * Configures an object with the initial property values.
     *
     * @param object $object the object to be configured
     * @param array $properties the property initial values given in terms of name-value pairs.
     * @return object the object itself
     */
    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }
        return $object;
    }
    
    /**
     * Инициализация компонентов
     *
     * @return void
     */
    protected static function initComponents()
    {
		// Подключение к базе данных
        self::$db = Database::getInstance();

        // Инициализация кэша
        Cache::getInstance();

        // Определение языка
        //self::$params = \Hex\Base\Language::getCurrentLanguage();
    }

    /**
     * Возвращает контроллер по его идентификатору
     */
    public static function createController($controller, $config = array())
    {
        $controllerName = Controller::getFullClassName($controller);
        return new $controllerName($controller);
    }

    /**
     * Задаёт маршрут приложению
     */
    public static function route($route, $params = array())
    {
        list($controller, $action) = self::$router->parseRoute($route);

        $controllerObject = self::createController($controller);

        $result = $controllerObject->runAction($action, $params);

        if ($result instanceof Response)
            return $result;
        else
            Application::$response->content = $result;

        return Application::$response;
    }
}