<?php
require('config/environment.php');
require('config/config.php');
require('config/database.php');

// Composer Autoloader
require(DIR_VENDOR.'/autoload.php');

require(DIR_VENDOR.'/yiisoft/yii2/Yii.php');

// Base
require(DIR_BASE.'/Autoloader.php');

// Instantiate the loader
\Hex\Base\Autoloader::getInstance();