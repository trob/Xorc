<?php

namespace Xorc\Mail;

use Xorc\Controller\Registry;

abstract class MailAbstract {
	
	/**
	 * Отправитель сообщения
	 * @var string
	 */
	protected $from;
	
	/**
	 * Массив получателей сообщения
	 * @var array
	 */
	protected $to;
	
	/**
	 * Тема сообщения
 	 * @var sring
	 */
	protected $subject;
	
	/**
	 * Тело сообщения
	 * @var string
	 */
	protected $message;
	
	/**
	 * Реестр
	 * @var array
	 */
	private $registry;
	
	public function __construct() {
		
		$this->registry =& Registry::getInstance();
		
		$this->from = $this->registry['mail']['from'];
		
	}
	
	public function from($from) {
		$this->from = $from;
		return $this;
	}
	
	public function to($to) {
		if (\is_array($to)) $this->to .= $to;
		if (\is_string($to)) $this->to[] = $to;
		return $this;
	}
	
	public function subject($subject) {
		$this->subject = $subject;
		return $this;
	}
	
	public function message($message) {
		$this->message = $message;
		return $this;
	}
	
	public abstract function send();
	
	
}