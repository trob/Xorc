<?php

namespace Xorc\View;

use \DOMDocument	as DOMDocument,
	\XSLTProcessor	as XSLTProcessor,
	Xorc\XmlBuilder	as XmlBuilder;

/**
 * View использующий xsl в качестве шаблонов
 * @package Xorc framework
 * @author Roman Kazakov (a.k.a. RC21) <rc21mail@gmail.com> http://rc21net.ru
 * @version 1.0
 * @copyright Copyright (c) 2013 Roman Kazakov http://rc21net.ru
 * @license GNU General Public License v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
class XslView  extends XmlBuilder {

	/**
	* Объект DOMDocument для xsl файла
	* @var DOMDocument
	*/
	private $xsldoc;

	/**
	 * Объект XSLTProcessor
	 * @var XSLTProcessor
	 */
	private $xslt;

	private $xslFile;

	/**
	 * HTML-строка на выходе
	 * @var string
	 */
	private $html;

	/**
	* Перегруженный конструктор класса:
	* cоздает оъбект DOMDocument, загружает в него корневой элемент <root/>
	* и создает XSLT процессор
	*/
	public function __construct($xslFile = null){
		parent::__construct();
		$this->xsldoc = new DOMDocument();
		$this->xslt = new XSLTProcessor();
		$this->xslt->registerPHPFunctions();
		$this->xslFile = $xslFile ? $xslFile : $this->setXslFile();
	}

	/**
	 * Вычисляет из имени контроллера и экшена имя xsl файла
	 * @return <i>string</i> имя xsl файла
	 */
	private function setXslFile() {

		$path = $this->registry['path']['views'];

		$controller = strtolower(substr($this->registry['controller'], 0, strlen($this->registry['controller']) - 10));

		$action =  ucfirst(substr($this->registry['action'], 0, strlen($this->registry['action']) - 6));
		$action = ($action == 'Index') ? '' : $action;

		$ext = '.xsl';

		return $path . $controller . $action . $ext;
	}

	/**
	* Устанавливает xsl параметры
	* @param <i>array</i> <b>$params</b> Массив xsl параметров
	*/
	public function setParams($params){
		foreach($params as $par => $value){
			$this->xslt->setParameter('', $par, $value);
		}
		return $this;
	}

	/**
	* Осуществляет xsl-преобразование xml
	* @param <i>object</i> <b>$data_xml</b> - Данные в формате xml (DOMDocument)
	*/
	public function transform(){
		$this->xsldoc->load($this->xslFile);
		$this->xslt->importStyleSheet($this->xsldoc);
		$this->html = $this->xslt->transformToXML($this->xml);
		return $this;
	}

	/**
	 * Выводит html страницу
	 * @return XslView
	 */
	public function show() {
		echo $this->html;
		return $this;
	}
}