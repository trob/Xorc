<?php

namespace Xorc\Model;

use Xorc\Model\DataBase\MySqli 	as MySqli,
	Xorc\Controller\Mail 		as Mail,
	\Exception 					as Exception;

/**
 * Класс для работы с пользователями
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com>
 *
 */
 abstract class UserAbstract extends MySqli {

 	/**
 	 * Название таблицы пользователей в БД
 	 * @var string
 	 */
	protected $userDBtable;

	/**
	 * Название поля с id
	 * @var string
	 */
	protected $idField = 'id';

	/**
	 * Название поля с логином
	 * @var string
	 */
	protected $loginField = 'login';

	/**
	 * Название поля с паролем (хеш пароля)
	 * @var string
	 */
	protected $passwordField = 'password';

	/**
	 * Название сессии
	 * @var string
	 */
	protected $sessionName = 'Xorc_session';

	/**
	 * Минимальная длина логина
	 * @var integer
	 */
	protected $loginMinLength = 3;

	/**
	 * Максимальная длина логина
	 * @var integer
	 */
	protected $loginMaxLength = 50;

	/**
	 * Минимальная длина пароля
	 * @var integer
	 */
	protected $passwordMinLength = 5;

	/**
	 * Максимальная длина пароля
	 * @var integer
	 */
	protected $passwordMaxLength = 50;

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
	 * Массив сообщений об ошибках
	 * @var array
	 */
	protected $exceptionMessages;

	/**
	 * Конструктор класса
	 */
	public function __construct() {
		
		// Выполняем родительский конструктор: подключение к БД и т.д.
		parent::__construct();

		// Заполняем массив сообщений об ошибках
		$this->exceptionMessages = array(
			0 => 'You need to fill all fields.',
			1 => 'No user with such name.',
			2 => 'Wrong password.',
			3 => 'Access denied.',
			4 => 'User with such name already exists. Choose another name',
			5 => 'Login length must be from ' . $this->loginMinLength . ' till ' . $this->loginMaxLength . ' symbols.',
			6 => 'Password length must be from ' . $this->passwordMinLength . ' till ' . $this->passwordMaxLength . ' symbols.',
			7 => 'Registration error. Try again.'
		);

		// выполняем дополнительные действия из наследуемых классов
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
		//TODO: реализация
		return $this;
	}

	/**
	 * Добавить пользователя в базу
	 * @param array $prop массив с логином и паролем
	 * @throws Exception
	 * @return \Xorc\Model\UserAbstract
	 */
	public function add($prop) {

		// Проверяем логин и пароль на пустоту
		if (empty($prop['login']) || empty($prop['password'])) throw new Exception($this->exceptionMessages[0]);

		// Ограничения длинны логина и пароля
		if (strlen($prop['login']) < $this->loginMinLength || strlen($prop['login']) > $this->loginMaxLength) throw new Exception($this->exceptionMessages[5]);
		if (strlen($prop['password']) < $this->passwordMinLength || strlen($prop['password']) > $this->passwordMaxLength) throw new Exception($this->exceptionMessages[6]);

		// Экранируем логин и пароль
		$this->login = $this->escape($prop['login'], 'str');
		$this->passwordHash = md5($this->escape($prop['password'], 'str'));

		// Проверяем есть ли пользователь с таким логином
		if ($this->getObjectByParam($this->userDBtable, $this->loginField, $this->login)) throw new Exception($this->exceptionMessages[4]);

		// Добавляем в базу
		if (!$this->insertByPos($this->userDBtable, array(null, $this->login, $this->passwordHash))) throw new Exception($this->exceptionMessages[7]);

		return $this;
	}

	/**
	 * Aктивация пользователя
	 * @return \Xorc\Model\UserAbstract
	 */
	public function activate() {
		
		return $this;
	}
	
	/**
	 * Регистрация пользователя (добавление в базу и отправка email)
	 * @param array $prop массив с логином и паролем
	 * @return \Xorc\Model\UserAbstract
	 */
	public function register($prop) {
		$this->add($prop);
		
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
		if (!isset($_POST[$this->loginField])) throw new Exception($this->exceptionMessages[0]);

		$userLogin = $this->escape($_POST[$this->loginField], 'str');

		// Имя пользователя введено - ищем его в базе
		$user = $this->getObjectByParam($this->userDBtable, $this->loginField, $userLogin, 'assoc');

		// Нет пользователя с таким именем
		if (!$user){
			unset($_POST[$this->loginField], $_POST[$this->passwordField], $user, $userLogin);
			throw new Exception($this->exceptionMessages[1]);
		}

		// Проверяем пароль
		$userPasswordHash = md5($this->escape($_POST[$this->passwordField], 'str'));

		// Неверный пароль
		if ($userPasswordHash != $user[$this->passwordField]){
			unset($_POST[$this->loginField], $_POST[$this->passwordField], $user, $pwd);
			throw new Exception($this->exceptionMessages[2]);
		}

		// Авторизация успешна
		$userID = (int) $user[$this->idField];

		$hash = $this->getSessionHash($userID);

		session_name($this->sessionName);
		session_start();
		$_SESSION['id'] = $userID;
		$_SESSION['hash'] = $hash;

		unset($userLogin, $user, $userPasswordHash, $userid, $solt, $ip, $browserData, $hash, $_POST[$this->loginField], $_POST[$this->passwordField]);

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