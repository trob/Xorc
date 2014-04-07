<?php

namespace Xorc\Model;

require_once 'MySql.php';

/**
 * Класс аутентификации
 * @deprecated Use instead Xorc\Model\UserAbstract
 * @package Xorc framework
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com> http://rc21net.ru
 * @version 1.0
 * @copyright Copyright (c) 2013 Roman Kazakov http://rc21net.ru
 * @license GNU General Public License v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
class Auth extends MySql {

	private $cookieID;

	private $cookieHASH;

	private $userXMLpath;

	function __construct($cookieName, $userXMLpath){
		$this->cookieID = $cookieName.'_id';
		$this->cookieHASH = $cookieName.'_hash';
		$this->userXMLpath = $userXMLpath;
	}

	function check(){
		// Куки не установлены
		if (!isset($_COOKIE[$this->cookieID]) or !isset($_COOKIE[$this->cookieHASH])) {
			return false;
		// Куки установлены
		}else{
			$users = simplexml_load_file($this->userXMLpath);
			$user = $users->xpath('/users/user[@id="' . $_COOKIE[$this->cookieID] . '"]');
			// В базе нет юзера с таким id'ом
			if (!$user){
				setcookie($this->cookieID, null, time()+1);
				setcookie($this->cookieHASH, null, time()+1);
				unset($users, $user);
				return false;
			}else{
				// Хеш и ip не соответствуют значениям в базе
				if ($_COOKIE[$this->cookieHASH] != $user['0']->hash or $_SERVER['REMOTE_ADDR'] != $user['0']->ip) {
					settype($_COOKIE[$this->cookieID], "integer");
					$users->user[$_COOKIE[$this->cookieID]]->hash = null;
					$users->user[$_COOKIE[$this->cookieID]]->ip = null;
					$users->asXML($this->userXMLpath);
					setcookie($this->cookieID, null, time()+1);
					setcookie($this->cookieHASH, null, time()+1);
					unset($users, $user);
					return false;
				}else{
					// Проверка завершена успешно
					unset($users, $user);
					return true;
				}
			}
		}
	}

	function login(){

		//Если не введено именя пользователя - показываем лог-форму
		if (!isset($_POST['login'])) return false;

		// Имя пользователя введено - ищем его в базе
		$user = $this->getObjectByParam('cm_user_login', 'login', $_POST['login']);

		//Нет пользователя с таким именем
		if (!$user){
			unset($_POST['login'], $_POST['password'], $user);
			return false;
		}

		// Проверяем пароль
		$pwd = md5($_POST['password']);

		//Пароль не верный
		if ($pwd != $user->pw_hash){
			unset($_POST['login'], $_POST['password'], $user, $pwd);
			return false;

		}

		// Авторизация успешна
		$userid = $user->id;
		settype($userid, "integer");
		$solt = md5($this->generateCode(10));
		$ip = md5($_SERVER['REMOTE_ADDR']);
		$browserData = md5($str);
		session_start();
		$_SESSION['id'] = $userid;

		$users->user[$userid]->hash = $hash;
		$users->user[$userid]->ip = $_SERVER['REMOTE_ADDR'];
		$users->asXML($this->userXMLpath);
		setcookie($this->cookieID, $userid, time()+60*60*6);
		setcookie($this->cookieHASH, $hash, time()+60*60*6);
		unset($users, $user, $pwd, $userid, $hash, $_POST['login'], $_POST['password']);
		return true;

	}

	function logout(){
		// Подключаемся к базе и находим пользователя
		$users = simplexml_load_file($this->userXMLpath);
		$user = $users->xpath('/users/user[@id="' . $_COOKIE[$this->cookieID] . '"]');
		// Удаляем информацию в базе
		settype($_COOKIE[$this->cookieID], "integer");
		$users->user[$_COOKIE[$this->cookieID]]->hash = null;
		$users->user[$_COOKIE[$this->cookieID]]->ip = null;
		$users->asXML($this->userXMLpath);
		unset($users, $user);
		// Удаляем куки
		setcookie($this->cookieID, null, time()+1);
		setcookie(cookieHASH, null, time()+1);
		// Сообщаем об успехе
		return true;
	}

	// Функция для генерации случайной строки
	private function generateCode($length=6) {
	    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
	    $code = "";
	    $clen = strlen($chars) - 1;
	    while (strlen($code) < $length) {
	            $code .= $chars[mt_rand(0,$clen)];
	    }
	    return $code;
		unset($length, $chars, $clen, $code);
	}
}