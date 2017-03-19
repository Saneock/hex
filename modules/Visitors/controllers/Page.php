<?php
namespace web\frontend\controllers;

use Hex\Base\Controller;
use Hex\Base as Hex;

class Page extends Controller
{
	public $layout = 'default';

	public function actionIndex()
	{
		return $this->render('page/index');
	}
}