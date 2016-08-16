<?php
include "memory_test.php";
// =======================

include "init.php";

$params = array(
	"multilang" => true // Мультиязычный сайт
);

\Hex\Kernel\Application::init($params);


// ======================= Test

exit();
// Установить язык в русский
putenv('LC_ALL=ru_RU');
setlocale(LC_ALL, 'ru_RU');

bindtextdomain("ru_RU", "locale");

textdomain("ru_RU");

function _l($text, $context = 'frontend')
{
	$contextString = "{$context}\004{$text}";
	$translation = _($contextString);

	if ($translation == $contextString)
		return $msgid;

	return $translation;
}