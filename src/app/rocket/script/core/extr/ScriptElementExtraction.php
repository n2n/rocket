<?php

namespace rocket\script\core\extr;

class ScriptElementExtraction {
	private $id;
	private $className;
	private $props = array();
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}
	
	public function getClassName() {
		return $this->className;
	}

	public function setClassName($className) {
		$this->className = $className;
	}
	
	public function getProps() {
		return $this->props;
	}

	public function setProps(array $props) {
		$this->props = $props;
	}
}