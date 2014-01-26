<?php

/**
 * Класс аутентификации c использованием XML
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com>
 * @version 2.0 beta
 */
class AuthXML {

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
		$users = simplexml_load_file($this->userXMLpath);
		$user = $users->xpath('/users/user[name="' . $_POST['login'] . '"]');
		//Нет пользователя с таким именем
		if (!$user){
			unset($_POST['login'], $_POST['password'], $users, $user);
			return false;
		}else{
			// Проверяем пароль
			$pwd = md5($_POST['password']);
			//Пароль не верный
			if ($pwd != $user['0']->password){
				unset($_POST['login'], $_POST['password'], $users, $user, $pwd);
				return false;
			// Авторизация успешна
			}else{
				$userid = $user['0']['id'];
				settype($userid, "integer");
				$hash = md5($this->generateCode(10));
				$users->user[$userid]->hash = $hash;
				$users->user[$userid]->ip = $_SERVER['REMOTE_ADDR'];
				$users->asXML($this->userXMLpath);
				setcookie($this->cookieID, $userid, time()+60*60*6);
				setcookie($this->cookieHASH, $hash, time()+60*60*6);
				unset($users, $user, $pwd, $userid, $hash, $_POST['login'], $_POST['password']);
				return true;
			}
		}
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