<?php

require_once ROOT_PATH.'{path-to-Xorc}/FrontControllerAbstract.php';

/**
 * Front controller
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com>
 * @version 1.0
 */
class FrontController extends FrontControllerAbstract {

	protected function addRoutes() {

		$this->router->addRoute(
			'exampleRoute',
			array(	'pattern'		=> '/\/exampleRoute\/([^\?]+?)((\/$)|(\/\?)|\?|$)/',
					'controller'	=> 'exampleRouteController',
					'action'		=> 'exampleRouteAction',
					'params'		=> 2
				)
		);

	}

}