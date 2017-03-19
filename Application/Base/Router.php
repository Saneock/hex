<?php
namespace Hex\Base;

use Hex;
use Hex\Base\Application;
use Hex\Base\View;
use Exception\InvalidRoute;

/**
 * Маршрутизации данных
 *
 * Определяет какой раздел показать, какой контроллер и метод вызвать.
 * Работает с данными в ссылке.
 *
 * Class RouterCore
 * @package Base
 */
class RouterCore
{	
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
     * Главный роутер сайта
     *
     * @var array
     */
    private static $router;

	/**
     * Путь
     *
     * @var string
     */
    private static $route;

	/**
     * Параметры запроса
     *
     * @var array
     */
    private static $queryParams = array();

	/**
     * Путь по умолчанию
     *
     * @var string
     */
    private static $defaultRoute = 'page/index';

	/**
     * Путь ошибки
     *
     * @var string
     */
    private static $errorRoute = 'page/error';

	/**
     * Тип запроса
     *
     * @var string
     */
    private $requestType = Response::FORMAT_HTML;



	/**
     * @return \Hex\Base\Router
     */
	public function __construct()
    {
		// Получение данных из ссылки
		self::$url = self::parseUrl(Hex::$app->getRequest());

		// Определение типа запроса
		$this->defineRequestType();

		// Запись данных о раздлах в переменную
		if(!is_array($this->sections))
			$this->sections = self::getSections();

		// Определение текущего раздела
		Hex::$app->setSection($this->getCurrentSection());

		// Определение директорий относительно раздела
		$this->defineSectionDirs();

		// Определение класса роутинга
		self::$router = $this->getRouterClass();

		// Установка страницы с ошибкой 404
		$this->define404(self::$router);

		// Определение маршрутов приложения
		$this->defineAppRoutes(self::$router);
    }

	/**
     * Получение данных из ссылки
     *
     * @param Request $request
     * @return array
     */
    public static function parseUrl(Request $request) : array
    {
		$url = explode('/', trim(urldecode($request->getPathInfo()), ' /'));

		return $url;
	}

	/**
     * Обработка запроса
     *
     * @param Request $request
     * @return array Первый элемент - путь, второй - параметры запроса.
     */
    public function parseRequest(Request $request) : array
    {    
		if ($request->getPathInfo() == '') { 
			list(self::$route, self::$queryParams) = [self::$defaultRoute, []];
		} else {
			$this->defineRoute(self::$router);
			$this->runRouter(); 
		}

		return [self::$route, self::$queryParams];
	}

	/**
     * Определение типа запроса
	 *
     * @return void
     */
    private function defineRequestType()
    {
		$type = array_shift(self::$url);

		if (strpos($type, '-') === 0) {
			$type = substr($type, 1);
			switch ($type) {
				case Response::FORMAT_RAW:
				case Response::FORMAT_HTML:
				case Response::FORMAT_JSON:
				//case Response::FORMAT_JSONP:
				case Response::FORMAT_XML:
					Hex::$app->getResponse()->format = $type;
				break;
				default:
					array_unshift(self::$url, '-'.$type);
				break;
			}
		} else {
			array_unshift(self::$url, $type);
		}

		$_SERVER['REQUEST_URI'] = '/'.implode('/', self::$url);
	}
	
	/**
     * Получение контроллера и действия из пути
	 *
     * @return array Первый элемент - контроллер, второй - действие.
     */
    public static function parseRoute($route)
    { 
		$elements = explode('/', trim($route, ' /'));

		if (count($elements) == 2) {
			$module = false;
			$controller = $elements[0];
			$action = $elements[1];
		} elseif (count($elements) == 3) {
			$module = $elements[0];
			$controller = $elements[1];
			$action = $elements[2];
		} else {
			throw new InvalidRoute("Route contain ".count($elements)." parts, instead of 2 or 3.");
		}

		return [$controller, $action, $module];
	}

	/**
     * Определение всех возможных разделов
     *
     * @return array
     */
	public static function getSections()
	{
		return array(
			'face' => new Section([
				'title' => __('Лицевая часть'),
				'name' => 'face',
				'path' => 'frontend'
			]),
			'admin' => new Section([
				'title' => __('Админ панель'),
				'name' => 'admin',
				'path' => 'admin'
			])	
		);
	}

	/**
     * Определение текущего раздела
     *
     * @return Section Возвращает данные о разделе
     */
    public function getCurrentSection() : Section
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
     * Определение директорий относительно раздела
     */
    protected function defineSectionDirs()
    {
		// Section directory
		define('DIR_SECTION', DIR_SECTIONS.'/'.Hex::$app->getSection()->path);

		// Cache
		define('DIR_SECTION_CACHE', DIR_SECTION.'/cache');

		// Locale
		define('DIR_SECTION_LOCALE', DIR_SECTION.'/locale');

		// Templates
		define('DIR_SECTION_TEMPLATES', DIR_SECTION.'/templates');

		// Controllers
		define('DIR_SECTION_CONTROLLERS', DIR_SECTION.'/controllers');
	}

	/**
     * Получение списка разделов
     *
     * @return array
     */
    public function getSectionsList() : array
    {
		if(!is_array($this->sections))
			$this->sections = self::getSections();

		return $this->sections;
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
     * @return Router
     */
    public static function runRouter()
    {
		return self::$router->run();
	}

	/**
     * Установка страницы с ошибкой 404
	 *
     * @return void
     */
    private static function define404($router)
    { 
		$router->set404(function() {
			Hex::$app->getResponse()->setStatusCode(404);
			Hex::$app->route(self::$errorRoute);
		});
	}

	/**
     * Определение маршрутов приложения
	 *
     * @return void
	 * @todo Написать действие выполнения метода
     */
    private static function defineAppRoutes($router)
    {
		if (Hex::$app->getRequest()->getIsAjax()) {
			$this->routeAjax($router);
			return;
		}
	}

	/**
     * Определение пользовательских маршрутов
	 *
     * @return void
     */
    private static function defineRoute($router)
    {
		$router->get('/([a-z0-9_-]+)', function($page) {
			self::$route = "page/".$page;
			self::$queryParams = ['pageName' => $page];
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
			echo 'sfsdf';
			self::$route = "page/".$page;
		});
	}

	
}