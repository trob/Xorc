<?php
namespace Xorc\Mail;

class Phpmail extends MailAbstract {
	
	public function send() {
		\mail($this->to, $this->subject, $this->message);
	}
	
}