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
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\util\ex\IllegalStateException;
use rocket\ei\util\Eiu;
use n2n\persistence\meta\structure\Column;

class AdaptableEiPropConfigurator extends EiConfiguratorAdapter implements EiPropConfigurator {

	/**
	 * @var PropertyAssignation
	 */
	private $propertyAssignation;
	
	/**
	 * @var EiPropConfiguratorAdaption[]
	 */
	private $adapations = [];
	
	/**
	 * @var \Closure[]
	 */
	private $setupCallbacks = [];
	
	/**
	 * @var int
	 */
	private $defaultCompatibilityLevel = CompatibilityLevel::COMPATIBLE;
	
	
// 	public function getPropertyAssignation(): PropertyAssignation {
// 		return new PropertyAssignation($this->getAssignedEntityProperty(), 
// 				$this->getAssignedObjectPropertyAccessProxy());
// 	}

	/**
	 * @param int $defaultCompatibilityLevel
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	function setDefaultCompatibilityLevel(int $defaultCompatibilityLevel) {
		$this->defaultCompatibilityLevel = $defaultCompatibilityLevel;
		return $this;
	}
	
	/**
	 * @return int
	 */
	function getDefaultCompatibilityLevel() {
		return $this->defaultCompatibilityLevel;
	}
	
	function testCompatibility(PropertyAssignation $propertyAssignation): int {
		try {
			$this->assignProperty($propertyAssignation);
		} catch (IncompatiblePropertyException $e) {
			return CompatibilityLevel::NOT_COMPATIBLE;
		}
		
		$curLevel = null;
		foreach ($this->adapations as $adaption) {
			$resultLevel = $adaption->testCompatibility($propertyAssignation);
			if ($resultLevel === null) {
				continue;
			}
			
			if ($resultLevel === CompatibilityLevel::NOT_COMPATIBLE) {
				return $resultLevel;
			}
			
			if ($curLevel === null || $curLevel > $resultLevel) {
				$curLevel = $resultLevel;
			}
		}
		return $curLevel ?? $this->defaultCompatibilityLevel;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\indepenent\EiPropConfigurator::initAutoEiPropAttributes()
	 */
	function initAutoEiPropAttributes(N2nContext $n2nContext, Column $column = null) {
		$eiu = new Eiu($n2nContext);
		foreach ($this->adapations as $adaption) {
			$adaption->autoAttributes($eiu, $this->dataSet, $column);
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
								$this->entityPropertyConfigurable->isEntityPropertyRequired()));
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
		
		foreach ($this->adapations as $adaption) {
			$adaption->assignProperty($propertyAssignation);
		}
		
		$this->propertyAssignation = $propertyAssignation;
	}
	
	public function getTypeName(): string {
		return self::shortenTypeName(parent::getTypeName(), array('Ei', 'Prop'));
	}
	
	public function setMaxCompatibilityLevel(int $maxCompatibilityLevel) {
		$this->defaultCompatibilityLevel = $maxCompatibilityLevel;
	}
	
	private $entityPropertyConfigurable;
	
	/**
	 * @param EntityPropertyConfigurable $entityPropertyConfigurable
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	public function setEntityPropertyConfigurable(EntityPropertyConfigurable $entityPropertyConfigurable) {
		$this->entityPropertyConfigurable = $entityPropertyConfigurable;
		return $this;
	}
	
	private $objectPropertyConfigurable;
	
	/**
	 * @param ObjectPropertyConfigurable $objectPropertyConfigurable
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	public function setObjectPropertyConfigurable(ObjectPropertyConfigurable $objectPropertyConfigurable) {
		$this->objectPropertyConfigurable = $objectPropertyConfigurable;
		return $this;
	}
	

	
// 	public function registerDraftConfigurable(DraftConfigurable $confDraftableEiProp) {
// 		$this->confDraftableEiProp = $confDraftableEiProp;		
// 	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\ei\component\prop\indepenent\PropertyAssignation
	 */
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
		
		foreach ($this->adapations as $adaption) {
			$adaption->setup($eiu, $this->dataSet);
		}
		
		foreach ($this->setupCallbacks as $setupCallback) {
			$setupCallback($eiu, $this->dataSet);
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\EiConfiguratorAdapter::createMagDispatchable()
	 */
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magCollection = new MagCollection();
		
		$eiu = new Eiu($n2nContext, $this->eiComponent);
		foreach ($this->adapations as $adaption) {
			$adaption->mag($eiu, $this->dataSet, $magCollection);
		}
		
		return new MagForm($magCollection);
	}
	
	function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$eiu = new Eiu($n2nContext, $this->eiComponent);
		$magCollection = $magDispatchable->getMagCollection();
		foreach ($this->adapations as $adaption) {
			$adaption->save($eiu, $magCollection, $this->dataSet);
		}
	}
	
	/**
	 * @param EiPropConfiguratorAdaption $adaption
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	function addAdaption(EiPropConfiguratorAdaption $adaption) {
		$this->adapations[spl_object_hash($adaption)] = $adaption;
		return $this;
	}
	
	/**
	 * @param EiPropConfiguratorAdaption $adaption
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	function removeAdaption(EiPropConfiguratorAdaption $adaption) {
		unset($this->adapations[spl_object_hash($adaption)]);
		return $this;
	}
	
	/**
	 * @param \Closure $setupCallback
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	function addSetupCallback(\Closure $setupCallback) {
		$this->setupCallbacks[spl_object_hash($setupCallback)] = $setupCallback;
		return $this;
	}
	
	/**
	 * @param \Closure $setupCallback
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	function removeSetupCallback(\Closure $setupCallback) {
		unset($this->setupCallbacks[spl_object_hash($setupCallback)]);
		return $this;
	}
}
