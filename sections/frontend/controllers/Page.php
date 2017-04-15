<?php
namespace web\frontend\controllers;

use Hex;
use Hex\Base\Controller;

class Page extends Controller
{
	public $layout = 'default';

	public function actionIndex()
	{
		return $this->render('page/index', ['page' => 'index2']);
	}

	public function actionItems($pageName = null)
	{
		//\PC::debug([$pageName]);
	}
}