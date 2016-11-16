<?php
namespace web\frontend\controllers;

use Hex\Base\Controller;
use Hex\Base as Hex;

class Page extends Controller
{
	public function actionIndex()
	{
		return $this->renderPartial('page/index');
	}
}