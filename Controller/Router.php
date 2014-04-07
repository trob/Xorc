<?php

namespace Xorc\Controller;

/**
 * Маршрутизатор
 * @package Xorc framework
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com> http://rc21net.ru
 * @version 1.0
 * @copyright Copyright (c) 2013 Roman Kazakov http://rc21net.ru
 * @license GNU General Public License v2 or later http://www.gnu.org/licenses/gpl-2.0.html
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
			if (strpos($_SERVER['REQUEST_URI'], $baseUrl) === false) die('BaseURI in the config file is wrong'); // TODO: throw exception
			$this->uri = substr($_SERVER['REQUEST_URI'], $len);
		// если базовый url не передан
		}else{
			$this->uri = $_SERVER['REQUEST_URI'];
		}

		$this->registry['uri'] = $this->uri;

		// маршрут по умолчанию
		$this->routes['default'] = array('pattern'		=> '/\/.*/',
										 'controller'	=> 0,
										 'action'		=> 1,
										 'params'		=> 2
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
		// переворачиваем массив маршрутов, чтоб дефолтный использовался последним, а первым - добавленный последним
		$routes = array_reverse($this->routes);

		// перебираем маршруты, ищем первый совпадающий с uri, записываем его ключ (имя) в $this->route
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
		
		// вырезаем из uri его path (после хоста и до параметров) и срезаем слешы в начале и конце
		$path = trim(parse_url($this->uri, PHP_URL_PATH), '/');
		
		// разбиваем path по слешу
		$this->routeParts = explode('/', $path);

		return $this;
	}

	/**
	 * Извлекает имя модуля контроллеров из выбранного маршрута
	 * @return \Xorc\Controller\Router
	 */
	protected function getModuleName() {
		
		// если в выбранном маршруте указан модуль, используем его
		if (isset( $this->routes[$this->route]['module'] )) {
			$this->registry['module'] = $this->routes[$this->route]['module'];
			$this->moduleShift = 0;
			return $this;
		}
		
		// если path пустой - модуля нет
		if (empty($this->routeParts[0])) {
			$this->moduleShift = 0;
			return $this;
		}

		// TODO: проверить, возможно другой механизм, например, регистрация модулей в конфиге
		// если в директории контроллеров есть папка с названием как первый компонент path - это модуль, используем его
		if (is_dir($this->registry['path']['app'] . $this->registry['ns']['controllers'] . $this->routeParts[0])) {
			$this->registry['module'] = $this->routeParts[0];
			$this->moduleShift = 1;
			return $this;
		}

		$this->moduleShift = 0;
		return $this;

	}

	/**
	 * Извлекает имя контроллера из выбранного маршрута
	 * @return \Xorc\Controller\Router
	 */
	protected function getControllerName() {
		
		// достаем из выбранного маршрута название контроллера
		$controllerName = $this->routes[$this->route]['controller'];
		
		// если это число, значит имя контроллера содержится в одной из частей маршрута => извлекаем ее
		if (is_int($controllerName)) {
			// если был найден модуль, необходимо сдвинуть имя на единицу
			$controllerNum = $controllerName + $this->moduleShift;
			// если соответствующая част маршрута не пустая, берем ее в качестве имени, если нет - Index
			$controllerPrefix = !empty($this->routeParts[$controllerNum]) ? ucfirst($this->routeParts[$controllerNum]) : 'Index';
			// склеиваем имя контроллера
			$this->registry['controller'] = $controllerPrefix . 'Controller';
			
		// если имя контроллера из маршрута не число, значит используем несредственно его
		} else {
			$this->registry['controller'] = $controllerName;
		}
	
		
		
		return $this;
	}

	/**
	 * Извлекает имя действия из выбранного маршрута
	 * @return \Xorc\Controller\Router
	 */
	protected function getActionName() {
		
		// достаем из выбранного маршрута название действия
		$actionName = $this->routes[$this->route]['action'];
		
		// если это число, значит имя действия содержится в одной из частей маршрута => извлекаем ее
		if (is_int($actionName)) {
			// если был найден модуль, необходимо сдвинуть имя на единицу
			$actionNum = $actionName + $this->moduleShift;
			// если соответствующая част маршрута не пустая, берем ее в качестве имени, если нет - index
			$actionPrefix = !empty($this->routeParts[$actionNum]) ? $this->routeParts[$actionNum] : 'index';
			// склеиваем имя действия
			$this->registry['action'] = $actionPrefix . 'Action';
			
		// если имя действия из маршрута не число, значит используем несредственно его
		} else {
			$this->registry['action'] = $actionName;
		}

		return $this;
	}

	/**
	 * Извлекает параметры
	 * @return \Xorc\Controller\Router
	 */
	protected function getParams() {

		// если в выбранном маршруте нет правила для params => выход
		if (!isset($this->routes[$this->route]['params'])) return $this;

		// если был найден модуль - сдвигаем на единицу
		$paramsNum = $this->routes[$this->route]['params'] + $this->moduleShift;

		// если в маршруте не содержится параметров => выход
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