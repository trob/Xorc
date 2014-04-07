<?php
namespace Xorc\Mail;

/**
 * асширение класа Mail для работы с почтой через стандартную функцию php mail()
 * @package Xorc framework
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com> http://rc21net.ru
 * @version 1.0
 * @copyright Copyright (c) 2013 Roman Kazakov http://rc21net.ru
 * @license GNU General Public License v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
class Phpmail extends Mail {
	
	/**
	 * (non-PHPdoc)
	 * @see \Xorc\Mail\Mail::send()
	 */
	public function send() {
		
		$this->commonHeaders();
		
		// Отправляем почту
		mail(
				implode(',', $this->to),
				$this->subject,
				$this->message,
				implode("\r\n", $this->headers)
			);
	}
	
}