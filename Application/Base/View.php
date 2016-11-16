<?php
namespace Hex\Base;

use Hex\Base as Hex;

/**
 * Объект шаблона
 *
 * Class ViewCore
 * @package Base
 */
class ViewCore extends \Abstracts\Singleton
{
    protected static $instance;

    /**
     * Объект шаблонизатора
	 *
	 * Через него осуществляются все действия с шаблонами
     */
    private static $engine;

    /**
     * Путь к директории кэша шаблонов
     */
    public static $cacheDirectory = DIR_SECTION_CACHE.'/templates';

    /**
     * Путь к собственным расширениям шаблонизатора
     */
    public static $extensionsPath = DIR_APP.'/Twig_Extensions';

    /**
     * Имя файла кэша расширений
     */
    public static $cacheFileName = 'twig_extensions.php';

    /**
     * Расширение файлов шаблонов по умолчанию
     */
    public static $templateExtension = 'twig';

	/**
     * Настройка шаблонизатора
     *
     * @return \Hex\Base\ViewCore
     */
	protected function __construct()
    {
        // Если тип запроса JSON, то возвращаем false
        if (in_array(Application::$response->format, [Application::$response::FORMAT_JSON, Application::$response::FORMAT_JSONP, Application::$response::FORMAT_XML]))
            return false;

		$loader = new \Twig_Loader_Filesystem(DIR_SECTION_TEMPLATES);

        self::$engine = new \Twig_Environment($loader, array(
            'cache' => self::$cacheDirectory,
            'auto_reload' => (ENVIRONMENT == 'development')
        ));

        $this->addExtensions();
    }

    /**
     * Подключение собственных расширений
     */
	protected function addExtensions()
    {
        // Проверка кэша расширений
        if($this->isCachedExtensions())
            return $this->setExtensionsFromCache();

        if(!is_dir(self::$extensionsPath))
            throw new \Exceptions\IO\Filesystem\DirectoryNotFoundException(self::$extensionsPath);

        $extensioins = array_diff(scandir(self::$extensionsPath), array('..', '.'));

        $cache = array();

        foreach ($extensioins as $filename) {
            // Пропустить расширение, которое начинается со знака _
            if(strpos($filename, '_') === 0)
                continue;

            $name = substr($filename, 0, strrpos($filename, "."));
            $this->addExtension($name);
            
            $cache[] = $name;
        }

        $this->saveExtensionsToCache($cache);
    }

    /**
     * Подключение расширения
     */
	protected function addExtension($name)
    { 
        $classname = '\Hex\Twig_Extensions\\'.$name;
        self::$engine->addExtension(new $classname);
    }

    /**
     * Проверяет, актуален ли кэш расширений
     *
     * @return bool
     */
	protected function isCachedExtensions()
    { 
        $file = DIR_CACHE.'/'.self::$cacheFileName;

        if (is_readable($file) and ENVIRONMENT !== 'development')
            return true;
        else
            return false;
    }

    /**
     * Устанавливает расширения из кэша
     */
	protected function setExtensionsFromCache()
    {
        include(DIR_CACHE.'/'.self::$cacheFileName);
    }

    /**
     * Записывает расширения в кэш
     */
	protected function saveExtensionsToCache($extensioins)
    {
        $file = DIR_CACHE.'/'.self::$cacheFileName;

        $content = "<?php\n";

        foreach ($extensioins as $name) {
            $content .= "\n".'self::$engine->addExtension(new \Hex\Twig_Extensions\\'.$name.');';
        }

        file_put_contents($file, $content);
    }

    /**
     * Получшение объекта шаблонизатора
     *
     * @return Twig_Environment
     */
	public function getEngine()
    {
		return self::$engine;
    }
    
    /**
     * Получение шаблона
     *
     * @return string
     */
	public static function render($template, $data = array(), $appendExtension = true)
    {
        if(!self::$engine)
            return null;

        if ($appendExtension)
            $template .= '.'.self::$templateExtension;

		return self::$engine->render($template, $data);
    }

    /**
     * Отображение шаблона
     *
     * @return string
     */
	public static function display($template, $data = array(), $appendExtension = true)
    {
		echo self::render($template, $data, $appendExtension);
    }	
}