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
namespace rocket\spec\ei\component\field\impl\translation\conf;

use rocket\spec\ei\component\field\impl\adapter\AdaptableEiFieldConfigurator;
use rocket\spec\ei\component\EiSetupProcess;
use n2n\l10n\N2nLocale;
use rocket\spec\config\UnknownSpecException;
use rocket\spec\ei\mask\UnknownEiMaskException;
use rocket\spec\ei\component\UnknownEiComponentException;
use rocket\spec\ei\component\field\impl\translation\TranslationEiField;
use rocket\spec\ei\component\field\indepenent\CompatibilityLevel;
use rocket\spec\ei\component\field\indepenent\PropertyAssignation;
use rocket\spec\ei\component\InvalidEiComponentConfigurationException;
use n2n\persistence\orm\CascadeType;
use n2n\reflection\ReflectionUtils;
use n2n\core\container\N2nContext;
use n2n\dispatch\mag\impl\model\BoolMag;
use n2n\util\config\LenientAttributeReader;
use n2n\dispatch\mag\impl\model\MagCollectionArrayMag;
use n2n\dispatch\mag\MagCollection;
use n2n\dispatch\mag\impl\model\StringMag;
use n2n\dispatch\mag\impl\model\MagForm;
use n2n\dispatch\mag\MagDispatchable;
use n2n\l10n\IllegalN2nLocaleFormatException;
use n2n\reflection\property\TypeConstraint;
use n2n\core\config\HttpConfig;

class TranslationEiConfigurator extends AdaptableEiFieldConfigurator {
	const ATTR_USE_SYSTEM_LOCALES_KEY = 'useSystemN2nLocales';
	const ATTR_SYSTEM_LOCALE_DEFS_KEY = 'systenN2nLocaleDefs';
	const ATTR_CUSTOM_LOCALE_DEFS_KEY = 'customN2nLocaleDefs';
	const ATTR_LOCALE_ID_KEY = 'id';
	const ATTR_LOCALE_LABEL_KEY = 'label';
	const ATTR_LOCALE_MANDATORY_KEY = 'mandatory';
	
	private $translationEiField;
	
	public function __construct(TranslationEiField $translationEiField) {
		parent::__construct($translationEiField);
				
		$this->autoRegister($translationEiField);
		
		$this->translationEiField = $translationEiField;
	}
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		$level = parent::testCompatibility($propertyAssignation);
		if (CompatibilityLevel::NOT_COMPATIBLE == $level) {
			return CompatibilityLevel::NOT_COMPATIBLE;
		}
		
		return CompatibilityLevel::EXTREMELY_COMMON;
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$lar = new LenientAttributeReader($this->attributes);
		
		$magCollection = $magDispatchable->getMagCollection();
		$magCollection->addMag(new BoolMag(self::ATTR_USE_SYSTEM_LOCALES_KEY, 'Use system locales',
				$lar->getBool(self::ATTR_USE_SYSTEM_LOCALES_KEY, true)));
		
		$systemN2nLocaleDefsMag = new MagCollectionArrayMag(self::ATTR_SYSTEM_LOCALE_DEFS_KEY, 'System locales',
				$this->createN2nLocaleDefMagClosure());
		$systemN2nLocaleDefsMag->setValue($this->n2nLocaleDefsToMagValue($this->readModN2nLocaleDefs(
				self::ATTR_SYSTEM_LOCALE_DEFS_KEY, $lar, $n2nContext->lookup(HttpConfig::class)->getSupersystem()->getN2nLocales())));
		$magCollection->addMag($systemN2nLocaleDefsMag);
		
		$customN2nLocaleDefsMag = new MagCollectionArrayMag(self::ATTR_CUSTOM_LOCALE_DEFS_KEY, 'Custom locales',
				$this->createN2nLocaleDefMagClosure());
		$customN2nLocaleDefsMag->setValue($this->n2nLocaleDefsToMagValue(
				$this->readN2nLocaleDefs(self::ATTR_CUSTOM_LOCALE_DEFS_KEY, $lar)));
		$magCollection->addMag($customN2nLocaleDefsMag);
		
