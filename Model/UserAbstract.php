<?php

namespace Xorc\Model;

require_once 'MySql.php';

 abstract class UserAbstract extends MySql {

	protected $userDBtable;

	protected $idField = 'id';

	protected $loginField = 'login';

	protected $passwordField = 'password';

	protected $sessionName = 'Xorc_session';

	protected $loginMinLength = 3;

	protected $loginMaxLength = 50;

	protected $passwordMinLength = 5;

	protected $passwordMaxLength = 50;

	protected $id;

	protected $login;

	protected $passwordHash;

	/**
	 * Массив сообщений об ошибках
	 * @var array
	 */
	protected $exceptionMessages;

	public function __construct() {
		parent::__construct();

		$this->exceptionMessages = array(
			0 => 'Необходимо заполнить все поля.',
			1 => 'Нет пользователя с таким именем.',
			2 => 'Неверный пароль.',
			3 => 'Доступ запрещен.',
			4 => 'Пользователь с таким именем уже существует. Выбирите другой логин.',
			5 => 'Длинна логина от ' . $this->loginMinLength . ' до ' . $this->loginMaxLength . ' символов.',
			6 => 'Длинна проля от ' . $this->passwordMinLength . ' до ' . $this->passwordMaxLength . ' символов.',
			7 => 'При регистрации возникла ошибка. Попробуйте еще раз.'
		);

		$this->initExtendProperties();
	}

	/**
	 * Необходимо задать:
	 * $this->userDBtable - имя таблицы с пользователями
	 */
	abstract protected function initExtendProperties();

	public function get($id) {

	}

	public function registry($prop) {

		//проверяем логин и пароль на пустоту
		if (empty($prop['login']) || empty($prop['password'])) throw new Exception($this->exceptionMessages[0]);

		// ограничения длинны логина и пароля
		if (strlen($prop['login']) < $this->loginMinLength || strlen($prop['login']) > $this->loginMaxLength) throw new Exception($this->exceptionMessages[5]);
		if (strlen($prop['password']) < $this->passwordMinLength || strlen($prop['password']) > $this->passwordMaxLength) throw new Exception($this->exceptionMessages[6]);

		//экранируем логин и пароль
		$this->login = $this->escape($prop['login'], 'str');
		$this->passwordHash = md5($this->escape($prop['password'], 'str'));

		//проверяем есть ли пользователь с таким логином
		if ($this->getObjectByParam($this->userDBtable, $this->loginField, $this->login)) throw new Exception($this->exceptionMessages[4]);

		// добавляем в базу
		if (!$this->insertByPos($this->userDBtable, array(null, $this->login, $this->passwordHash))) throw new Exception($this->exceptionMessages[7]);

		return $this;
	}

	public function update() {

	}

	public function delete() {

	}

	/**
	 * Проверяет авторизацию пользователя
	 * @throws <i>Exception</i> Если пользователь не авторизован выбрасывается исключение
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
	 */
	public function login() {

		//Если не введено именя пользователя - показываем лог-форму
		if (!isset($_POST[$this->loginField])) throw new Exception($this->exceptionMessages[0]);

		$userLogin = $this->escape($_POST[$this->loginField], 'str');

		// Имя пользователя введено - ищем его в базе
		$user = $this->getObjectByParam($this->userDBtable, $this->loginField, $userLogin, 'assoc');

		//Нет пользователя с таким именем
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