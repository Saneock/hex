<?php
// Directories
define('ROOT', $_SERVER['DOCUMENT_ROOT']); // Корневая директория
define('URI', $_SERVER['REQUEST_URI']); // Адрес в ссылке

define('DIR_VENDOR', ROOT.'/vendor'); // Путь к пакетам Composer
define('DIR_APP', ROOT.'/Application'); // Путь к папке приложения
define('DIR_BASE', DIR_APP.'/Base'); // Путь к главным классам приложения




define('ENCODING', 'UTF-8'); // Кодировка сайта
define('TIMEZONE', 'Europe/Chisinau'); // Часовой пояс