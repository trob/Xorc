<?php

namespace Xorc;

/**
 * Класс для работы с базой данных MySQLi
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com>
 * @version 2.0
 */
class MySqli {

	/**
	 * Реестр
	 * @var <i>array</i>
	 */
	public $registry;

	/**
	 * Иденитфикатор соединения
	 * @var <i>mysqli</i>
	 */
	protected $dbConnect;

	/**
	 * Результат запроса к БД
	 * @var <i>mysqli_result</i>
	 */
	protected $result;

	/**
	 * Cвойство-объект, в который метод getObject
	 * сохраняет свойства (поля) полученного из БД объекта
	 * @var <i>object</i>
	 */
	protected $object;

	/**
	 * Конструктор класса
	 * выполняет подключение к серверу
	 * и выбирает БД
	 */
	public function __construct(){

		$this->registry =& Registry::getInstance();

		$this->dbConnect = new mysqli($this->registry['db']['server'], $this->registry['db']['user'], $this->registry['db']['password'], $this->registry['db']['db']);
		if ($this->dbConnect->connect_errno){
			echo 'Ошибка '.$this->dbConnect->connect_errno;
			exit;
		}
	}

	/**
	 * Выполняет запрос к базе данных
	 * (при этом из памяти удаляются результаты предыдущего запроса);
	 * результат запроса сохраняется в свойсвтве объекта $result.
	 * @param <i>string</i> <b>$query</b> Cтрока с SQL запросом.
	 * @return Ambigous <<i>resource</i>, <i>boolean</i>> Если запрос выполнен возвращается результат запроса, если не выполнен - false.
	 */
	public function &query($query){
		if(is_resource($this->result)) $this->result->free();
		$this->result = $this->dbConnect->query($query);
		if ($this->result) return $this->result;
		else return false;
	}

	/**
	 * Выполняет несколько запросов к БД.
	 * @param <i>array</i> <b>$queries</b> Массив строк с SQL запросами.
	 * @return <i>void</i>
	 */
	public function queries($queries){
		foreach($queries as $query){
			$this->query($query);
		}
	}

	/**
	 * Выполняет запрос к базе данных (с помощью метода query())
	 * и если результат запроса содержит хотя бы одну строку
	 * метод записывает значения этой строки в объект - $properties.
	 * @param <i>string</i> <b>$query</b> Cтрока с SQL запросом.
	 * @param <i>string</i> <b>$type</b> Возвращаемый тип: <code>object (<i>default</i>) | assoc | num</code>
	 * @return Ambigous <<i>object</i>, <i>boolean</i>> Если запрос выполнен и результат содержит хотя бы одну строку
	 * метод возвращает объект, в противном случае возвращается false.
	 */
	public function getObject($query, $type = 'object'){
		$this->query($query);
		if(is_resource($this->result) && $this->result->num_rows){
			switch ($type) {
				case 'object':
					$this->object = $this->result->fetch_object();
				break;
				case 'assoc':
					$this->object = $this->result->fetch_assoc();
				break;
				case 'num':
					$this->object = $this->result->fetch_array();
				break;
			}
			$this->result->free();
			return $this->object;
		}else{
			return false;
		}
	}

	/**
	 * Выбирает из таблицы $table первую строку, в которой поле $field равно $value, и возвращает эту строку в виде объекта.
	 * @param <i>string</i> <b>$table</b> Имя таблицы
	 * @param <i>string</i> <b>$field</b> Имя поля
	 * @param <i>string</i> <b>$value</b> Значение поля'
	 * @param <i>string</i> <b>$type</b> Возвращаемый тип: <code>object (<i>default</i>) | assoc | num</code>
	 * @return Ambigous <<i>object</i>, <i>boolean</i>> Если запрос выполнен и результат содержит хотя бы одну строку
	 * метод возвращает объект, в противном случае возвращается false.
	 */
	public function getObjectByParam($table, $field, $value, $type = 'object') {
		$query = "SELECT * FROM " . $table . " WHERE " . $field . "='" . $value ."'";
		return $this->getObject($query, $type);
	}

	/**
	 * Добавляет в таблицу $table значения из массива $values.
	 * Порядок значений в массиве $values должен соответсвовать порядку столбцов в таблице $table.
	 * @param <i>string</i> <b>$table</b> Имя таблицы
	 * @param <i>array</i> <b>$values</b> Массив вставляемых значений
	 * @return Ambigous <<i>resource</i>, <i>boolean</i>>
	 */
	public function insertByPos($table, $values) {
		$length = count($values);

		$query = "INSERT into " . $table . " VALUES(";
		for ($i=0; $i<$length; $i++) {
			$value = $values[$i] ? "'" . $values[$i] . "'" : "NULL";
			$query .= $value;
			if ($i+1 != $length) $query .= ", ";
		}
		$query .= ")";

		return $this->query($query);
	}

