<?php
namespace Hex\Base;

use Hex\Base\Object;
use Hex\Base\Action;
use Hex\Base\Application;
use Hex\Base\View;

/**
 * Основной класс контроллера
 *
 * Class ControllerCore
 * @package Base
 */
class ControllerCore extends Object
{
    const EVENT_BEFORE_ACTION = 'beforeAction';

    const EVENT_AFTER_ACTION = 'afterAction';

    public $name;

    public $action;

    public $defaultAction = 'index';

    public $layout;

    public $enableCsrfValidation = false;

    public $actionParams = [];


    public function __construct($name, $config = [])
    {
        $this->name = $name;

		parent::__construct($config);
	}

    public function HasAction($fullActionName)
	{
		try {
			$action = new \ReflectionMethod($this->getFullClassName(), $fullActionName);
			return true;
		} catch (Exception $ex) {
			return false;
		}
	}

    public static function getFullClassName($name)
	{
		return '\web\frontend\controllers\\'.self::getClassName($name);
	}

    public static function getClassName($name)
	{
		return self::normalizeEntityName($name);
	}

    public static function getActionName($name)
	{
		return 'action' . self::normalizeEntityName($name);
	}

    public static function normalizeEntityName($name)
    {
        return str_replace(' ', '', ucwords(implode(' ', explode('-', $name))));
    }

    public function actions()
    {
        return [];
    }

    /**
     * Returns the unique ID of the controller.
     * @return string the controller ID that is prefixed with the module ID (if any).
     */
    public function getUniqueId()
    {
        return $this->module instanceof Application ? $this->name : $this->module->getUniqueId() . '/' . $this->name;
    }

    /**
     * Returns the route of the current request.
     * @return string the route (module ID, controller ID and action ID) of the current request.
     */
    public function getRoute()
    {
        return $this->action !== null ? $this->action->getUniqueId() : $this->getUniqueId();
    }

    public function createAction($action)
    {
        if ($action === '') {
            $action = $this->defaultAction;
        }
        
        $actionMap = $this->actions();

        if (isset($actionMap[$action])) {
            return new $actionMap[$action];
        } elseif (preg_match('/^[a-z0-9\\-_]+$/', $action) && strpos($action, '--') === false && trim($action, '-') === $action) {
            $methodName = self::getActionName($action);
            if (method_exists($this, $methodName)) {
                $method = new \ReflectionMethod($this, $methodName);
                if ($method->isPublic() && $method->getName() === $methodName) {
                    return new Action($action, $this);
                } else {
                    throw new \Exceptions\TypeException("Method shold be public [action: " . $action . ", controller: " . $this->name . "]");
                }
            } else {
                throw new \Exception\UnknownMethod("Action not found [action: " . $action . ", controller: " . $this->name . "]");
            }
        }

        return null;
    }

    /**
     * Runs an action within this controller with the specified action ID and parameters.
     * If the action ID is empty, the method will use [[defaultAction]].
     * @param string $id the ID of the action to be executed.
     * @param array $params the parameters (name-value pairs) to be passed to the action.
     * @return mixed the result of the action.
     * @throws InvalidRouteException if the requested action ID cannot be resolved into an action successfully.
     * @see createAction()
     */
    public function runAction($id, $params = [])
    {
        $action = $this->createAction($id);
        if ($action === null) {
            throw new \Exception\InvalidRoute('Unable to resolve the request: ' . $this->getUniqueId() . '/' . $id);
        }

        //Yii::trace('Route to run: ' . $action->getUniqueId(), __METHOD__);

        if (Application::$requestedAction === null) {
            Application::$requestedAction = $action;
        }

        $oldAction = $this->action;
        $this->action = $action;

        $modules = [];
        $runAction = true;
        
        /* // call beforeAction on modules
        foreach ($this->getModules() as $module) {
            if ($module->beforeAction($action)) {
                array_unshift($modules, $module);
            } else {
                $runAction = false;
                break;
            }
        }*/

        $result = null;

        if ($runAction && $this->beforeAction($action)) {
            // Run the action
            $result = $action->runWithParams($params);

            $result = $this->afterAction($action, $result);

            /* // call afterAction on modules
            foreach ($modules as $module) {
                // @var $module Module
                $result = $module->afterAction($action, $result);
            }*/
        }

        $this->action = $oldAction;

        return $result;
    }

    /**
     * Binds the parameters to the action.
     * This method is invoked by [[\yii\base\Action]] when it begins to run with the given parameters.
     * This method will check the parameter names that the action requires and return
     * the provided parameters according to the requirement. If there is any missing parameter,
     * an exception will be thrown.
     * @param \yii\base\Action $action the action to be bound with parameters
     * @param array $params the parameters to be bound to the action
     * @return array the valid parameters that the action can run with.
     * @throws BadRequestHttpException if there are missing or invalid parameters.
     */
    public function bindActionParams($action, $params)
    {
        if (!($action instanceof Action)) {
            throw new \Exceptions\Data\TypeException('Parameter $action should be the Action object. ' . get_class($this) . '::bindActionParams($action, $params)');
        }

        $method = new \ReflectionMethod($this, $action->actionMethod);

        $args = [];
        $missing = [];
        $actionParams = [];
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $params)) {
                if ($param->isArray()) {
                    $args[] = $actionParams[$name] = (array) $params[$name];
                } elseif (!is_array($params[$name])) {
                    $args[] = $actionParams[$name] = $params[$name];
                } else {
                    throw new \Exceptions\Http\Client\BadRequestException('Invalid data received for parameter "'.$name.'"');
                }
                unset($params[$name]);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $actionParams[$name] = $param->getDefaultValue();
            } else {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            throw new \Exceptions\Http\Client\BadRequestException('Missing required parameters: '.implode(', ', $missing));
        }

        $this->actionParams = $actionParams;

        return $args;
    }

    public function beforeAction($action)
    {
        //if ($this->enableCsrfValidation && Yii::$app->getErrorHandler()->exception === null && !Yii::$app->getRequest()->validateCsrfToken()) {
        if ($this->enableCsrfValidation/* && !Application::$request->validateCsrfToken()*/) {
            throw new \Exceptions\Http\Client\BadRequestException(__('Unable to verify your data submission.'));
        }
        
        return true;
    }

    public function afterAction($action, $result)
    {
        return $result;
    }

    public function render($view, $params = [])
    {
        $content = View::render($view, $params);
        return $this->renderContent($content);
    }

    /**
     * Renders a static string by applying a layout.
     * @param string $content the static string being rendered
     * @return string the rendering result of the layout with the given static string as the `$content` variable.
     * If the layout is disabled, the string will be returned back.
     * @since 2.0.1
     */
    public function renderContent($content)
    {
        $layoutFile = $this->findLayoutFile($this->getView());
        if ($layoutFile !== false) {
            return View::render($layoutFile, ['content' => $content]);
        } else {
            return $content;
        }
    }

    /**
     * Renders a view without applying layout.
     * This method differs from [[render()]] in that it does not apply any layout.
     * @param string $view the view name. Please refer to [[render()]] on how to specify a view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * @return string the rendering result.
     * @throws InvalidParamException if the view file does not exist.
     */
    public function renderPartial($view, $params = [])
    {
        return View::render($view, $params, $this);
    }
}