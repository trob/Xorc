<?php

namespace Xorc\Controller;
use \Exception as Exception;

/**
 * Диспетчер контроллеров
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com>
 * @version 1.0
 *
 */
class Dispatcher{

	/**
	 * Реестр
	 * @var <i>array</i>
	 */
	protected $registry;

	/**
	 * Namespace приложения
	 * @var string
	 */
	protected $appNameSpace;
	
	/**
	* Путь к дериктории контроллеров
	* @var <i>string</i>
	*/
	protected $controllersPath;

	/**
	* Имя модуля
	* @var <i>string</i>
	*/
	protected $moduleName;

	/**
	 * Имя контроллера
	 * @var <i>string</i>
	 */
	protected $controllerName;

	/**
	 * Имя метода
	 * @var <i>string</i>
	 */
	protected $actionName;

	/**
	 * Диспетчер контроллеров
	 * @param <i>array</i> <b>$request</b> Объект запроса
	 * @param <i>string</i> <b>$controllersPath</b> Путь к дериктории контроллеров
	 */
	public function __construct($appNameSpace) {
		$this->registry =& Registry::getInstance();
		$this->appNameSpace = $appNameSpace;
		$this->controllersPath = $this->registry['path']['controllers'];
		$this->moduleName = isset($this->registry['module']) ? $this->registry['module'].'/' : '';
		$this->controllerName = $this->registry['controller'];
		$this->actionName = $this->registry['action'];
	}

	/**
	 * Вызывает ErrorController
	 * @throws <i>Exception</i> Неудалось запустить ErrorController: не найден файл, нет класса, нет метода
	 */
	protected function errorController() {
		try {
			$file = $this->controllersPath.'ErrorController.php';

			if (!file_exists($file)) throw new Exception();
			require_once $file;

			$fullClassName = $this->appNameSpace.'\\ErrorController';
			
			if (!class_exists($fullClassName)) throw new Exception();
			$controller = new $fullClassName;
			$this->registry['controller'] = 'ErrorController';

			if (!method_exists($controller, 'indexAction')) throw new Exception();
			$this->registry['action'] = $action = 'indexAction';
			$controller->$action();

		} catch (Exception $e) {
			die('No ErrorController');
		}
	}

	/**
	 * Вызывает контроллер
	 * @param string $appNameSpace - Namespace приложения
	 * @throws <i>Exception</i> Неудалось запустить контроллер: не найден файл, нет класса, нет метода
	 */
	public function dispatch() {
		try {
			$file = $this->controllersPath.$this->moduleName.$this->controllerName.'.php';
			
			if (!file_exists($file)) throw new Exception('Controller file does not found');
			require_once $file;
			
			$fullClassName = $this->appNameSpace.'\\'.$this->controllerName;
			
			//echo $fullClassName;
			if (!class_exists($fullClassName)) throw new Exception('Controller class does not found');
			$controller = new $fullClassName;

			if (!method_exists($controller, $this->actionName)) throw new Exception('Action does not found');
			$action = $this->actionName;
			$controller->$action();

		} catch (Exception $e) {
			echo $e->getMessage();
			//$this->errorController();
		}

	}
}