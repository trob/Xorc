<?php

namespace Xorc\Model;

use Xorc\Model\DataBase\MySqli 	as MySqli,
	Xorc\Controller\Mail 		as Mail,
	\Exception 					as Exception;
use Xorc;
	
/**
 * Класс для работы с пользователями
 * @package Xorc framework
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com> http://rc21net.ru
 * @version 1.0
 * @copyright Copyright (c) 2013 Roman Kazakov http://rc21net.ru
 * @license GNU General Public License v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
 abstract class UserAbstract extends MySqli {

 	/**
 	 * Название таблицы пользователей в БД
 	 * @var string
 	 */
	protected $userDBtable;

	/**
	 * Названия полей в базе данных
	 * @var array
	 */
	protected $userDBfields;

	/**
	 * Название сессии
	 * @var string
	 */
	protected $sessionName;

	/**
	 * ID пользователя
	 * @var string
	 */
	protected $id;

	/**
	 * Логин пользователя
	 * @var string
	 */
	protected $login;

	/**
	 * Хэш пароля
	 * @var string
	 */
	protected $passwordHash;

	/**
	 * Статус пользователя
	 * @var integer
	 */
	protected $status;
	
	/**
	 * Опциональные свойства пользователя
	 * @var array
	 */
	public $properties;
	
	/**
	 * Массив сообщений об ошибках
	 * @var array
	 */
	protected $exceptionMessages;

	/**
	 * Названия хранимых процедур
	 * @var array
	 */
	protected $userDBprocedures;
	
	
	/**
	 * Конструктор класса
	 */
	public function __construct() {
		
		// Выполняем родительский конструктор: подключение к БД и т.д.
		parent::__construct();

		// Названия полей в таблице
		$this->userDBfields = array(
			'id' => new DBfield('id', true, 'integer', null, null),
			'login' => new DBfield('login', true, 'string', 3, 50),
			'password' => new DBfield('password', true, 'string', 5, 50),
			'status' => new DBfield('status', true, 'integer', null, null),
			'email' => new DBfield('email', false, 'string', null, null),
			'registrationDate' => new DBfield('registrationDate', false, 'date', null, null),
			'lastAccessDate' => new DBfield('lastAccessDate', false, 'date', null, null)
		);
		
		// Название сессии
		$this->sessionName = 'Xorc_session';
		
		// Заполняем массив сообщений об ошибках
		$this->exceptionMessages = array(
			0 => 'You need to fill all fields.',
			1 => 'No user with such name.',
			2 => 'Wrong password.',
			3 => 'Access denied.',
			4 => 'User with such name already exists. Choose another name',
			5 => 'Login length must be from ' . $this->userDBfields['login']->minValue . ' till ' . $this->userDBfields['login']->maxValue . ' symbols.',
			6 => 'Password length must be from ' . $this->userDBfields['password']->minValue . ' till ' . $this->userDBfields['password']->maxValue . ' symbols.',
			7 => 'Registration error. Try again.',
			8 => 'Email needs for registration.',
			9 => 'No user with such id.',
			10 => 'Wrong activation parameters'
		);

		// Выполняем дополнительные действия из наследуемых классов
		$this->initExtendProperties();
	}

	/**
	 * Абстрактный класс, вызывается в конце конструктора
	 * Необходимо задать:
	 * $this->userDBtable - имя таблицы с пользователями
	 */
	abstract protected function initExtendProperties();

	/**
	 * Получить пользователя по ID
	 * @param string $id
	 * @return \Xorc\Model\UserAbstract
	 */
	public function get($id) {
		
		if ($this->userDBprocedures['get'] != null) {
			if (!$this->properties = $this->callProcedure($this->userDBprocedures['get'], $id)) throw new Exception($this->exceptionMessages[9]);
		}
		else {
			if (!$this->properties = $this->getObjectByParam($this->userDBtable, $this->userDBfields['id']->name, $id , 'assoc')) throw new Exception($this->exceptionMessages[9]);
		}
		return $this;
	}

	/**
	 * Добавить пользователя в базу
	 * @param array $prop массив с логином и паролем
	 * @throws Exception
	 * @return \Xorc\Model\UserAbstract
	 */
	public function add($prop) {

		$login = $prop[$this->userDBfields['login']->name];
		$password = $prop[$this->userDBfields['password']->name];
		
		// Проверяем логин и пароль на пустоту
		if (empty($login) || empty($password)) throw new Exception($this->exceptionMessages[0]);

		// Ограничения длинны логина и пароля
		if (strlen($login) < $this->userDBfields['login']->minValue || strlen($login) > $this->userDBfields['login']->maxValue) throw new Exception($this->exceptionMessages[5]);
		if (strlen($password) < $this->userDBfields['password']->minValue || strlen($password) > $this->userDBfields['password']->maxValue) throw new Exception($this->exceptionMessages[6]);

		// Экранируем логин и пароль
		$this->properties['login'] = $this->escape($login, 'str');
		$this->properties['password'] = md5($this->escape($password, 'str'));

		// Проверяем есть ли пользователь с таким логином
		if ($this->getObjectByParam($this->userDBtable, $this->userDBfields['login']->name, $this->login)) throw new Exception($this->exceptionMessages[4]);

		// экранируем и добавляем в массив осиальные свойства
		foreach ($this->userDBfields as $key => $property) {
			$this->properties[$property->name] = $prop[$property->name] ? $this->escape($prop[$property->name], $property->type) : null;
		}
		
		// Добавляем в базу
		if ($this->userDBprocedures['add'] != null) {
			if (!$this->callProcedure($this->userDBprocedures['add'], $this->properties)) throw new Exception($this->exceptionMessages[7]);
		}
		else {
			if (!$this->properties['id'] = $this->insertByName($this->userDBtable, $this->properties)) throw new Exception($this->exceptionMessages[7]);
		}
		
		return $this;
	}

	/**
	 * Активация пользователя
	 * @param integer $id - ID пользователя
	 * @param string $solt - секрет для активации
	 * @throws \Exception
	 * @return \Xorc\Model\UserAbstract
	 */
	public function activate($id, $solt) {
		
		$this->get($this->escape($id, 'integer'));
		
		$registerSolt = md5($this->properties['email'].$this->properties['registrationDate']);
		
		if ($registerSolt == $this->escape($solt, 'string')) {
			$this->update($this->userDBtable, array($this->userDBfields['status']->name, 1), $this->userDBfields['id']->name, $this->properties['id']);
		} else {
			throw new \Exception($this->exceptionMessages[10]);
		}
		
		return $this;
	}
	
	/**
	 * Регистрация пользователя (добавление в базу и отправка email)
	 * @param array $prop массив с логином и паролем
	 * @return \Xorc\Model\UserAbstract
	 */
	public function register($prop, $message) {
		
		// дата регистрации
		$this->properties['registrationDate'] = time();
		
		// устанавливаем статус
		$this->properties['status'] = 0;
		
		// добавляем пользователя
		$this->add($prop);
		
		// если не указан email, выбрасываем ошибку
		if ($this->properties['email'] == null) throw new \Exception($this->exceptionMessages[8]);
		
		// секрет для активации
		$registerSolt = md5($this->properties['email'].$this->properties['registrationDate']);
		
		$search = array('{id}', '{solt}');
		$replace = array($this->properties['id'], $registerSolt);
		
		// отправляем сообщение
		$mail = Xorc\Mail\Mail::create();
		$mail 	->to($this->properties['email'])
				->subject($message['subgect'])
				->message(str_replace($search, $replace, $message['body']))
				->send();
		
		return $this;
	}
	
	/**
	 * Проверяет авторизацию пользователя
	 * @throws <i>Exception</i> Если пользователь не авторизован выбрасывается исключение
	 * @return \Xorc\Model\UserAbstract
	 */
	public function auth() {

		session_name($this->sessionName);
		session_start();

		if (!isset($_SESSION['id'])) throw new Exception($this->exceptionMessages[3]);

		$hash = $this->getSessionHash($_SESSION['id']);
		if ($hash != $_SESSION['hash']) throw new Exception($this->exceptionMessages[3]);

		return $this;

	}

	/**
	 * Авторизует пользователя
	 * @throws <i>Exception</i> Если данные для авторизации не верны выбрасывается исключение
	 * @return \Xorc\Model\UserAbstract
	 */
	public function login() {

		// Если не введено именя пользователя - показываем лог-форму
		if (!isset($_POST[$this->userDBfields['login']->name])) throw new Exception($this->exceptionMessages[0]);

		$userLogin = $this->escape($_POST[$this->userDBfields['login']->name], 'str');

		// Имя пользователя введено - ищем его в базе
		$user = $this->getObjectByParam($this->userDBtable, $this->userDBfields['login']->name, $userLogin, 'assoc');

		// Нет пользователя с таким именем
		if (!$user){
			unset($_POST[$this->userDBfields['login']->name], $_POST[$this->userDBfields['password']->name], $user, $userLogin);
			throw new Exception($this->exceptionMessages[1]);
		}

		// Проверяем пароль
		$userPasswordHash = md5($this->escape($_POST[$this->userDBfields['password']->name], 'str'));

		// Неверный пароль
		if ($userPasswordHash != $user[$this->userDBfields['password']->name]){
			unset($_POST[$this->userDBfields['login']->name], $_POST[$this->userDBfields['password']->name], $user, $pwd);
			throw new Exception($this->exceptionMessages[2]);
		}

		// Авторизация успешна
		$userID = (int) $user[$this->userDBfields['id']->name];

		$hash = $this->getSessionHash($userID);

		session_name($this->sessionName);
		session_start();
		$_SESSION['id'] = $userID;
		$_SESSION['hash'] = $hash;

		unset($userLogin, $user, $userPasswordHash, $userid, $solt, $ip, $browserData, $hash, $_POST[$this->userDBfields['login']->name], $_POST[$this->userDBfields['password']->name]);

		return $this;
	}

	/**
	 * Разлогинивает пользователя
	 * @return \Xorc\Model\UserAbstract
	 */
	public function logout() {
		session_name($this->sessionName);
		session_start();
		unset($_SESSION['id'], $_SESSION['hash']);
		setcookie ($this->sessionName, null, time() - 3600);
		return $this;
	}

	/**
	 * Вспомогательный метод - генерирует хеш для сессии
	 * @param <i>Integer</i> <b>$userID</b> ID пользователя
	 * @return <i>string</i> - хэш
	 */
	private function getSessionHash($userID) {
		$ip = md5($_SERVER['REMOTE_ADDR']);
		$browserData = md5($_SERVER['HTTP_USER_AGENT']);
		$hash = md5(md5($userID) . $ip . $browserData);
		return $hash;
	}
}