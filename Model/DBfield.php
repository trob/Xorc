<?php
namespace Xorc\Model;

/**
 * Представление поля таблицы
 * @package Xorc framework
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com> http://rc21net.ru
 * @version 1.0
 * @copyright Copyright (c) 2013 Roman Kazakov http://rc21net.ru
 * @license GNU General Public License v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
class DBfield {

	/**
	 * Название поля
	 * @var string
	 */
	public $name;

	/**
	 * Обязательное поле
	 * @var boolean
	 */
	public $mandatory;

	/**
	 * Тип поля
	 * @var string
	 */
	public $type;

	/**
	 * Минимальное значение
	 * @var unknown
	 */
	public $minValue;

	/**
	 * Максимальное значение
	 * @var unknown
	 */
	public $maxValue;

	public function __construct($name, $mandatory, $type, $minValue, $maxValue) {
		$this->name = $name;
		$this->mandatory = $mandatory;
		$this->type = $type;
		$this->minValue = $minValue;
		$this->maxValue = $maxValue;
	}
}