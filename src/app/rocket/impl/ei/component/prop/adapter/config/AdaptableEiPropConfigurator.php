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
namespace rocket\impl\ei\component\prop\adapter\config;

use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\impl\ei\component\EiConfiguratorAdapter;
use n2n\core\container\N2nContext;
use n2n\web\dispatch\mag\MagCollection;
use rocket\ei\component\EiSetup;
use n2n\reflection\property\ConstraintsConflictException;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use rocket\ei\component\prop\indepenent\IncompatiblePropertyException;
use n2n\reflection\property\AccessProxy;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use n2n\persistence\meta\structure\Column;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\util\ex\IllegalStateException;
use rocket\ei\util\Eiu;

class AdaptableEiPropConfigurator extends EiConfiguratorAdapter implements EiPropConfigurator {

	/**
	 * @var PropertyAssignation
	 */
	private $propertyAssignation;
	
	/**
	 * @var EiPropConfiguratorAdaption[]
	 */
	private $adpations = [];
	
	/**
	 * @var int
	 */
	private $maxCompatibilityLevel = CompatibilityLevel::COMPATIBLE;
		
	public function initAutoEiPropDataSet(N2nContext $n2nContext, Column $column = null) {
		if ($this->addMandatory && $this->autoMandatoryCheck && $this->mandatoryRequired()) {
			$this->dataSet->set(self::ATTR_MANDATORY_KEY, true);
		}
	}
	
// 	public function getPropertyAssignation(): PropertyAssignation {
// 		return new PropertyAssignation($this->getAssignedEntityProperty(), 
// 				$this->getAssignedObjectPropertyAccessProxy());
// 	}
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		try {
			$this->assignProperty($propertyAssignation);
			return $this->maxCompatibilityLevel;
		} catch (IncompatiblePropertyException $e) {
			return CompatibilityLevel::NOT_COMPATIBLE;
		}
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\ei\component\prop\indepenent\EiPropConfigurator::assignProperty()
	 */
	public function assignProperty(PropertyAssignation $propertyAssignation) {
// 		if (!$this->isPropertyAssignable()) {
// 			throw new IncompatiblePropertyException('EiProp can not be assigned to a property.');
// 		}
	
		if ($this->entityPropertyConfigurable !== null) {
			try {
				$this->entityPropertyConfigurable->setEntityProperty(
						$propertyAssignation->getEntityProperty(
								$this->confEntityProperty->isEntityPropertyRequired()));
			} catch (\InvalidArgumentException $e) {
				throw $propertyAssignation->createEntityPropertyException(null, $e);
			}
		}
	
		if ($this->objectPropertyConfigurable !== null) {
			try {
				$this->objectPropertyConfigurable->setObjectPropertyAccessProxy(
						$propertyAssignation->getObjectPropertyAccessProxy(
								$this->objectPropertyConfigurable->isObjectPropertyRequired()));
			} catch (\InvalidArgumentException $e) {
				throw $propertyAssignation->createAccessProxyException(null, $e);
			} catch (ConstraintsConflictException $e) {
				throw $propertyAssignation->createAccessProxyException(null, $e);
			}
		}
		
		$this->propertyAssignation = $propertyAssignation;
	}
	
	public function getTypeName(): string {
		return self::shortenTypeName(parent::getTypeName(), array('Ei', 'Prop'));
	}
	
	public function setMaxCompatibilityLevel(int $maxCompatibilityLevel) {
		$this->maxCompatibilityLevel = $maxCompatibilityLevel;
	}
	
	private $entityPropertyConfigurable;
	
	public function setEntityPropertyConfigurable(EntityPropertyConfigurable $entityPropertyConfigurable) {
		$this->entityPropertyConfigurable = $entityPropertyConfigurable;
	}
	
	private $objectPropertyConfigurable;
	
	public function setObjectPropertyConfigurable(ObjectPropertyConfigurable $objectPropertyConfigurable) {
		$this->objectPropertyConfigurable = $objectPropertyConfigurable;
	}
	

	
// 	public function registerDraftConfigurable(DraftConfigurable $confDraftableEiProp) {
// 		$this->confDraftableEiProp = $confDraftableEiProp;		
// 	}
	
