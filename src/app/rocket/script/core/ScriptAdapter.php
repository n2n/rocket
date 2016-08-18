<?php
namespace rocket\script\core;

use n2n\core\Module;

use rocket\script\core\Script;

abstract class ScriptAdapter implements Script {
	private $id;
	private $label;
	private $module;
	
	public function __construct($id, $label, Module $module) {
		$this->setId($id);
		$this->setLabel($label);
		$this->setModule($module);
	}
	
	public function getId() {
		return $this->id;
	}	
	
	public function setId($id) {
		$this->id = (string) $id;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function setLabel($label) {
		$this->label = (string) $label;
	}
		
	public function getModule() {
		return $this->module;
	}
	
	public function setModule(Module $module) {
		$this->module = $module;
	}
	
	public function equals($obj) {
		return $obj instanceof Script && $this->getId() === $obj->getId();
	}
}