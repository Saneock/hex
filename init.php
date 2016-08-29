<?php
require('config/config.php');
require('config/database.php');

// Composer Autoloader
require(DIR_VENDOR.'/autoload.php');

// Base
require(DIR_BASE.'/Autoloader.php');

// Instantiate the loader
\Hex\Base\Autoloader::init();