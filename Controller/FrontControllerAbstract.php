<?php

namespace Xorc\Controller;

/**
 * Фронт контроллер
 * @package Xorc framework
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com> http://rc21net.ru
 * @version 1.0
 * @copyright Copyright (c) 2013 Roman Kazakov http://rc21net.ru
 * @license GNU General Public License v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
class FrontControllerAbstract {

	/**
	 * Многомерый массив настроек
	 * @var array
	 */
	protected  $config;

	/**
	 * Реестр
	 * @var array
	 */
	protected $registry;
	
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
		
		$configPath = ROOT_PATH.'/app/config/config.';
		
		if (file_exists($configPath.'php')) {
			require_once $configPath.'php';
			$this->config = $Config;
			unset($Config);
		}
		elseif (file_exists($configPath.'ini')) {
			$this->config = parse_ini_file($configPath.'ini', true);
		}
		else {
			throw new \Exception('No config file.');
		}
		return $this;
	}

	/**
	 * Загрузчик классов
	 */
	protected  function autoloader() {
		spl_autoload_register(array($this, 'defaultAutoloader'));
		return  $this;
	}
	
	protected function defaultAutoloader($class) {
		if (strpos($class, 'Xorc') === 0) require_once $this->config[path][lib] . $class . '.php';
		else require_once $this->config[path][app] . $class . '.php';
	}
	
	/**
	 * Инициализирует реестр и загружает в него настройки
	 */
	protected function registry() {

		$this->registry =& Registry::getInstance();
		
		foreach ($this->config as $key => $value) {
			$this->registry[$key] = $value;
		}

		unset($this->config);

		$this->config =& $this->registry;

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
				->autoloader()
				->registry()
				->router()
				->dispatcher()
				->unload();
	}
}