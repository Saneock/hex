<?php
require('config/config.php');

// Composer Autoloader
require(DIR_VENDOR.'/autoload.php');

// Kernel
require('Kernel/Autoloader.php');

// Instantiate the loader
\Hex\Kernel\Autoloader::init();