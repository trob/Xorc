<?php

namespace Xorc\Mail;

use Xorc\Controller\Registry as Registry;

/**
 * Фабрика для почтового транспорта
 * @author rc21
 *
 */
class Mail {
	
	/**
	 * Тип транспорта почты: стандартная функция php mail() или smtp
	 * @var string
	 */
	private $type;
	
	/**
	 * Реестр
	 * @var array
	 */
	private $registry;
	
	public function __construct($type = null) {
		
		// если тип передан в конструктор используем его
		if ($type) $this->type = $type;
		
		// если не передан, используем из конфига
		else {
			$this->registry =& Registry::getInstance();
			$this->type = $this->registry['mail']['type'];
		}
		
		// если в конфиге тоже не определен, используем по умолчанию php mail()
		if ($this->type == null) $this->type = 'phpmail';
		
		$class = __NAMESPACE__ . '\\' . $this->type;
		
		return new $class();
	}
	
}