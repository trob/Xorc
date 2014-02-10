<?php

namespace Xorc\Controller;

/**
 * Фронт контроллер
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com>
 * @version 1.0
 */
class FrontControllerAbstract {

	/**
	 * Многомерый массив настроек
	 * @var array
	 */
	protected  $config;

	/**
	 * Маршрутизатор
	 * @var Router
	 */
	protected $router;

	protected $appNameSpace;
	
	/**
	 * Конструктор класса
	 */
	public function __construct() {
		
	}
	
	/**
	 * Метод для загрузки настроек из файла конфигурации
	 */
	protected function loadConfig() {
		$this->config = parse_ini_file(ROOT_PATH.'/app/config/config.ini', true);
		return $this;
	}

	/**
	 * Загрузчик классов
	 */
	protected  function autoloader() {
		spl_autoload_register(array($this, 'libAutoloader'));
		return  $this;
	}
	
	protected function libAutoloader($class) {
		if (strpos($class, 'Xorc') === 0) require_once $this->config[path][lib] . $class . '.php';
	}
	
	/**
	 * Инициализирует реестр и загружает в него настройки
	 */
	protected function registry() {

		foreach ($this->config as $key => $value) {
			Registry::set($key, $value);
		}

		unset($this->config);

		$this->config =& Registry::getInstance();

		return $this;
	}

	/**
	 * Вызывает маршрутизатор
	 */
	protected function router() {

		$this->router = new Router();

		$this->addRoutes();

		$this->router->route();

		return $this;
	}
	
	/**
	 * Добавляет маршруты. Может быть перегружен в фронт-контроллере
	 */
	protected function addRoutes(){

	}

	/**
	 * Вызывает диспетчер контроллеров
	 */
	protected function dispatcher() {
		$dispatcher = new Dispatcher();
		$dispatcher->dispatch($this->appNameSpace);
		return $this;
	}

	/**
	 * Выгружает приложение
	 */
	protected function unload() {
		Registry::unsetInstance();
	}

	/**
	 * Общий запуск FrontController'а
	 */
	public function run() {

		$this	->loadConfig()
				->autoloader()
				->registry()
				->router()
				->dispatcher()
				->unload();
	}
}