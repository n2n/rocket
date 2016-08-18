<?php
namespace rocket\script\entity\field\impl;

use rocket\script\entity\field\EntityPropertyScriptField;
use n2n\persistence\orm\property\EntityProperty;
use rocket\script\core\CompatibilityTest;
use rocket\script\entity\field\AssignableScriptField;

abstract class EntityPropertyScriptFieldAdapter extends IndependentScriptFieldAdapter implements EntityPropertyScriptField, AssignableScriptField {
	protected $entityProperty;
	
	public function getIdBase() {
		return $this->entityProperty->getName();
	}
	
	public function setEntityProperty(EntityProperty $entityProperty) {
		$this->entityProperty = $entityProperty;
	}
	
	public function getEntityProperty() {
		return $this->entityProperty;
	}
	
	public function checkCompatibility(CompatibilityTest $compatibilityTest) {
		if (!$this->isCompatibleWith($compatibilityTest->getEntityProperty())) {
			$compatibilityTest->entityPropertyTestFailed();
			return;
		}
	}
	
	public abstract function isCompatibleWith(EntityProperty $entityProperty);
}