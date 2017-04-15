<?php

require('directories.php');

define('ENCODING', 'UTF-8'); // Кодировка сайта
define('TIMEZONE', 'Europe/Chisinau'); // Часовой пояс

$config = [
	'components' => [
		'request' => [
			'cookieValidationKey' => 'DSFgksdifhiw899734hekfDFGisjdfi9374',
		],
	]
];