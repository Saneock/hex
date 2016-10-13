<?php
namespace Hex\Base;

use Hex\Base\Application as Hex;

/**
 * Маршрутизации данных
 *
 * Определяет какой раздел показать, какой контроллер и метод вызвать.
 * Работает с данными в ссылке.
 *
 * Class Router
 * @package Base
 */
class RouterCore extends \Abstracts\Singleton
{
    protected static $instance;
	
	/**
     * Текущий адрес страницы, разбитый на массив
	 *
	 * Содержит данные из ссылки после названия домена
     *
     * @var array
     */
    public static $url;

	/**
     * Список доступных разделов
	 *
	 * Заполняется в конструкторе класса, функцией getSectionsInfo(); 
     *
     * @var array
     */
    private $sections;

	/**
     * Тип запроса
	 *
	 * Типы: html, html-block, ajax, json, xml
	 *
	 * html - Показ всей страницы
	 * html-block - Показ отдельного блока, вызываемого с помощью передачи названия контроллера и метода
	 * ajax - Приложение возвращает данные в формате JSON. HTML контент присутствует
	 * json - Приложение возвращает данные в формате JSON. HTML контент отсутствует
	 * xml - Приложение возвращает данные в формате XML
     *
     * @var string
     */
    public static $type = 'html';

	/**
     * Главный роутер сайта
     *
     * @var array
     */
    private static $router;



	/**
     * @return \Hex\Base\Router
     */
	protected function __construct()
    {
		// Получение данных из ссылки
		self::$url = self::parseUrl();

		// Запись данных о раздлах в переменную
		if(!is_array($this->sections))
			$this->sections = self::getSectionsInfo();

		// Определение текущего раздела
		Hex::$section = $this->getCurrentSection();

		// Определение типа запроса
		self::$type = $this->getRequestType();

		// Определение класса роутинга
		self::$router = $this->getRouterClass();

		// Определение маршрутов приложения
		$this->defineAppRoutes(self::$router);

		// Определение пользовательских маршрутов
		$this->defineRoutes(self::$router);
    }

	/**
     * Получение данных из ссылки
     *
     * @return array
     */
    public static function parseUrl() : array
    {
		$url = explode('/', trim(urldecode(URI), ' /'));
		
		return $url;
	}

	/**
     * Определение всех возможных разделов
     *
     * @return array
     */
	public static function getSectionsInfo()
	{
		return array(
			'face' => array(
				'title' => _l('Лицевая часть'),
				'name' => 'face',
				'path' => 'frontend'
			),
			'admin' => array(
				'title' => _l('Админ панель'),
				'name' => 'admin',
				'path' => 'admin'
			)	
		);
	}

	/**
     * Определение текущего раздела
     *
     * @return array Возвращает данные о разделе
     */
    public function getCurrentSection() : array
    {
		$section = current(self::$url);

		if (isset($this->sections[$section])) {
			// Unset section from route URI
			array_shift(self::$url);

			// Override REQUEST_URI variable with new path
			$_SERVER['REQUEST_URI'] = '/'.implode('/', self::$url);

			// Return selected section
			return $this->sections[$section];
		}

		return current($this->sections);
	}

	/**
     * Получение списка разделов
     *
     * @return array
     */
    public function getSectionsList() : array
    {
		if(!is_array($this->sections))
			$this->sections = self::getSectionsInfo();

		return $this->sections;
	}

	/**
     * Определение типа запроса
     *
     * @return string
	 *
	 * @todo Написать метод определения типа запроса
     */
    public function getRequestType() : string
    {
		if (
			isset($_SERVER['HTTP_X_REQUESTED_WITH']) and
			!empty($_SERVER['HTTP_X_REQUESTED_WITH']) and
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
		) {
			$type = 'ajax'; 
		//} else if () {	

		} else {
			$type = self::$type;
		}
		
		return $type;
	}

	/**
     * Получение класса роутинга
     */
    private static function getRouterClass()
    {
		return new \Bramus\Router\Router();
	}

	/**
     * Запуск роутера
	 *
     * @return void
     */
    public static function runRouter()
    {
		self::$router->run();
	}

	/**
     * Определение маршрутов приложения
	 *
     * @return void
	 * @todo Написать действие выполнения метода
     */
    private static function defineAppRoutes($router)
    {
		switch (self::$type) {
			case 'ajax':
				$this->routeAjax($router);
				break;
		}
	}

	/**
     * Определение пользовательских маршрутов
	 *
     * @return void
     */
    private static function defineRoutes($router)
    {
		$router->get('/([a-z0-9_-]+)', function($page) {
			
		});
	}

	/**
     * Роутинг AJAX запроса
     */
    private function routeAjax($router)
    {
		$router->get('/(\w+)?(/\w*)?(/\w*)?.*', function($lang, $controller, $method) {
			// Check URI correctness
			if ($lang === null)
				$missingArgument = 'lang';
			else if ($controller === null)
				$missingArgument = 'controller';
			else if ($method === null)
				$missingArgument = 'method';

			if (isset($missingArgument))
				throw new \Exception\Router\MissingArgument($missingArgument, 'section*/ajax/lang/controller/method', 'GET');

			// Do actions
		});
	}

	
}