<?php
namespace Hex\Base;

use Hex;
use Hex\Base\Object;
use Hex\Base\Controller;
use Hex\Base\Application;

/**
 * Основной класс контроллера
 *
 * Class ActionCore
 * @package Base
 */
class ActionCore extends Object
{
    public $name;

    public $controller;

    public $actionMethod;

    /**
     * Constructor.
     *
     * @param string $id the ID of this action
     * @param Controller $controller the controller that owns this action
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($name, $controller, $config = [])
    {
        $this->name = $name;
        $this->controller = $controller;
        $this->actionMethod = Controller::getActionName($name);

        parent::__construct($config);
    }

    /**
     * Returns the unique ID of this action among the whole application.
     *
     * @return string the unique ID of this action among the whole application.
     */
    public function getUniqueId()
    {
        return $this->controller->getUniqueId() . '/' . $this->name;
    }

    /**
     * Runs this action with the specified parameters.
     * This method is mainly invoked by the controller.
     *
     * @param array $params the parameters to be bound to the action's run() method.
     * @return mixed the result of the action
     * @throws InvalidConfigException if the action class does not have a run() method
     */
    public function runWithParams($params)
    {
        $args = $this->controller->bindActionParams($this, $params);

        Hex::$app->getLogger()->info('Running action [' . $this->controller->name . '::' . $this->name . ', ' . Controller::getFullClassName($this->controller->name) . '::' . $this->actionMethod . '()] with params: '.json_encode($args));

        if (Application::$requestedParams === null) {
            Application::$requestedParams = $args;
        }

        return call_user_func_array([$this->controller, $this->actionMethod], $args);
    }
}