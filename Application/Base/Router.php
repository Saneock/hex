<?php
namespace Hex\Base;

use Hex\Base as Hex;

/**
 * Маршрутизации данных
 *
 * Определяет какой раздел показать, какой контроллер и метод вызвать.
 * Работает с данными в ссылке.
 *
 * Class RouterCore
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
    private static $queryParams;

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
     * @return \Hex\Base\Router
     */
	protected function __construct()
    {
		// Получение данных из ссылки
		self::$url = self::parseUrl(Application::$request);

		// Запись данных о раздлах в переменную
		if(!is_array($this->sections))
			$this->sections = self::getSections();

		// Определение текущего раздела
		Hex\Application::$section = $this->getCurrentSection();

		// Определение директорий относительно раздела
		$this->defineSectionDirs();

		// Определение шаблонизатора
		Hex\Application::$view = \Hex\Base\View::GetInstance();

		// Определение класса роутинга
		self::$router = $this->getRouterClass();

		// Установка страницы с ошибкой 404
		$this->define404(self::$router);

		// Определение маршрутов приложения
		//$this->defineAppRoutes(self::$router);
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
		if ($request->pathInfo == '') {
			list(self::$route, self::$queryParams) = [self::$defaultRoute, []];
		} else {
			$this->defineRoute(self::$router);
			$this->runRouter(); 
		}

		return [self::$route, self::$queryParams];
	}
	
	/**
     * Получение контроллера и действия из пути
	 *
     * @return array Первый элемент - контроллер, второй - действие.
     */
    public static function parseRoute($route)
    { 
		return explode('/', trim($route, ' /'));
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
		define('DIR_SECTION', DIR_SECTIONS.'/'.Hex\Application::$section->path);

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
			header('HTTP/1.1 404 Not Found');
			list($controller, $action) = explode('/', self::$errorRoute);
			Hex\Application::route($controller, $action);
		});
	}

	/**
     * Определение маршрутов приложения
	 *
     * @return void
	 * @todo Написать действие выполнения метода
     */
    /*private static function defineAppRoutes($router)
    {
		switch (self::$type) {
			case 'ajax':
				$this->routeAjax($router);
			break;
		}
	}*/

	/**
     * Определение пользовательских маршрутов
	 *
     * @return void
     */
    private static function defineRoute($router)
    { 
		$router->get('/(\w+)', function($page) {
			self::$route = "page/index";
			self::$queryParams = [];
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