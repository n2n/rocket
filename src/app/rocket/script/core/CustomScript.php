<?php
namespace rocket\script\core;

use n2n\core\Module;
use n2n\reflection\ReflectionContext;
use n2n\core\N2nContext;
use rocket\script\core\extr\CustomScriptExtraction;

class CustomScript extends ScriptAdapter {
	private $controllerClass;
	
	public function __construct($id, $label, Module $module, \ReflectionClass $controllerClass) {
		parent::__construct($id, $label, $module);
		$this->controllerClass = $controllerClass;
	}
	
	public function getControllerClass() {
		return $this->controllerClass;
	}
		
	/* (non-PHPdoc)
	 * @see \rocket\script\core\Script::createController()
	 */
	public function createController() {
		return ReflectionContext::createObject($this->controllerClass);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\core\Script::getOverviewPathExt()
	 */
	public function getOverviewPathExt() {
		return null;
	}
	
	public function hasSecurityOptions() {
		return false;
	}
	
	public function getPrivilegeOptions(N2nContext $n2nContext) {}
	
	public function createAccessOptionCollection(N2nContext $n2nContext) {}
	
	public function createRestrictionSelectorItems(N2nContext $n2nContext) {}
	
	public function toScriptExtraction() {
		$extraction = new CustomScriptExtraction($this->getId(), $this->getModule());
		$extraction->setControllerClassName($this->controllerClass->getName());
		$extraction->setLabel($this->getLabel());
		return $extraction;
	}
}