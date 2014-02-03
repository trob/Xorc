<?php

namespace Xorc;

/**
 * Класс для построения xml дерева из различных данных
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com>
 * @version 2.0
 */
class XmlBuilder {

	protected $registry;

	/**
	 * Xml дерево
	 * @var DOMDocument
	 */
	protected $xml;

	/**
	 * Конструктор класса:
	 * cоздает оъбект DOMDocument и
	 * загружает в него корневой элемент <root/>
	 * @return object Обект класса
	 */
	public function __construct(){
		$this->registry =& Registry::getInstance();
		$this->xml = new DOMDocument();
		$this->xml->loadXML('<root/>');
	}

	/**
	 * Добавляет к объекту DOMDocument xml файл
	 * @param string $filepath Путь к xml файлу
	 * @return void
	 */
	public function appendXMLfile($filepath){
		$child = new DOMDocument();
		$child->load($filepath);
		$child = $this->xml->importNode($child->documentElement, true);
		$this->xml->appendChild($child);
		unset($child);
		return $this;
	}

	/**
	 * Добавляет к объекту DOMDocument xml строку
	 * @param string $xmlString XML строка
	 * @return void
	 */
	public function appendXMLstring($xmlString){
		$child = new DOMDocument();
		$child->loadXML($xmlString);
		$child = $this->xml->importNode($child->documentElement, true);
		$this->xml->appendChild($child);
		unset($child);
		return $this;
	}

	/**
	 * Добавляет к обекту DOMDocument данные из SQL запроса
	 * @param resource $resourse Результат SQL запроса
	 * @param string $fragmentName Имя создаваемого xml элемента
	 * @return void
	 */
	public function appendMySQLresource($resourse, $fragmentName){
		var_dump($resourse);
		$fragment = $this->xml->createElement($fragmentName);
		for ($i=0; $i<mysql_num_rows($resourse); $i++){
			$line = $this->xml->createElement('line');
			$f=mysql_fetch_array($resourse, MYSQL_ASSOC);
			foreach ($f as $key=>$value){
				$field = $this->xml->createElement($key, $value);
				$line->appendChild($field);
			}
			$fragment->appendChild($line);
		}
		$this->xml->appendChild($fragment);
		return $this;
	}

	/**
	 * Добавляет к объекту DOMDocument новый узел (node)
	 * @param string $nodeName Имя элемента
	 * @param string $nodeValue Значение элемента
	 * @return void
	 */
	public function appendNode($nodeName, $nodeValue){
		$newNode = $this->xml->createElement($nodeName, $nodeValue);
		$this->xml->appendChild($newNode);
		return $this;
	}

	/**
	 * Добавляет к объекту DOMDocument массив
	 * @param Array $array Добавляемый массив
	 * @param string $fragmentName Имя создаваемого xml элемента
	 * @param string $keyName Имя элементов, соответсвующих элементам массива
	 */
	public function appendArray($array, $fragmentName, $keyName = null) {
		$fragment = $this->xml->createElement($fragmentName);
		foreach ($array as $key => $value) {
			$nodeName = ($keyName) ? $keyName : $key;
			$node = $this->xml->createElement($nodeName, $value);
			$fragment->appendChild($node);
		}
		$this->xml->appendChild($fragment);
		return $this;
	}

	/**
	 * Добавляет к объекту DOMDocument php-объект
	 * @param object $object Добавляемый объект
	 * @param string $fragmentName Имя создаваемого xml элемента
	 * @param string $keyName Имя элементов, соответсвующих элементам массива
	 */
	public function appendObject($object, $fragmentName, $keyName = null) {
		return $this->appendArray($object, $fragmentName, $keyName);
	}

	/**
	 * Добавляет к объекту любой допустимый тип данных
	 * @param Array|resource|string $data
	 * @param string $secondData
	 * @param string $keyName
	 */
	public function append($data, $fragmentName = null, $keyName) {
		if (is_array($data)) $this->appendArray($data, $fragmentName, $keyName);
		else if (is_resource($data)) $this->appendMySQLresource($data, $fragmentName);
		else if ($fragmentName && is_string($data)) $this->appendNode($fragmentName, $data);
		else if (is_string($data) && file_exists($data)) $this->appendXMLfile($data);
		else if (is_string($data)) $this->appendXMLstring($data);
		return $this;
	}

	/**
	 * Возвращает готовый (или промежуточный) xml
	 * @return DOMDocumen Объект DOMDocumen
	 */
	public function getXml(){
		return $this->xml;
	}

}