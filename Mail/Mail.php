<?php

namespace Xorc\Mail;

use Xorc\Controller\Registry as Registry;

/**
 * Абстрактный класс для работы с почтой. 
 * Все почтовые классы должны наследоваться от него.
 * Содержит метод фабрику create() возвращающий объект для работы с почтой.
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com>
 * @version 2.0
 */
abstract class Mail {
	
	/**
	 * Тип транспорта почты: стандартная функция php mail() или smtp
	 * @var string
	 */
	private $type;
	
	/**
	 * Реестр
	 * @var array
	 */
	protected $registry;
	
	/**
	 * Массив заголовков
	 * @var array
	 */
	protected $headers;
	
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
	 * Массив получателей копии сообщения
	 * @var array
	 */
	protected $cc;
	
	/**
	 * Массив получателей скрытой копии сообщения
	 * @var array
	 */
	protected $bcc;
	
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
	 * Кодировка письма
	 * @var string
	 */
	protected $charset;
	
	/**
	 * Content-type письма: text | html
	 * @var string
	 */
	protected $contentType;
	
	/**
	 * Конструктор класса
	 */
	public function __construct() {
	
		// загружаем реестр
		$this->registry =& Registry::getInstance();
	
		// загружаем из реестра отправителя
		$this->from = $this->registry['mail']['from'];
		
		// загружаем из реестра кодировку
		$this->charset = $this->registry['mail']['charset'];
		
		// загружаем из реестра content-type
		$this->contentType = $this->registry['mail']['contentType'];
	
	}
	
	/**
	 * Устанавливает отправителя почты (если не задан, берется из настроек php)
	 * @param string $from
	 * @return \Xorc\Mail\Mail
	 */
	public function from($from) {
		$this->from = $from;
		return $this;
	}
	
	/**
	 * Устанавливает получателя (получателей) почтового сообщения
	 * @param multitype: string | array $to
	 * @return \Xorc\Mail\Mail
	 */
	public function to($to) {
		$this->toArray($to, $this->to);
		return $this;
	}
	
	/**
	 * Устанавливает получателя (получателей) копии почтового сообщения
	 * @param multitype: string | array $cc
	 * @return \Xorc\Mail\Mail
	 */
	public function cc($cc) {
		$this->toArray($cc, $this->cc);
		return $this;
	}
	
	/**
	 * Устанавливает получателя (получателей) скрытой копии почтового сообщения
	 * @param multitype: string | array $bcc
	 * @return \Xorc\Mail\Mail
	 */
	public function bcc($bcc) {
		$this->toArray($bcc, $this->bcc);
		return $this;
	}
	
	/**
	 * Устанавливает тему сообщения
	 * @param string $subject
	 * @return \Xorc\Mail\Mail
	 */
	public function subject($subject) {
		$this->subject = $subject;
		return $this;
	}
	
	/**
	 * Устанавливает текст сообщения
	 * @param string $message
	 * @return \Xorc\Mail\Mail
	 */
	public function message($message) {
		$this->message = $message;
		return $this;
	}
	
	/**
	 * Устанавливает кодировку сообщения
	 * @param string $charset
	 * @return \Xorc\Mail\Mail
	 */
	public function charset($charset) {
		$this->charset = $charset;
		return $this;
	}
	
	/**
	 * Устанавливает content-type сообщения
	 * @param string $contentType Варианты: plain | html
	 * @return \Xorc\Mail\Mail
	 */
	public function contentType($contentType) {
		$this->contentType = $contentType;
		return $this;
	}
	
	/**
	 * Устанавливает общие заголовки
	 */
	protected function commonHeaders() {
		
		$this->headers[] = 'Date: ' . date("D, d M Y H:i:s") . ' UT';
		$this->headers[] = 'MIME-Version: 1.0';
		$this->headers[] = 'Content-Transfer-Encoding: 8bit';
		$this->headers[] = "X-Priority: 3";
		
		// Если задан отправитель, включаем его в заголовки
		if ($this->from != null) {
			$this->headers[] = 'From: ' . $this->from;
			$this->headers[] = 'Return-Path: ' . $this->from;
			$this->headers[] = 'Reply-To: ' . $this->from;
		}
		
		// Если задан content-type или кодировка, включаем их в заголовки
		if ($this->charset != null || $this->contentType != null) {
			$this->contentType = $this->contentType != null ? $this->contentType : 'plain';
			$this->charset = $this->charset != null ? $this->charset : 'utf-8';
			$this->headers[] = 'Content-type: text/' . $this->contentType . '; charset="' . $this->charset . "\"";
		}
		
		// Если задна получатель(ли) копии почты, включаем в заголовки
		if ($this->cc != null) {
			$this->headers[] = 'Cc: ' . implode(',', $this->cc);
		}
		
		// Если задна получатель(ли) скрытой копии почты, включаем в заголовки
		if ($this->bcc != null) {
			$this->headers[] = 'Bcc: ' . implode(',', $this->bcc);
		}
	}
	
	/**
	 * Отправляет сообщение
	 */
	public abstract function send();
	
	/**
	 * Фабрика для создания обекта почты
	 * @param string $type Тип используемой почтовой службы: 'phpmail', 'smtp', null
	 * @return \Xorc\Mail\Mail
	 */
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
	
	/**
	 * Вспомогательный метод, добавляет в массив другой массив или строку
	 * @param multitype: array | string $arg массив или строка, добавляемые к исходному масииву
	 * @param array $array исходный массив
	 */
	private function toArray($arg, &$array) {
		if (\is_array($arg)) $array = array_merge($array, $arg);
		if (\is_string($arg)) $array[] = $arg;
	}
	
}