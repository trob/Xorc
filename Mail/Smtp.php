<?php

namespace Xorc\Mail;

class Smtp {
	
	private $smtp_username 	= 'test@test.ru';  //Смените на имя своего почтового ящика.
	private $smtp_port     	= '25'; // Порт работы. Не меняйте, если не уверены.
	private $smtp_host     	= 'smtp.yandex.ru';  //сервер для отправки почты
	private $smtp_password 	= 'pass';  //Измените пароль
	private $smtp_debug   	= true;  //Если Вы хотите видеть сообщения ошибок, укажите true вместо false
	private $smtp_from  	= 'windows-1251';  //кодировка сообщений. (или UTF-8, итд)
	private $smtp_from     	= 'Leon1010'; //Ваше имя - или имя Вашего сайта. Будет показывать при прочтении в поле "От кого"
	
	
	
	public function __construct() {
		echo 'mail - smtp';
	}
	

	
	public function smtpmail($mail_to, $subject, $message, $headers='') {
		
		$SEND = "Date: ".date("D, d M Y H:i:s") . " UT\r\n";
		$SEND .=    'Subject: =?'.$this->smtp_charset.'?B?'.base64_encode($subject)."=?=\r\n";
		if ($headers) $SEND .= $headers."\r\n\r\n";
		else
		{
			$SEND .= "Reply-To: ".$this->smtp_username."\r\n";
			$SEND .= "MIME-Version: 1.0\r\n";
			$SEND .= "Content-Type: text/plain; charset=\"".$this->smtp_charset."\"\r\n";
			$SEND .= "Content-Transfer-Encoding: 8bit\r\n";
			$SEND .= "From: \"".$this->smtp_from."\" <".$this->smtp_username.">\r\n";
			$SEND .= "To: $mail_to <$mail_to>\r\n";
			$SEND .= "X-Priority: 3\r\n\r\n";
		}
		$SEND .=  $message."\r\n";
		if( !$socket = fsockopen($this->smtp_host, $this->smtp_port, $errno, $errstr, 30) ) {
			if ($this->smtp_debug) echo $errno."&lt;br&gt;".$errstr;
			return false;
		}
	
		if (!server_parse($socket, "220", __LINE__)) return false;
	
		fputs($socket, "HELO " . $this->smtp_host . "\r\n");
		if (!server_parse($socket, "250", __LINE__)) {
			if ($this->smtp_debug) echo '<p>Не могу отправить HELO!</p>';
			fclose($socket);
			return false;
		}
		fputs($socket, "AUTH LOGIN\r\n");
		if (!server_parse($socket, "334", __LINE__)) {
			if ($this->smtp_debug) echo '<p>Не могу найти ответ на запрос авторизаци.</p>';
			fclose($socket);
			return false;
		}
		fputs($socket, base64_encode($this->smtp_username) . "\r\n");
		if (!server_parse($socket, "334", __LINE__)) {
			if ($this->smtp_debug) echo '<p>Логин авторизации не был принят сервером!</p>';
			fclose($socket);
			return false;
		}
		fputs($socket, base64_encode($this->smtp_password) . "\r\n");
		if (!server_parse($socket, "235", __LINE__)) {
			if ($this->smtp_debug) echo '<p>Пароль не был принят сервером как верный! Ошибка авторизации!</p>';
			fclose($socket);
			return false;
		}
		fputs($socket, "MAIL FROM: <".$this->smtp_username.">\r\n");
		if (!server_parse($socket, "250", __LINE__)) {
			if ($this->smtp_debug) echo '<p>Не могу отправить комманду MAIL FROM: </p>';
			fclose($socket);
			return false;
		}
		fputs($socket, "RCPT TO: <" . $mail_to . ">\r\n");
	
		if (!server_parse($socket, "250", __LINE__)) {
			if ($this->smtp_debug) echo '<p>Не могу отправить комманду RCPT TO: </p>';
			fclose($socket);
			return false;
		}
		fputs($socket, "DATA\r\n");
	
		if (!server_parse($socket, "354", __LINE__)) {
			if ($this->smtp_debug) echo '<p>Не могу отправить комманду DATA</p>';
			fclose($socket);
			return false;
		}
		fputs($socket, $SEND."\r\n.\r\n");
	
		if (!server_parse($socket, "250", __LINE__)) {
			if ($this->smtp_debug) echo '<p>Не смог отправить тело письма. Письмо не было отправленно!</p>';
			fclose($socket);
			return false;
		}
		fputs($socket, "QUIT\r\n");
		fclose($socket);
		return TRUE;
	}
	
	function server_parse($socket, $response, $line = __LINE__) {
		while (@substr($server_response, 3, 1) != ' ') {
			if (!($server_response = fgets($socket, 256))) {
				if ($this->smtp_debug) echo "<p>Проблемы с отправкой почты!</p>$response<br>$line<br>";
				return false;
			}
		}
		if (!(substr($server_response, 0, 3) == $response)) {
			if ($this->smtp_debug) echo "<p>Проблемы с отправкой почты!</p>$response<br>$line<br>";
			return false;
		}
		return true;
	}

	
	
	
	
	
}