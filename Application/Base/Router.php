<?php
namespace Hex\Base;

/**
 * Маршрутизации данных
 *
 * Определяет какой раздел показать, какой контроллер и метод вызвать.
 * Работает с данными в ссылке.
 *
 * Class Router
 * @package Base
 */
class Router extends \Abstracts\Singleton
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
		\Hex\Base\Application::$section = $this->getCurrentSection();
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
				'name' => 'face'
			),
			'admin' => array(
				'title' => _l('Админ панель'),
				'name' => 'admin'
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
		$section = array_shift(self::$url);

		if(isset($this->sections[$section]))
			return $this->sections[$section];

		$key = key($this->sections);
		reset($this->sections);

		return $this->sections[$key];
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
}