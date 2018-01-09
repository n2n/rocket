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
namespace rocket\spec\ei\component\field\impl\string\conf;

use rocket\spec\ei\component\EiSetupProcess;
use n2n\util\ex\IllegalStateException;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\spec\ei\component\field\impl\string\PathPartEiProp;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\spec\ei\component\field\impl\string\modificator\PathPartEiModificator;
use n2n\impl\web\dispatch\mag\model\StringMag;
use rocket\spec\config\SpecEiSetupProcess;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\util\config\InvalidAttributeException;
use n2n\util\StringUtils;
use rocket\spec\ei\component\field\indepenent\CompatibilityLevel;
use n2n\persistence\meta\structure\Column;
use rocket\spec\ei\component\field\indepenent\PropertyAssignation;
use rocket\spec\ei\manage\generic\UnknownScalarEiPropertyException;
use rocket\spec\ei\manage\generic\UnknownGenericEiPropertyException;
use n2n\reflection\CastUtils;
use rocket\spec\ei\manage\generic\ScalarEiProperty;
use rocket\spec\ei\manage\generic\GenericEiProperty;

class PathPartEiPropConfigurator extends AlphanumericEiPropConfigurator {
	const OPTION_BASE_PROPERTY_FIELD_ID_KEY = 'basePropertyFieldId';
	const OPTION_NULL_ALLOWED_KEY = 'allowEmpty';
	const OPTION_UNIQUE_PER_FIELD_ID_KEY = 'uniquePerFieldId';
	const OPTION_CRITICAL_KEY = 'critical';
	const OPTION_CRITICAL_MESSAGE_KEY = 'criticalMessageCodeKey';
	
	private static $commonNeedles = array('pathPart');

	private $pathPartEiProp;
	
	public function __construct(PathPartEiProp $pathPartEiProp) {
		parent::__construct($pathPartEiProp);
		$this->pathPartEiProp = $pathPartEiProp;
		$this->addMandatory = false;
	}
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		$level = parent::testCompatibility($propertyAssignation);
		if (!$level) return $level;
	
		if (StringUtils::contains(self::$commonNeedles, $propertyAssignation->getObjectPropertyAccessProxy()
				->getPropertyName())) {
			return CompatibilityLevel::COMMON;
		}
	
