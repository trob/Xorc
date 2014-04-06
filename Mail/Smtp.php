<?php

namespace Xorc\Mail;

class Smtp extends Mail{
	
	/**
	 * SMTP хост для отправки почты
	 * @var string
	 */
	private $smtpHost;
	
	/**
	 * SMTP порт
	 * @var string
	 */
	private $smtpPort;
	
	/**
	 * Имя пользователя для авторизации на smtp хосте
	 * @var string
	 */
	private $smtpUser;
	
	/**
	 * Пароль для авторизации на smtp хосте
	 * @var string
	 */
	private $smtpPassword;
	
	/**
	 * Идентификатор сокета соединения с SMTP сервером
	 * @var resource
	 */
	private $socket;
	
	/**
	 * Конструктор класса
	 */
	public function __construct() {
		
		// выполняем родительский конструктор
		parent::__construct();
		
		// загружаем из реестра SMTP хост
		$this->smtpHost = $this->registry['mail']['smtpHost'];
		
		// загружаем из реестра SMTP порт
		$this->smtpPort = $this->registry['mail']['smtpPort'];
		
		// загружаем из реестра SMTP пользователя
		$this->smtpUser = $this->registry['mail']['smtpUser'];
		
		// загружаем из реестра SMTP пароль
		$this->smtpPassword = $this->registry['mail']['smtpPassword'];
		
	}

	/**
	 * Устанавливает SMTP хост
	 * @param string $smtpHost
	 * @return \Xorc\Mail\Smtp
	 */
	public function smtpHost($smtpHost) {
		$this->smtpHost = $smtpHost;
		return $this;
	}
	
	/**
	 * Устанавливает SMTP порт
	 * @param string $smtpPort
	 * @return \Xorc\Mail\Smtp
	 */
	public function smtpPort($smtpPort) {
		$this->smtpPort = $smtpPort;
		return $this;
	}
	
	/**
	 * Устанавливает имя пользователя для авторизации на SMTP сервере
	 * @param string $smtpUser
	 * @return \Xorc\Mail\Smtp
	 */
	public function smtpUser($smtpUser) {
		$this->smtpUser = $smtpUser;
		return $this;
	}
	
	/**
	 * Устанавливает пароль для авторизации на SMTP сервере
	 * @param string $smtpPassword
	 * @return \Xorc\Mail\Smtp
	 */
	public function smtpPassword($smtpPassword) {
		$this->smtpPassword = $smtpPassword;
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Xorc\Mail\Mail::send()
	 */
	public function send() {
		
		// Устанавливаем общие заголовки
		$this->commonHeaders();
		
		// Добавляем в заголовки получателя,
		$this->headers[] = 'To: ' . implode(',', $this->to);
		// тему,
		$this->headers[] = 'Subject: =?'. $this->charset . '?B?' . base64_encode($this->subject) . "=?=";
		// пустая строка перед телом письма,
		$this->headers[] = '';
		// тело писмьма,
		$this->headers[] = $this->message;
		// строка с точкой в конце
		$this->headers[] = '.';
		
		// получатели
		$recipients = $this->to;
		if ($this->cc != null) $recipients = array_merge($recipients, $this->cc);
		if ($this->bcc != null) $recipients = array_merge($recipients, $this->bcc);
		
		
		try {
			
			// открываем соединение
			if (!$this->socket = fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, 30)) throw new \Exception($errno . ' - ' . $errstr);
			
			// ожидаем ответ сервера
			while ($line = fgets($this->socket, 515)) {
				// если 4-ый символ пробел значит ответ получен, выходим из цикла ожидания
				if (substr($line, 3, 1) == ' ') break;
			}
			
			// достаем код ответа (первые три цифры)
			$answer = substr($line, 0, 3);
			
			// если 220, то все ок, иначе бросам исключение
			if($answer != '220') throw new \Exception($line);
			
					
			$this	->socketSendCommand('HELO ' . $_SERVER['SERVER_NAME'], 		'250') 	// посылаем серверу приветствие и свой адрес
					->socketSendCommand('AUTH LOGIN', 							'334') 	// сообщаем серверу о готовности авторизоваться
					->socketSendCommand(base64_encode($this->smtpUser), 		'334') 	// посылаем серверу логин
					->socketSendCommand(base64_encode($this->smtpPassword), 	'235') 	// посылаем серверу пароль
					->socketSendCommand('MAIL FROM: ' . $this->from, 			'250');	// сообщаем серверу от кого письмо
					
			foreach ($recipients as $k => $recipient) {
				$this->socketSendCommand('RCPT TO: ' . $recipient, 				'250');	// сообщаем серверу кому письмо
			}
			
					
			$this	->socketSendCommand('DATA', 								'354') 	// сообщаем, что начинаем вводить данные
					->socketSendCommand(implode("\r\n", $this->headers), 		'250') 	// передаем само письмо
					->socketSendCommand('QUIT', 								'221'); // сообщаем серверу о закрытии соединения
			
			// закрываем соединение
			fclose($this->socket);
			
		} catch (\Exception $e) {
			die( $e->getMessage() );
		}
	}

	/**
	 * Отправляет комманды серверу и сравнивает полученный ответ с ожидаемым. Если не совпадают, бросает исключение.
	 * @param string $command команда
	 * @param string $answer ожидаемый ответ
	 * @throws \Exception
	 * @return \Xorc\Mail\Smtp
	 */
	private function socketSendCommand($command, $answer) {
		
		// отправляем команду серверу
		fputs( $this->socket, $command . "\r\n" );
		
		// ожидаем ответ сервера
		while ($line = fgets($this->socket, 515)) {
			// если 4-ый символ пробел значит ответ получен, выходим из цикла ожидания
			if (substr($line, 3, 1) == ' ') break;
		}
		
		// записываем код ответа
		$code = substr($line, 0, 3);		
		
		// если код ответа не соответствует ожидаемому бросаем исключение
		if($code != $answer) throw new \Exception($command . '<br />' . $line);
		
		return $this;
	}
		
}