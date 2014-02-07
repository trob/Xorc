<?php

namespace Xorc\Controller;

/**
 * Класс для подсчета времени генерации страницы.
 * @author Roman Kazakov (a.k.a. RC21) <rc21@mail.ru>
 * @version 1.0.20110918
 */
class ExecTime {

	/**
	 * Время начала генерации страницы.
	 * @var <i>float</i>
	 */
	protected $startTime;

	/**
	 * Время, затраченное на генерацию страницы.
	 * @var <i>float</i>
	 */
	protected $endTime;

	/**
	 * Конструктор класса: устанавливает время начала генерации страницы.
	 * @return <i>object</i> Объект класса
	 */
	public function __construct(){
		$this->startTime = microtime(true);
	}

	/**
	 * Позволяет переустановить время начала генерации страницы, заданное при создании объекта
	 * @return <i>void</i>
	 */
	public function setStartTime(){
		$this->startTime = microtime(true);
	}

	/**
	 * Возвращает время начала генерации страницы.
	 * @return <i>float</i>
	 */
	public function getStartTime(){
		return $this->startTime;
	}

	/**
	 * Расчитывает время генерации страницы и возвращает его в формате секунд.
	 * @return <i>string</i>
	 */
	public function getEndTime(){
		$this->endTime = microtime(true) - $this->startTime;
		return sprintf("%f сек.", $this->endTime);
	}

	/**
	 * Статичный метод по функционалу полностью аналогичен getEndTime(),
	 * но может быть использован в XSL
	 * @param <i>float</i> <b>$startTime</b>
	 * @return <i>string</i>
	 */
	public static function staticGetEndTime($startTime){
		$endTime = microtime(true) - $startTime;
		return sprintf("%f сек.", $endTime);
	}
}