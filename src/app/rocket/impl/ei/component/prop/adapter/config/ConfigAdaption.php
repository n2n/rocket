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

use rocket\ei\util\Eiu;
use n2n\util\type\attrs\DataSet;
use n2n\persistence\meta\structure\Column;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use n2n\reflection\property\AccessProxy;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\property\EntityProperty;

abstract class ConfigAdaption implements EiPropConfiguratorAdaption {
	
	/**
	 * @var PropertyAssignation
	 */
	private $propertyAssignation;
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\config\EiPropConfiguratorAdaption::testCompatibility()
	 */
	function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		return null;
	}
	
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\config\EiPropConfiguratorAdaption::assignProperty()
	 */
	function assignProperty(PropertyAssignation $propertyAssignation) {
		$this->propertyAssignation = $propertyAssignation;
	}
	
	protected function getPropertyAssignation() {
		if ($this->propertyAssignation !== null) {
			return $this->propertyAssignation;
		}
		
		throw new IllegalStateException('PropertyAssignation not available.');
		
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\config\EiPropConfiguratorAdaption::autoAttributes()
	 */
	function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
	}
	
	protected function mandatoryRequired() {
		$accessProxy = $this->getPropertyAssignation()->getObjectPropertyAccessProxy(false);
		if (null === $accessProxy) return false;
		return !$accessProxy->getConstraint()->allowsNull() && !$accessProxy->getConstraint()->isArrayLike();
	}
	/**
	 * @throws InvalidEiComponentConfigurationException
	 * @return string
	 */
	protected function requirePropertyName() {
		$propertyAssignation = $this->getPropertyAssignation();
		
		if (null !== ($entityProperty = $propertyAssignation->getEntityProperty())) {
			return $entityProperty->getName();
		}
		
		if (null !== ($accessProxy = $propertyAssignation->getObjectPropertyAccessProxy())) {
			return $accessProxy->getPropertyName();
		}
		
		throw new InvalidEiComponentConfigurationException('No property assigned to EiProp: ' . $this->eiComponent);
	}
}
