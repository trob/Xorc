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
	protected static $registry = Array();

	private function __construct() {}
	private function __clone() {}

	/**
	 * Записывает значение в реестр
	 * @param <i>string</i> <b>$key</b> Ключ значения
	 * @param <i>any</i> <b>$value</b> Значение
	 */
	public static function set($key, $value) {
		self::$registry[$key] = $value;
	}

	/**
	 * Получает значение из реестра
	 * @param <i>string</i> <b>$key</b> Ключ (первый ключь многомерного массива)
	 * @param <i>string</i> <b>$secondKey</b> Вторичный ключь многомерного массива
	 */
	public static function get($key, $secondKey = null) {
		if (isset(self::$registry[$key])) {
			if ($secondKey) $value = self::$registry[$key][$secondKey];
			else $value = self::$registry[$key];
		}
		else $value = null;

		return $value;
	}

	/**
	 * Проверяет наличие значения в реестре
	 * @param <i>string</i> <b>$key</b> Ключ
	 */
	public static function has($key) {
		return isset(self::$registry[$key]);
	}

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