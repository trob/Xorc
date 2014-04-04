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
	
	static public function create($type = null) {
		
		// если тип передан в конструктор используем его
		if ($type) $this->type = $type;
		
		// если не передан, используем из конфига
		else {
			$self->registry =& Registry::getInstance();
			$self->type = $self->registry['mail']['type'];
		}
		
		// если в конфиге тоже не определен, используем по умолчанию php mail()
		if ($self->type == null) $self->type = 'phpmail';
		
		$class = __NAMESPACE__ . '\\' . $self->type;
		
		return new $class();
	}
	
}