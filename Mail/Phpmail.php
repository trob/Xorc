<?php
namespace Xorc\Mail;

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