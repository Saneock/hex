<?php
include "memory_test.php";
// =======================

include "init.php";

$params = array(
	"multilang" => true // Мультиязычный сайт
);

\Hex\Base\Application::init($params);



// ======================= Test

// Установить язык в русский
putenv('LC_ALL=ru_RU');
setlocale(LC_ALL, 'ru_RU');

bindtextdomain("ru_RU", "locale");

textdomain("ru_RU");

function _l($text, $context = false)
{
	if($context)
		$contextString = "{$context}\004{$text}";
	else
		$contextString = $text;

	$translation = _($contextString);

	if ($translation == $contextString)
		return $text;

	return $translation;
}