	protected function getPropertyAssignation() {
		if ($this->propertyAssignation === null) {
			throw new IllegalStateException('No PropertyAssignation available.');
		}
		
		return $this->propertyAssignation;
	}
	
// 	/**
// 	 * @todo remove this everywhere
// 	 * @deprecated remove this everywhere
// 	 * @return boolean
// 	 */
// 	public function isPropertyAssignable(): bool {
// 		return $this->entityPropertyConfigurable !== null
// 				|| $this->objectPropertyConfigurable !== null;
// 	}
	
	protected function isAssignableToEntityProperty(): bool {
		return $this->entityPropertyConfigurable !== null;
	}
	
	protected function isAssignableToObjectProperty(): bool {
		return $this->objectPropertyConfigurable != null;
	}

	protected function getAssignedEntityProperty() {
		if ($this->entityPropertyConfigurable === null) return null;
		
		return $this->entityPropertyConfigurable->getEntityProperty();
	}
	
// 	protected function getAssignedObjectPropertyAccessProxy() {
// 		if ($this->objectPropertyConfigurable === null) return null;
		
// 		return $this->objectPropertyConfigurable->getObjectPropertyAccessProxy();
// 	}
	
// 	protected function requireEntityProperty(): EntityProperty {
// 		if (null !== ($entityProperty = $this->getAssignedEntityProperty())) {
// 			return $entityProperty;
// 		}
	
// 		throw new InvalidEiComponentConfigurationException('No EntityProperty assigned to EiProp: ' . $this->eiComponent);
// 	}
	
	protected function requireObjectPropertyAccessProxy(): AccessProxy  {
		if (null !== ($accessProxy = $this->getAssignedObjectPropertyAccessProxy())) {
			return $accessProxy;
		}
	
		throw new InvalidEiComponentConfigurationException('No ObjectProperty assigned to EiProp: ' . $this->eiComponent);
	}
	
	protected function requirePropertyName(): string {
		$propertyAssignation = $this->getPropertyAssignation();
		
		if (null !== ($entityProperty = $propertyAssignation->getEntityProperty())) {
			return $entityProperty->getName();
		}
	
		if (null !== ($accessProxy = $propertyAssignation->getObjectPropertyAccessProxy())) {
			return $accessProxy->getPropertyName();
		}
	
		throw new InvalidEiComponentConfigurationException('No property assigned to EiProp: ' . $this->eiComponent);
	}
	
	public function getEntityPropertyName() {
		if ($this->entityPropertyConfigurable === null) {
			return null;
		}
		
		return $this->entityPropertyConfigurable->getEntityProperty()->getName();
	}
	
	public function getObjectPropertyName() {
		if ($this->objectPropertyConfigurable === null) {
			return null;
		}
		
		return $this->objectPropertyConfigurable->getObjectPropertyAccessProxy()->getPropertyName();
	}
	
	public function setup(EiSetup $eiSetupProcess) {
		$eiu = $eiSetupProcess->eiu();
		
		foreach ($this->adpations as $adaption) {
			$adaption->setup($eiu, $this->dataSet);
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\EiConfiguratorAdapter::createMagDispatchable()
	 */
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magCollection = new MagCollection();
		
		$eiu = new Eiu($n2nContext);
		foreach ($this->adpations as $adaption) {
			$adaption->mag($eiu, $this->dataSet, $magCollection);
		}
		
		return new MagForm($magCollection);
	}

	protected function mandatoryRequired() {
		$accessProxy = $this->getPropertyAssignation()->getObjectPropertyAccessProxy(false);
		if (null === $accessProxy) return false;
		return !$accessProxy->getConstraint()->allowsNull() && !$accessProxy->getConstraint()->isArrayLike();
	}
	
	function addAdaption(EiPropConfiguratorAdaption $adaption) {
		$this->adpations[] = $adaption;
	}
}
