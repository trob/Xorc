<?php

namespace Xorc;

/**
* Маршрутизатор
* @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com>
* @version 1.1
*/
class Router{

	/**
	 * Массив маршрутов
	 * @var <i>Array</i>
	 */
	protected $routes;

	/**
	 * URI для опредеоения маршрута
	 * @var <i>string</i>
	 */
	protected $uri;

	/**
	 * Имя выбранного маршрута
	 * @var <i>string</i>
	 */
	protected $route;

	/**
	* Массив частей uri
	* @var <i>Array</i>
	*/
	protected $routeParts;

	/**
	 * Сдвиг $routeParts в зависимости от модуля
	 * @var <i>integer</i>
	 */
	private $moduleShift;

	/**
	 * Реестр
	 * @var <i>array</i>
	 */
	protected $registry;

	/**
	 * Конструктор класса
	 * @param <i>string</i> <b>$baseUrl</b>
	 */
	public function __construct(){

		$this->registry =& Registry::getInstance();

		$baseUrl = $this->registry[server][baseurl];

		// если базовый url передан - вырезаем его
		if ($baseUrl) {
			$len = strlen($baseUrl);
			// базовый url не содержится в REQUEST_URI
			if (strpos($_SERVER['REQUEST_URI'], $baseUrl) === false) die('BaseURI in the config file is wrong');
			$this->uri = substr($_SERVER['REQUEST_URI'], $len);
		// если базовый url не передан
		}else{
			$this->uri = $_SERVER['REQUEST_URI'];
		}

		$this->registry['uri'] = $this->uri;

		// маршрут по умолчанию
		$this->routes['default'] = array('pattern'		=> '/\/.*/',
										 'controller'	=> 1,
										 'action'		=> 2,
										 'params'		=> 3
		);

	}

	/**
	 * Добавляет маршрут
	 * @param <i>string</i> <b>$key</b> Имя маршрута
	 * @param <i>Array</i> <b>$route</b> Маршрут
	 */
	public function addRoute($key, $route){
		$this->routes[$key] = $route;
	}

	/**
	 * Находит маршрут, соответсвующий uri
	 */
	protected function getRoute() {
		$routes = array_reverse($this->routes);

		foreach ($routes as $name => $route) {
			if (preg_match($route['pattern'], $this->uri)){
				$this->route = $name;
				break;
			}
		}

		return $this;
	}

	/**
	 * Разбивает Url на части по слешу (/)
	 */
	protected function explodeUrl() {
		$path = parse_url($this->uri, PHP_URL_PATH);
		$this->routeParts = explode('/', $path);

		return $this;
	}

	protected function getModuleName() {

		if (isset( $this->routes[$this->route]['module'] )) {
			$this->registry['module'] = $this->routes[$this->route]['module'];
			$this->moduleShift = 0;
			return $this;
		}

		if (empty($this->routeParts[1])) {
			$this->moduleShift = 0;
			return $this;
		}

		if (is_dir($this->registry['path']['controllers'] . $this->routeParts[1])) {
			$this->registry['module'] = $this->routeParts[1];
			$this->moduleShift = 1;
			return $this;
		}

		$this->moduleShift = 0;
		return $this;

	}

	/**
	 * Извлекает имя контроллера
	 */
	protected function getControllerName() {
		$controllerName = $this->routes[$this->route]['controller'];
		if (is_int($controllerName)) {
			$controllerNum = $controllerName + $this->moduleShift;
			$controllerPrefix = !empty($this->routeParts[$controllerNum]) ? ucfirst($this->routeParts[$controllerNum]) : 'Index';
			$this->registry['controller'] = $controllerPrefix . 'Controller';
		} else {
			$this->registry['controller'] = $controllerName;
		}

		return $this;
	}

	/**
	 * Извлекает имя действия
	 */
	protected function getActionName() {
		$actionName = $this->routes[$this->route]['action'];
		if (is_int($actionName)) {
			$actionNum = $actionName + $this->moduleShift;
			$actionPrefix = !empty($this->routeParts[$actionNum]) ? $this->routeParts[$actionNum] : 'index';
			$this->registry['action'] = $actionPrefix . 'Action';
		} else {
			$this->registry['action'] = $actionName;
		}

		return $this;
	}

	/**
	 * Извлекает параметры
	 */
	protected function getParams() {

		// если в выбранном маршруте нет правила для params => выход
		if (!isset($this->routes[$this->route]['params'])) return $this;

		$paramsNum = $this->routes[$this->route]['params'] + $this->moduleShift;

		if (count($this->routeParts) <= $paramsNum) return $this;

		// записываем в реестр
		$this->registry['queryParams'] = array_slice($this->routeParts, $paramsNum);

		return $this;
	}

	/**
	 * Запускает процесс маршрутизации
	 */
	public function route(){
		$this	->getRoute()
				->explodeUrl()
				->getModuleName()
				->getControllerName()
				->getActionName()
				->getParams();
	}
}