<?php

/**
 * Index controller
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com>
 * @version 1.0
 */
class IndexController {

	protected $registry;

	private $viewParams;

	public function __construct() {

		$this->registry =& Registry::getInstance();

	}

	public function indexAction() {

		$this->viewParams['page'] = $this->registry['uri'];

		require_once $this->registry['path']['lib'].'XslView.php';
		$view = new XslView();

		$view	->setParams($this->viewParams)
				->transform()
				->show();


	}
}