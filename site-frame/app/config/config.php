<?php
$Config = Array(
	'path' => Array(
		'root' => ROOT_PATH,
		'lib' => ROOT_PATH.'/../lib/php/include/',
		'app' => ROOT_PATH.'/../',
		'controllers' => ROOT_PATH.'/app/controllers/',
		'views' => ROOT_PATH.'/app/views/',
		'models' => ROOT_PATH.'/app/models/'
	),
	'ns' => Array(
		'controllers' => 'Juristinform\\app\\controllers\\'
	),
	'db' => Array(
		'server' => 'localhost',
		'db' => 'juristinform',
		'user' => 'root',
		'password' => 'tylerd'
	),
	'server' => Array(
		'baseurl' => ''
	),
	'debug' => Array(
		'loaded' => 'Config is loaded<br />'
	),
	'mail' => Array(
		//'type' => 'phpmail',
		'type' => 'smtp',
		'from' => 'jckv@yandex.ru',
		'charset' => 'utf-8',
		'contentType' => 'html',
		//'contentType' => 'plain',
		'smtpHost' => 'smtp.yandex.ru',
		'smtpPort' => '25',
		'smtpUser' => 'jckv',
		'smtpPassword' => 'tylerd(21)'
	)
);