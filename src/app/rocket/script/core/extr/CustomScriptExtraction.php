<?php

namespace rocket\script\core\extr;

use rocket\script\core\ScriptManager;
use n2n\reflection\ReflectionUtils;
use n2n\core\TypeNotFoundException;
use rocket\script\core\CustomScript;
class CustomScriptExtraction extends ScriptExtraction {
	private $controllerClassName;
	
	public function getControllerClassName() {
		return $this->controllerClassName;
	}

	public function setControllerClassName($customControllerClassName) {
		$this->controllerClassName = $customControllerClassName;
	}
	
	public function createScript(ScriptManager $scriptManager) {
		$constrollerClass = null;
		try {
			$controllerClass = ReflectionUtils::createReflectionClass(
					$this->getControllerClassName());
			if (!$controllerClass->implementsInterface('n2n\http\Controller')) {
				throw ScriptManager::createInvalidScriptConfigurationException($this->getId(), null, 
						$constrollerClass->getName() . ' must implement n2n\http\Controller');
			}
		} catch (TypeNotFoundException $e) {
			throw ScriptManager::createInvalidScriptConfigurationException($this->getId(), $e);
		}
	
		return new CustomScript($this->getId(), $this->getLabel(), $this->getModule(), $controllerClass);
	}
	
	public static function createFromCustomScript(CustomScript $script) {
		$extraction = new CustomScriptExtraction($script->getId(), $script->getModule());
		$extraction->setLabel($script->getLabel());
		$extraction->setControllerClassName($script->getControllerClass()->getName());
		return $extraction;
	}
}