		return $level;
	}
	
	
	public function initAutoEiPropAttributes(Column $column = null) {
		parent::initAutoEiPropAttributes($column);
	
		$options = $this->getBaseEiPropIdOptions();
		if (empty($options)) return;
		
		$this->attributes->set(self::OPTION_BASE_PROPERTY_FIELD_ID_KEY, key($options));
	}
	
	public function setup(EiSetupProcess $setupProcess) {
		parent::setup($setupProcess);
		$setupProcess instanceof SpecEiSetupProcess;
		
		$pathPartEiProp = $this->eiComponent;
		IllegalStateException::assertTrue($pathPartEiProp instanceof PathPartEiProp);
		
		if ($this->attributes->contains(self::OPTION_BASE_PROPERTY_FIELD_ID_KEY)) {
			try {
				$pathPartEiProp->setBaseScalarEiProperty($setupProcess->getScalarEiPropertyByFieldPath(
						$this->attributes->getString(self::OPTION_BASE_PROPERTY_FIELD_ID_KEY)));
			} catch (\InvalidArgumentException $e) {
				throw $setupProcess->createException('Invalid base ScalarEiProperty configured.', $e);
			} catch (UnknownScalarEiPropertyException $e) {
				throw $setupProcess->createException('Configured base ScalarEiProperty not found.', $e);
			}
		}
		
		if ($this->attributes->contains(self::OPTION_UNIQUE_PER_FIELD_ID_KEY)) {
			try {
				$pathPartEiProp->setUniquePerGenericEiProperty($setupProcess->getGenericEiPropertyByEiPropPath(
						$this->attributes->getString(self::OPTION_UNIQUE_PER_FIELD_ID_KEY)));
			} catch (\InvalidArgumentException $e) {
				throw $setupProcess->createException('Invalid unique per GenericEiProperty configured.', $e);
			} catch (UnknownGenericEiPropertyException $e) {
				throw $setupProcess->createException('Configured unique per GenericEiProperty not found.', $e);
			}
		}

		if ($this->attributes->contains(self::OPTION_NULL_ALLOWED_KEY)) {
			$allowEmpty = $this->attributes->getBool(self::OPTION_NULL_ALLOWED_KEY, false);
			if ($allowEmpty && $this->mandatoryRequired()) {
				throw new InvalidAttributeException(self::OPTION_NULL_ALLOWED_KEY 
						. ' must be false because AccessProxy does not allow null value: '
						. $this->getAssignedObjectPropertyAccessProxy());
			}
			$pathPartEiProp->setNullAllowed($allowEmpty);
		}
		
		if ($this->attributes->contains(self::OPTION_CRITICAL_KEY)) {
			$pathPartEiProp->setCritical($this->attributes->get(self::OPTION_CRITICAL_KEY));
		}
		
		if ($this->attributes->contains(self::OPTION_CRITICAL_MESSAGE_KEY)) {
			$pathPartEiProp->setCriticalMessage($this->attributes->getString(self::OPTION_CRITICAL_MESSAGE_KEY));
		}

		$setupProcess->getEiModificatorCollection()->add(new PathPartEiModificator($this->eiComponent));
	}

	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$magCollection = $magDispatchable->getMagCollection();

		$baseScalarEiPropertyId = null;
		if (null !== ($baseScalarEiProperty = $this->pathPartEiProp->getBaseScalarEiProperty())) {
			$baseScalarEiPropertyId = $baseScalarEiProperty->getId();
		}
		
		$magCollection->addMag(self::OPTION_BASE_PROPERTY_FIELD_ID_KEY, new EnumMag('Base Field', 
				$this->getBaseEiPropIdOptions(), $this->attributes->getString(self::OPTION_BASE_PROPERTY_FIELD_ID_KEY, 
						false, $baseScalarEiPropertyId), false));
		
		$genericEiPropertyId = null;
		if (null !== ($genericEiProperty = $this->pathPartEiProp->getUniquePerGenericEiProperty())) {
			$genericEiPropertyId = $genericEiProperty->getId();
		}
		$magCollection->addMag(self::OPTION_UNIQUE_PER_FIELD_ID_KEY, new EnumMag('Unique per', 
				$this->getUniquePerOptions(), $this->attributes->getString(self::OPTION_UNIQUE_PER_FIELD_ID_KEY, 
						false, $genericEiPropertyId)));
		
		$magCollection->addMag(self::OPTION_NULL_ALLOWED_KEY, new BoolMag('Null value allowed.', 
				$this->attributes->getBool(self::OPTION_NULL_ALLOWED_KEY, false, 
						$this->pathPartEiProp->isNullAllowed())));
		
		$magCollection->addMag(self::OPTION_CRITICAL_KEY, new BoolMag('Is critical', 
				$this->attributes->getBool(self::OPTION_CRITICAL_KEY, false, $this->pathPartEiProp->isCritical())));
		
		$magCollection->addMag(self::OPTION_CRITICAL_MESSAGE_KEY, new StringMag('Critical message (no message if empty)', 
				$this->attributes->getString(self::OPTION_CRITICAL_MESSAGE_KEY, false, 
						$this->pathPartEiProp->getCriticalMessage()), false));
		return $magDispatchable;
	}
	
	private function getBaseEiPropIdOptions() {
		$baseEiPropIdOptions = array();
		foreach ($this->eiComponent->getEiEngine()->getScalarEiDefinition()->getScalarEiProperties()
				as $id => $genericScalarProperty) {
			if ($id === $this->eiComponent->getId()) continue;
			CastUtils::assertTrue($genericScalarProperty instanceof ScalarEiProperty);
			$baseEiPropIdOptions[$id] = (string) $genericScalarProperty->getLabelLstr();
		}
		return $baseEiPropIdOptions;
	}
	
	private function getUniquePerOptions() {
		$options = array();
		foreach ($this->eiComponent->getEiEngine()->getGenericEiDefinition()->getGenericEiProperties() as $id => $genericEiProperty) {
			if ($id === $this->eiComponent->getId()) continue;
			CastUtils::assertTrue($genericEiProperty instanceof GenericEiProperty);
			$options[$id] = (string) $genericEiProperty->getLabelLstr();
		}
		return $options;
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$this->attributes->appendAll($magDispatchable->getMagCollection()->readValues(array(
				self::OPTION_BASE_PROPERTY_FIELD_ID_KEY, self::OPTION_NULL_ALLOWED_KEY, 
				self::OPTION_UNIQUE_PER_FIELD_ID_KEY, self::OPTION_CRITICAL_KEY, 
				self::OPTION_CRITICAL_MESSAGE_KEY)), true);
	}
}
