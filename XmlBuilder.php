<?php

namespace Xorc;

use \DOMDocument				as DOMDocument,
	Xorc\Controller\Registry	as Registry;

/**
 * Класс для построения xml дерева из различных данных
 * @package Xorc framework
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com> http://rc21net.ru
 * @version 1.0
 * @copyright Copyright (c) 2013 Roman Kazakov http://rc21net.ru
 * @license GNU General Public License v2 or later http://www.gnu.org/licenses/gpl-2.0.html
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
	 * загружает в него корневой элемент
	 * @param string $rootName - имя корневого элемента
	 */
	public function __construct($rootName = 'root'){
		$this->registry =& Registry::getInstance();
		$this->xml = new DOMDocument();
		$this->xml->loadXML('<'.$rootName.'/>');
	}

	/**
	 * Добавляет к объекту DOMDocument xml файл
	 * @param string $filepath Путь к xml файлу
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
	 * @deprecated
	 * Добавляет к обекту DOMDocument данные из SQL запроса
	 * @param resource $resourse Результат SQL запроса
	 * @param string $fragmentName Имя создаваемого xml элемента
	 */
	public function appendMySQLresource($resourse, $fragmentName){
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
	 */
	public function appendNode($nodeName, $nodeValue){
		$newNode = $this->xml->createElement($nodeName, $nodeValue);
		$this->xml->appendChild($newNode);
		return $this;
	}

	/**
	 * Преобразует php-объект или массив в xml-узел
	 * @param multitype: object | array $array Добавляемый объект или массив
	 * @param string $fragmentName Имя создаваемого xml элемента
	 * @param string $keyName Имя элементов, соответсвующих элементам массива
	 * @return DOMElement
	 */
	public function objectToNode ($object, $fragmentName = null, $keyName = null) {
		$fragmentName = $fragmentName ?: (is_object($object) ? get_class($object) : 'node');
		$fragment = $this->xml->createElement($fragmentName);
		foreach ($object as $key => $value) {
			$nodeName = $keyName ?: (!is_int($key) ? $key : 'node');
			$node = (is_array($value) || is_object($value)) ? 
				$this->objectToNode($value, $nodeName) : 
				$this->xml->createElement($nodeName, $value);
			$fragment->appendChild($node);
		}
		return $fragment;
	}
	
	/**
	 * Добавляет к объекту DOMDocument php-объект или массив
	 * @param multitype: object | array $array Добавляемый объект или массив
	 * @param string $fragmentName Имя создаваемого xml элемента
	 * @param string $keyName Имя элементов, соответсвующих элементам массива
	 */
	public function appendObject($object, $fragmentName = null, $keyName = null) {
		$fragment = $this->objectToNode($object, $fragmentName, $keyName);
		$this->xml->appendChild($fragment);
		return $this;
	}

	/**
	 * Добавляет к объекту DOMDocument любой допустимый тип данных
	 * @param Array|resource|string $data
	 * @param string $secondData
	 * @param string $keyName
	 */
	public function append($data, $fragmentName = null, $keyName) {
		if (is_array($data) || is_object($data)) $this->appendObject($data, $fragmentName, $keyName);
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