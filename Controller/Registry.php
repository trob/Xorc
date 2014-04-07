<?php

namespace Xorc\Controller;

/**
 * Реестр переменных
 * @package Xorc framework
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com> http://rc21net.ru
 * @version 1.0
 * @copyright Copyright (c) 2013 Roman Kazakov http://rc21net.ru
 * @license GNU General Public License v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
class Registry {

	/**
	 * Массив реестра
	 * @var <i>array</i>
	 */
	private static $registry = Array();

	private function __construct() {}
	private function __clone() {}

	/**
	 * Возвращает <b>ссылку</b> на реестр. <b><i>Присваивать возвращаемое значение необходимо тоже по ссылке</i></b>:
	 * <code>$variable =<b>&</b> Registry::getInstance();</code>
	 */
	public static function &getInstance() {
		return self::$registry;
	}

	/**
	 * Обнуляет (удаляет) реестр
	 */
	public static function unsetInstance() {
		self::$registry = null;
	}

}