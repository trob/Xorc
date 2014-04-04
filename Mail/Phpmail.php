<?php
namespace Xorc\Mail;

class Phpmail extends MailAbstract {
	
	public function send() {
		
		foreach ($this->to as $key=>$value) {
			$this->toStr .= $value;
		}
		
		\mail($this->toStr, $this->subject, $this->message);
	}
	
}