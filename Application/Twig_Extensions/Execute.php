<?php
namespace Hex\Twig_Extensions;

use Hex;

/**
 * Выполняет блок MVC
 *
 * @package Twig_Extensons
 */
class Execute extends \Twig_Extension
{
    public function __construct()
    {
        
    }

    public function getName()
    {
        return 'Execute';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('execute', array($this, 'execute')),
        );
    }

    public static function execute($path, $params = [])
    { 
        Hex::$app->execute($path, $params);
    }
}