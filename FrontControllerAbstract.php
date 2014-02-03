<?php

namespace Xorc;

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
	 * Инициализирует реестр и загружает в него настройки
	 */
	protected function registry() {
		require_once $this->config[path][lib].'Registry.php';

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

		require_once $this->config[path][lib].'Router.php';
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
		require_once $this->config[path][lib].'Dispatcher.php';
		$dispatcher = new Dispatcher();
		$dispatcher->dispatch();
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
				->registry()
				->router()
				->dispatcher()
				->unload();
	}
}