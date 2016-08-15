<?php
require('config.php');

// Composer Autoloader
require(DIR_VENDOR.'/autoload.php');

// Kernel
require('Autoloader.php');

// Instantiate the loader
\Hex\Kernel\Autoloader::init();