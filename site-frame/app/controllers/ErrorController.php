<?php

/**
 * Error controller
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com>
 * @version 1.0
 */
class ErrorController {

	protected $registry;

	private $viewParams;

	public function __construct() {

		$this->registry =& Registry::getInstance();

	}

	public function indexAction() {
		header('HTTP/1.1 404 Not Found');

		$this->viewParams['page'] = 'error404';

		require_once $this->registry['path']['lib'].'XslView.php';
		$view = new XslView($this->registry['path']['views'].'error.xsl');

		$view	->setParams($this->viewParams)
				->transform()
				->show();


	}
}