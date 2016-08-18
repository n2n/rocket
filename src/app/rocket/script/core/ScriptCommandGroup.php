<?php
namespace rocket\script\core;

class ScriptCommandGroup {
	private $name;
	private $scriptCommandClasses = array();
	
	public function __construct($name = null) {
		$this->name = $name;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function addCommandClass(\ReflectionClass $scriptCommandClass) {
		$this->scriptCommandClasses[$scriptCommandClass->getName()] = $scriptCommandClass;
	}
	
	public function getCommandClasses() {
		return $this->scriptCommandClasses;
	}
	
	public function removeAllScriptCommandClasses() {
		$this->scriptCommandClasses = array();
	}
}