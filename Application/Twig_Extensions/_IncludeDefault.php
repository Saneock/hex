<?php
namespace Hex\Twig_Extensions;

/**
 * Подключает шаблон
 *
 * Если файл не найден в выбранной папке, файл будет подключен из папки шаблонов по умолчанию 
 *
 * @package Twig_Extensons
 */
class IncludeDefault extends \Twig_Extension
{
    public function __construct()
    {
        
    }

    public function getName()
    {
        return 'IncludeDefault';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('include_default', array($this, 'include_default')),
        );
    }

    public static function include_default($path)
    {
        list($group, $template) = explode('/', $path);
        return \Hex\Base\Application::$view->getEngine()->loadTemplate('_default/'.$template)->display(array());

       // return \Hex\Base\Application::$view->display($template);
    }
}