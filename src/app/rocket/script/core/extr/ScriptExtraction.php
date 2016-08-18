<?php
namespace rocket\script\core\extr;

use n2n\core\Module;
use rocket\script\core\ScriptManager;

abstract class ScriptExtraction {
	private $id;
	private $module;
	private $label;
		
	public function __construct($id, Module $module) {
		$this->id = $id;
		$this->module = $module;
	}
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getModule() {
		return $this->module;
	}

	public function setModule($module) {
		$this->module = $module;
	}

	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
	}
	
	public abstract function createScript(ScriptManager $scriptManager);
}