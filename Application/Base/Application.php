<?php
namespace Hex\Base;

use Hex;
use Hex\Base\Module;
use Hex\Base\Response;
use \Exception\InvalidConfig as InvalidConfigException;
use \Exception\InvalidRoute;

/**
 * Основной класс служащий инициатором для отображения сайта 
 *
 * Class ApplicationCore
 * @package Base
 */
class ApplicationCore extends Module
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
     * Данные текущего раздела
     *
     * @var array
     */
    protected static $section;

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
     * Constructor.
     * @param array $config name-value pairs that will be used to initialize the object properties.
     * Note that the configuration must contain both [[id]] and [[basePath]].
     * @throws InvalidConfigException if either [[id]] or [[basePath]] configuration is missing.
     */
    public function __construct($config = [])
    {
        $this->preInit($config);

        $this->registerErrorHandler($config);

        Object::__construct($config);
    }

    /**
     * Pre-initializes the application.
     * This method is called at the beginning of the application constructor.
     * It initializes several important application properties.
     * If you override this method, please make sure you call the parent implementation.
     * @param array $config the application configuration
     */
    public function preInit(&$config)
    {
        // Установка глобальных параметров сайта
        self::setSiteParams();

        // Merge core components with custom components
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($config['components'][$id])) {
                $config['components'][$id] = $component;
            } elseif (is_array($config['components'][$id]) && !isset($config['components'][$id]['class'])) {
                $config['components'][$id]['class'] = $component['class'];
            }
        } 
    }

    /**
     * Returns the configuration of core application components.
     * @see set()
     */
    public function coreComponents()
    {
        return [
            'request' => ['class' => 'Hex\Base\Request'],
            'response' => ['class' => 'Hex\Base\Response'],
            'router' => ['class' => 'Hex\Base\Router'],
            'session' => ['class' => 'yii\web\Session'],
            'errorHandler' => ['class' => 'yii\web\ErrorHandler'],
            'log' => ['class' => 'yii\log\Dispatcher'],
            'view' => ['class' => 'Hex\Base\View'],
            //'formatter' => ['class' => 'yii\i18n\Formatter'],
            //'i18n' => ['class' => 'yii\i18n\I18N'],
            'mailer' => ['class' => 'yii\swiftmailer\Mailer'],
            //'urlManager' => ['class' => 'yii\web\UrlManager'],
            //'assetManager' => ['class' => 'yii\web\AssetManager'],
            'security' => ['class' => 'yii\base\Security'],
        ];
    }

    /**
     * Инициализация сайта
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Запуск приложения
     *
     * @return void
     */
    public function run()
    {
        // Обработка запроса
        list($route, $params) = $this->getRequest()->resolve();
        
        // Инициализация ответа
        $this->getResponse()->init();

        // Маршрутизация приложения
        $this->set('response', self::route($route, $params));
      
        // Отправка данных пользователю
        $this->getResponse()->send();
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
     * Returns current \Hex\Base\Section object
     */
    public function getSection()
    {
        return $this->section;
    }
    
    /**
     * Sets current \Hex\Base\Section object
     */
    public function setSection(Section $section)
    {
        $this->section = $section;
    }


    /**
     * Returns the database connection component.
     */
    public function getDb()
    {
        return $this->get('db');
    }

    /**
     * Returns the log dispatcher component.
     */
    public function getLog()
    {
        return $this->get('log');
    }

    /**
     * Returns the error handler component.
     */
    public function getErrorHandler()
    {
        return $this->get('errorHandler');
    }

    /**
     * Returns the cache component.
     * Null if the component is not enabled.
     */
    public function getCache()
    {
        return $this->get('cache', false);
    }

    /**
     * Returns the formatter component.
     */
    public function getFormatter()
    {
        return $this->get('formatter');
    }

    /**
     * Returns the request component.
     */
    public function getRequest()
    {
        return $this->get('request');
    }

    /**
     * Returns the router component.
     */
    public function getRouter()
    {
        return $this->get('router');
    }

    /**
     * Returns the response component.
     */
    public function getResponse()
    {
        return $this->get('response');
    }

    /**
     * Returns the view template object.
     * Null is returned if is not configured.
     */
    public function getView()
    {
        return $this->get('view', true);
    }

    /**
     * Returns the internationalization component
     */
    public function getTranslator()
    {
        return $this->get('translator');
    }

    /**
     * Returns the mailer component.
     */
    public function getMailer()
    {
        return $this->get('mailer');
    }

    /**
     * Returns the auth manager for this application.
     * Null is returned if auth manager is not configured.
     */
    public function getAuthManager()
    {
        return $this->get('authManager', false);
    }

    /**
     * Returns the asset manager.
     */
    public function getAssetManager()
    {
        return $this->get('assetManager');
    }

    /**
     * Returns the security component.
     */
    public function getSecurity()
    {
        return $this->get('security');
    }



    /**
     * Registers the errorHandler component as a PHP error handler.
     * @param array $config application config
     */
    protected function registerErrorHandler(&$config)
    {
        if (ENABLE_ERROR_HANDLER) {
            if (!isset($config['components']['errorHandler']['class'])) {
                echo "Error: no errorHandler component is configured.\n";
                exit(1);
            }
            $this->set('errorHandler', $config['components']['errorHandler']);
            unset($config['components']['errorHandler']);
            $this->getErrorHandler()->register();
        }
    }

    /**
     * Возвращает контроллер по его идентификатору
     */
    public function createController($controller, $config = array())
    {
        $controllerName = Controller::getFullClassName($controller);

        if (!class_exists($controllerName))
            throw new InvalidRoute('Controller '.$controllerName.' is not found');
            
        return new $controllerName($controller);
    }

    /**
     * Возвращает результат работы блока MVC
     */
    public function getMVCResult($route, $params = array(), $useLayout = false)
    {
        list($controller, $action, $module) = Hex::$app->getRouter()->parseRoute($route);

        if ($module === false) {
            $controllerObject = $this->createController($controller);
        } else {
            $module = new Module($module);
            $controllerObject = $module->createController($controller);
        }

        $result = $controllerObject->runAction($action, $params);

        if ($result instanceof Response) {
            return $result;
        }
        
        if ($result === null)
            $result = array();

        if (is_array($result)) {
            Hex::$app->getResponse()->data = $result;

            if ($useLayout)
                $result = $controllerObject->render($controller.'/'.$action, $result);
            else
                $result = $controllerObject->renderPartial($controller.'/'.$action, $result);
        }

        return $result;
    }

    /**
     * Задаёт маршрут приложению
     */
    public function route($route, $params = array())
    {
        $result = $this->getMVCResult($route, $params, true);

        if (in_array(Hex::$app->getResponse()->format, [Response::FORMAT_RAW, Response::FORMAT_HTML])) {
            ob_start(); 
            echo $result;
            Hex::$app->getResponse()->data = ob_get_clean();
        } elseif (Hex::$app->getRequest()->getIsAjax()) {
            ob_start(); 
            echo $result;
            Hex::$app->getResponse()->data['_content'] = ob_get_clean();
        }
    
        return Hex::$app->getResponse();
    }
    

    /**
     * Выводит блок MVC
     */
    public function execute($route, $params = array())
    {
        $result = $this->getMVCResult($route, $params, false);

        if (in_array(Hex::$app->getResponse()->format, [Response::FORMAT_RAW, Response::FORMAT_HTML])) {
            echo $result;
        } elseif (Hex::$app->getRequest()->getIsAjax()) {
            echo $result;
        }
    }

    
    /**
     * Returns path to layouts directory 
     */
    public static function getLayoutsPath()
    {
        return 'layouts';
    }
}