	/**
	 * Добавляет в таблицу $table значения из массива ассоциативного $values.
	 * Названия ключей массива $values должны соответствовать названиям столбцов в таблице $table.
	 * @param <i>string</i> <b>$table</b> Имя таблицы
	 * @param <i>array</i> <b>$values</b> Массив вставляемых значений
	 * @return Ambigous <<i>resource</i>, <i>boolean</i>>
	 */
	public function insertByName($table, $values) {
		$length = count($values);

		$query_1 = "INSERT into " . $table . " (";
		$query_2 = " VALUES(";
		$i = 0;
		foreach ($values as $key=>$value) {
			$i++;
			$query_1 .= $key;
			$query_2 .= "'" . $value . "'";
			if ($i != $length) {
				$query_1 .= ", ";
				$query_2 .= ", ";
			}
		}
		$query_1 .= ") ";
		$query_2 .= ")";
		$query = $query_1 . $query_2;

		return $this->query($query);
	}

	/**
	 * Обновляет в таблице строки, у которых поле $param равно $value
	 * @param <i>string</i> <b>$table</b> Имя таблицы
	 * @param <i>array</i> <b>$fields</b> Массив вставляемых значений
	 * @param <i>string</i> <b>$param</b> Имя поля
	 * @param <i>string</i> <b>$value</b> Значение поля
	 * @return Ambigous <<i>resource</i>, <i>boolean</i>>
	 */
	public function update($table, $fields, $param, $value) {
		$query = "UPDATE " . $table . " SET ";
		$filedsLength = count($fields);
		$paramLength = count($params);
		if ($filedsLength == 1) $query .= key($fields) . " = '" . $fields[0] . "'";
		else {
			$i = 0;
			foreach ($fields as $key=>$value) {
				$i++;
				$query .= $key . " = '" . $value . "'";
				if ($i != $length) {
					$query .= ", ";
				}
			}
		}

		$query .= " WHERE " . $param . "='" . $value ."'";

		return $this->query($query);
	}

	/**
	 * Удаляет из таблицы строки, у которых поле $field равно $value
	 * @param <i>string</i> <b>$table</b> Имя таблицы
	 * @param <i>string</i> <b>$field</b> Имя поля
	 * @param <i>string</i> <b>$value</b> Значение поля
	 * @return Ambigous <<i>resource</i>, <i>boolean</i>>
	 */
	public function delete($table, $field, $value) {
		$query = "DELETE FROM " . $table . " WHERE " . $field . "='" .$value ."'";
		return $this->query($query);
	}

	/**
	 * Защищает Post и Get переменные от SQL инъекций
	 * @param <i>string</i> <b>$variable</b> Post или Get переменная
	 * @param <i>string</i> <b>$type</b> Тип переменной
	 * @return Ambigous <<i>string</i>, <i>integer</i>>
	 */
	public function escape($variable, $type) {
		switch ($type) {
			case 'str':
				$variable = $this->dbConnect->real_escape_string($variable);
			break;

			case 'int':
				$variable = (int) $variable;
			break;
		}

		return $variable;
	}

	/**
	 * Выполняет запрос с вызовом хранимой процедуры
	 * @param <i>string</i> <b>$procedureName</b> Имя вызываемой хранимой процедуры.
	 * @param <i>array</i> <b>$procedureParams</b> Массив параметров хранимой процедуры.
	 * @return Ambigous <<i>resource</i>, <i>boolean</i>> Если запрос выполнен возвращается результат запроса, если не выполнен - false.
	 */
	public function callProcedure($procedureName, $procedureParams){
		$query = 'call '.$procedureName;
		if($procedureParams){
			$query .= '(';
			for($i = 0; $i < count($procedureParams); $i++){
				$query .= '"'.$procedureParams[$i].'"';
				if($i < count($procedureParams) - 1) $query .= ', ';
			}
			$query .= ')';
		}
		return $this->query($query);
	}

	/**
	 * Деструктор класса очищает память от результатов запроса
	 * и закрывает соединение с БД
	 */
	public function __destruct(){
		if(is_resource($this->result)) $this->result->free();
		// FIXME Закрытие соединения может вызвать ошибку, если другие объекты попытаются использовать это соединение
		//mysql_close($this->dbConnect);
	}
}
