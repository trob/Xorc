<?php
define(ROOT_PATH, realpath(dirname(__FILE__) . '/..'));

require_once ROOT_PATH.'/../lib/php/Xorc/ExecTime.php';
$execTime = new ExecTime();

require_once ROOT_PATH.'/app/controllers/FrontController.php';

$frontController = new FrontController();
$frontController->run();

/* echo $execTime->getEndTime(); */