		return $magDispatchable;
	}
	
	private function createN2nLocaleDefMagClosure() {
		return function () {
			$magCollection = new MagCollection();
			$magCollection->addMag(new StringMag(self::ATTR_LOCALE_ID_KEY, 'N2nLocale', null, true));
			$magCollection->addMag(new BoolMag(self::ATTR_LOCALE_MANDATORY_KEY, 'Mandatory'));
			$magCollection->addMag(new StringMag(self::ATTR_LOCALE_LABEL_KEY, 'Label', null, false));
			return new MagForm($magCollection);
		};
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$this->attributes->appendAll($magDispatchable->getMagCollection()->readValues(
				array(self::ATTR_USE_SYSTEM_LOCALES_KEY, self::ATTR_SYSTEM_LOCALE_DEFS_KEY, self::ATTR_CUSTOM_LOCALE_DEFS_KEY)));
	}
	
	private function n2nLocaleDefsToMagValue(array $n2nLocaleDefs) {
		$magValue = array();
		foreach ($n2nLocaleDefs as $n2nLocaleDef) {
			$magValue[] = array(
					self::ATTR_LOCALE_ID_KEY => $n2nLocaleDef->getN2nLocale()->getId(),
					self::ATTR_LOCALE_MANDATORY_KEY => $n2nLocaleDef->isMandatory(),
					self::ATTR_LOCALE_LABEL_KEY => $n2nLocaleDef->getLabel());
		}
		return $magValue;
	}
	
	private function readModN2nLocaleDefs($key, LenientAttributeReader $lar, array $n2nLocales): array {
		$modN2nLocaleDefs = $this->readN2nLocaleDefs($key, $lar);
		
		$n2nLocaleDefs = array();
		foreach ($n2nLocales as $n2nLocale) {			
			$n2nLocaleId = $n2nLocale->getId();
			
			if (isset($modN2nLocaleDefs[$n2nLocaleId])) {
				$n2nLocaleDefs[$n2nLocaleId] = $modN2nLocaleDefs[$n2nLocaleId];
				continue;
			}
			
			$n2nLocaleDefs[$n2nLocaleId] = new N2nLocaleDef($n2nLocale, false, null);
		}

		return $n2nLocaleDefs;
	}
	
	private function readN2nLocaleDefs($key, LenientAttributeReader $lar): array {
		$n2nLocaleDefs = array();
		foreach ($lar->getArray($key, array(), TypeConstraint::createArrayLike('array', false, 
				TypeConstraint::createSimple('scalar'))) as $n2nLocaleDefAttr) {
			if (!isset($n2nLocaleDefAttr[self::ATTR_LOCALE_ID_KEY])) continue;
			$n2nLocale = null;
			try {
				$n2nLocale = N2nLocale::create($n2nLocaleDefAttr[self::ATTR_LOCALE_ID_KEY]);
			} catch (IllegalN2nLocaleFormatException $e) {
				continue;
			}
			
			$n2nLocaleDefs[$n2nLocale->getId()] = new N2nLocaleDef($n2nLocale,
					(isset($n2nLocaleDefAttr[self::ATTR_LOCALE_MANDATORY_KEY]) 
							? (bool) $n2nLocaleDefAttr[self::ATTR_LOCALE_MANDATORY_KEY] : false),
					(isset($n2nLocaleDefAttr[self::ATTR_LOCALE_LABEL_KEY]) 
							? $n2nLocaleDefAttr[self::ATTR_LOCALE_LABEL_KEY] : null));
		}
		return $n2nLocaleDefs;
	}
	
	private function writeN2nLocaleDefs($key, array $n2nLocaleDefs, bool $modOnly) {
		$n2nLocaleDefsAttrs = array();
		
		foreach ($n2nLocaleDefs as $n2nLocaleDef) {
			$attrs = array(self::ATTR_LOCALE_ID_KEY => $n2nLocaleDef->getN2nLocale()->getId());
			if ($n2nLocaleDef->isMandatory()) {
				$attrs[self::ATTR_LOCALE_MANDATORY_KEY] = $n2nLocaleDef->isMandatory();
			}
			if (null !== ($label = $n2nLocaleDef->getLabel())) {
				$attrs[self::ATTR_LOCALE_LABEL_KEY] = $n2nLocaleDef->getLabel();
			}
			$n2nLocaleDefsAttrs[] = $attrs;
		}
		
		$this->attributes->set($key, $n2nLocaleDefsAttrs);
	}
	
	public function setup(EiSetupProcess $eiSetupProcess) {
		$n2nContext = $eiSetupProcess->getN2nContext();
		
		$lar = new LenientAttributeReader($this->attributes);
		
		$n2nLocaleDefs = array();
		if ($this->attributes->getBool(self::ATTR_USE_SYSTEM_LOCALES_KEY, false, true)) {
			$n2nLocaleDefs = $this->readModN2nLocaleDefs(self::ATTR_SYSTEM_LOCALE_DEFS_KEY, $lar, 
					$n2nContext->lookup(HttpConfig::class)->getAllN2nLocales());
		} 
		
		$n2nLocaleDefs = array_merge($n2nLocaleDefs, $this->readN2nLocaleDefs(self::ATTR_CUSTOM_LOCALE_DEFS_KEY, $lar));
		$this->translationEiField->setN2nLocaleDefs($n2nLocaleDefs);
		
		// @todo combine with relation eifields
		$eiFieldRelation = $this->translationEiField->getEiFieldRelation();
		$relationProperty = $eiFieldRelation->getRelationEntityProperty();
		$targetEntityClass = $relationProperty->getRelation()->getTargetEntityModel()->getClass();
		try {
			$targetEiSpec = $eiSetupProcess->getEiSpecByClass($targetEntityClass);
				
			$targetEiMask = null;
// 			if (null !== ($eiMaskId = $this->attributes->get(self::OPTION_TARGET_MASK_KEY))) {
// 				$targetEiMask = $target->getEiMaskCollection()->getById($eiMaskId);
// 			} else {
				$targetEiMask = $targetEiSpec->getEiMaskCollection()->getOrCreateDefault();
// 			}

			$entityProperty = $this->requireEntityProperty();
			if (CascadeType::ALL !== $entityProperty->getRelation()->getCascadeType()) {
				throw $eiSetupProcess->createException('EiField requires an EntityProperty which cascades all: ' 
						. ReflectionUtils::prettyPropName($entityProperty->getEntityModel()->getClass(),
								$entityProperty->getName()));
			}
			
			if (!$entityProperty->getRelation()->isOrphanRemoval()) {
				throw $eiSetupProcess->createException('EiField requires an EntityProperty which removes orphans: '
						. ReflectionUtils::prettyPropName($entityProperty->getEntityModel()->getClass(),
								$entityProperty->getName()));
			}

			$eiFieldRelation->init($targetEiSpec, $targetEiMask);
		} catch (UnknownSpecException $e) {
			throw $eiSetupProcess->createException(null, $e);
		} catch (UnknownEiMaskException $e) {
			throw $eiSetupProcess->createException(null, $e);
		} catch (UnknownEiComponentException $e) {
			throw $eiSetupProcess->createException('EiField for Mapped Property required', $e);
		} catch (InvalidEiComponentConfigurationException $e) {
			throw $eiSetupProcess->createException(null, $e);
		}
	}
}

class N2nLocaleDef {
	private $n2nLocale;
	private $mandatory;
	private $label;
	
	public function __construct(N2nLocale $n2nLocale, bool $mandatory, string $label = null) {
		$this->n2nLocale = $n2nLocale;
		$this->mandatory = $mandatory;
		$this->label = $label;
	}
	
	public function getN2nLocaleId() {
		return $this->n2nLocale->getId();
	}
	
	public function getN2nLocale() {
		return $this->n2nLocale;
	}
	
	public function isMandatory() {
		return $this->mandatory;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function buildLabel(N2nLocale $n2nLocale) {
		if ($this->label !== null) {
			return $this->label;
		}
		
		return $this->n2nLocale->getName($this->n2nLocale);
	}
}
