<?php

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
	public function __construct() {
		$this->registry =& Registry::getInstance();
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

			if (!class_exists('ErrorController')) throw new Exception();
			$controller = new ErrorController();
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
	 * @throws <i>Exception</i> Неудалось запустить контроллер: не найден файл, нет класса, нет метода
	 */
	public function dispatch() {
		try {

			$file = $this->controllersPath.$this->moduleName.$this->controllerName.'.php';

			if (!file_exists($file)) throw new Exception();
			require_once $file;

			if (!class_exists($this->controllerName)) throw new Exception();
			$controller = new $this->controllerName;

			if (!method_exists($controller, $this->actionName)) throw new Exception();
			$action = $this->actionName;
			$controller->$action();

		} catch (Exception $e) {
			$this->errorController();
		}

	}
}