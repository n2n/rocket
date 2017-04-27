<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\spec\ei\component\field\impl\adapter;

use n2n\reflection\property\AccessProxy;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;

abstract class ConfObjectPropertyEiFieldAdapter extends ConfEntityPropertyEiFieldAdapter implements ConfObjectPropertyEiField {
	protected $objectPropertyAccessProxy;
	protected $objectPropertyRequired = true;
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\field\ObjectPropertyEiField::getPropertyAccessProxy()
	 */
	public function getObjectPropertyAccessProxy(bool $required = false) {
		if ($this->entityProperty === null && $required) {
			throw new IllegalStateException('No EntityProperty assigned to ' . $this);
		}
		
		return $this->objectPropertyAccessProxy;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $objectPropertyAccessProxy = null) {
		if ($objectPropertyAccessProxy === null && $this->objectPropertyRequired) {
			throw new \InvalidArgumentException($this . ' requires an object property AccessProxy.');
		}
		
		$this->objectPropertyAccessProxy = $objectPropertyAccessProxy;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\impl\adapter\ConfEntityPropertyEiFieldAdapter::createEiConfigurator()
	 */
	public function createEiFieldConfigurator(): EiFieldConfigurator {
		$eiFieldConfigurator = parent::createEiFieldConfigurator();
		IllegalStateException::assertTrue($eiFieldConfigurator instanceof AdaptableEiFieldConfigurator);
		$eiFieldConfigurator->registerConfObjectPropertyEiField($this);
		return $eiFieldConfigurator;
	}
	
// 	public function isPropertyCompatible(PropertyAccessProxy $propertyAccessProxy) {
// 		$constraints = $propertyAccessProxy->getConstraints();
// 		return $constraints === null || $constraints->isPassableBy($this->entityProperty->getAccessProxy()->getConstraint());
// 	}
	
	public function getPropertyName(): string {
		return $this->objectPropertyAccessProxy->getPropertyName();
	}
	
// 	public function setup(SetupProcess $setupProcess) {
// 		parent::setup($setupProcess);
// 		try {
// 			$entityPropertyConstraints = $this->entityProperty->getAccessProxy()->getConstraint();
// 			$currentPropertyConstraints = $this->propertyAccessProxy->getConstraints();
			
// 			$propertyConstraints = new TypeConstraint($entityPropertyConstraints->getParamClass(),
// 					$entityPropertyConstraints->isArray(), $entityPropertyConstraints->isArrayObject(),
// 					!isset($currentPropertyConstraints) || $currentPropertyConstraints->allowsNull());
	
// 			$this->propertyAccessProxy->setConstraints($propertyConstraints);
// 		} catch (ConstraintsConflictException $e) {
// 			$setupProcess->failedE($this, $e);
// 		}
// 	}
	
	public function checkCompatibility(CompatibilityTest $compatibilityTest) {
		parent::checkCompatibility($compatibilityTest);
		
		if ($compatibilityTest->hasFailed()) return;
		// @todo rewrite compatibility test
// 		$propertyConstraints = $compatibilityTest->getPropertyAccessProxy()->getConstraint();
// 		$entityPropertyContraints = $compatibilityTest->getEntityProperty()->getAccessProxy()->getConstraint();
// 		if ($propertyConstraints !== null && !$propertyConstraints->isPassableBy($entityPropertyContraints, true)) {
// 			$compatibilityTest->propertyTestFailed('EiField can not pass Type ' . $entityPropertyContraints->__toString() 
// 					. ' to property due to incompatible TypeConstraint ' . $propertyConstraints->__toString());
// 		}
	}
}
