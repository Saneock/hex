<?php
namespace Hex\Base;

use Hex\Base\Application as Hex;

/**
 * Объект шаблона
 *
 * Class View
 * @package Base
 */
class ViewCore
{
	/**
     * @return \Hex\Base\ViewCore
     */
	protected function __construct()
    {
		$loader = new Twig_Loader_Filesystem(DIR_);
        
        $twig = new Twig_Environment($loader, array(
            'cache' => '/path/to/compilation_cache',
        ));
    }	
}