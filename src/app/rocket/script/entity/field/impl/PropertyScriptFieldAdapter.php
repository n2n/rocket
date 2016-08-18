<?php

namespace rocket\script\entity\field\impl;

use rocket\script\entity\field\PropertyScriptField;
use n2n\reflection\property\PropertyAccessProxy;
use n2n\reflection\property\TypeConstraints;
use n2n\reflection\property\ConstraintsConflictException;
use rocket\script\core\CompatibilityTest;
use rocket\script\core\SetupProcess;

abstract class PropertyScriptFieldAdapter extends EntityPropertyScriptFieldAdapter implements PropertyScriptField {
	protected $propertyAccessProxy;
	
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\PropertyScriptField::getPropertyAccessProxy()
	 */
	public function getPropertyAccessProxy() {
		return $this->propertyAccessProxy;
	}
	
	public function setPropertyAccessProxy(PropertyAccessProxy $propertyAccessProxy) {
		$this->propertyAccessProxy = $propertyAccessProxy;
	}
	
// 	public function isPropertyCompatible(PropertyAccessProxy $propertyAccessProxy) {
// 		$constraints = $propertyAccessProxy->getConstraints();
// 		return $constraints === null || $constraints->arePassableBy($this->entityProperty->getAccessProxy()->getConstraints());
// 	}
	
	public function getPropertyName() {
		return $this->propertyAccessProxy->getPropertyName();
	}
	
	public function setup(SetupProcess $setupProcess) {
		parent::setup($setupProcess);
		try {
			$entityPropertyConstraints = $this->entityProperty->getAccessProxy()->getConstraints();
			$currentPropertyConstraints = $this->propertyAccessProxy->getConstraints();
			
			$propertyConstraints = new TypeConstraints($entityPropertyConstraints->getParamClass(),
					$entityPropertyConstraints->isArray(), $entityPropertyConstraints->isArrayObject(),
					!isset($currentPropertyConstraints) || $currentPropertyConstraints->allowsNull());
	
			$this->propertyAccessProxy->setConstraints($propertyConstraints);
		} catch (ConstraintsConflictException $e) {
			$setupProcess->failedE($this, $e);
		}
	}
	
	public function checkCompatibility(CompatibilityTest $compatibilityTest) {
		parent::checkCompatibility($compatibilityTest);
		
		if ($compatibilityTest->hasFailed()) return;
		
		$propertyConstraints = $compatibilityTest->getPropertyAccessProxy()->getConstraints();
		$entityPropertyContraints = $compatibilityTest->getEntityProperty()->getAccessProxy()->getConstraints();
		if ($propertyConstraints !== null && !$propertyConstraints->arePassableBy($entityPropertyContraints, true)) {
			$compatibilityTest->propertyTestFailed('ScriptField can not pass Type ' . $entityPropertyContraints->__toString() 
					. ' to property due to incompatible TypeConstraints ' . $propertyConstraints->__toString());
		}
	}
}