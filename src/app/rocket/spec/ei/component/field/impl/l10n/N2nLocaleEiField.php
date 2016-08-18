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
namespace rocket\spec\ei\component\field\impl\l10n;

use n2n\l10n\N2nLocale;
use n2n\ui\view\impl\html\HtmlView;
use n2n\persistence\orm\property\EntityProperty;
use rocket\spec\ei\component\field\FilterableEiField;
use rocket\spec\ei\component\field\SortableEiField;
use n2n\dispatch\mag\impl\model\EnumMag;
use n2n\util\config\Attributes;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\gui\EntrySourceInfo; 
use n2n\persistence\orm\property\impl\N2nLocaleEntityProperty;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\component\field\impl\adapter\DraftableEiFieldAdapter;
use n2n\reflection\property\TypeConstraint;
use n2n\reflection\property\AccessProxy;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\manage\EiState;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\critmod\filter\FilterField;
use n2n\dispatch\mag\Mag;
use rocket\spec\ei\manage\gui\FieldSourceInfo;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\spec\ei\manage\critmod\sort\impl\SimpleSortField;
use n2n\N2N;
use rocket\spec\ei\component\field\GenericEiField;
use rocket\spec\ei\manage\generic\CommonGenericEiProperty;

class N2nLocaleEiField extends DraftableEiFieldAdapter implements FilterableEiField, SortableEiField, GenericEiField {
	private $definedN2nLocales;
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof N2nLocaleEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('n2n\\l10n\\N2nLocale',
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}
	
	public function getDefinedN2nLocales() {
		return $this->definedN2nLocales;
	}

	public function setDefinedN2nLocales(array $definedN2nLocales = null) {
		$this->definedN2nLocales = $definedN2nLocales;
	}

	public function createOutputUiComponent(HtmlView $view, FieldSourceInfo $entrySourceInfo)  {
		$value = $entrySourceInfo->getEiMapping()->getValue($this->getId());
		if (null === ($n2nLocale = N2nLocale::create($value))) return null;
		return $this->generateDisplayNameForN2nLocale($n2nLocale, $view->getN2nContext()->getN2nLocale());
	}

	public function createMag(string $propertyName, FieldSourceInfo $entrySourceInfo): Mag {
		return new EnumMag($propertyName, $this->getLabelLstr(), $this->buildN2nLocaleArray(
				$entrySourceInfo->getEiState()->getN2nLocale()), null, $this->isMandatory($entrySourceInfo));
	}
	
	public function saveMagValue(Mag $mag, FieldSourceInfo $entrySourceInfo) {
		$entrySourceInfo->setValue(N2nLocale::build($mag->getValue()));
	}
	
	public function loadMagValue(FieldSourceInfo $entrySourceInfo, Mag $mag) {
		if (null !== ($n2nLocale = $entrySourceInfo->getValue())) {
			$mag->setValue((string) $n2nLocale);
		}
	}
	
// 	public function optionAttributeValueToPropertyValue(Attributes $attributes, 
// 			EiMapping $eiMapping, EntrySourceInfo $entrySourceInfo) {
// 		$eiMapping->setValue($this->id, N2nLocale::create($attributes->get($this->id)));
// 	}
	
// 	public function propertyValueToOptionAttributeValue(EiMapping $eiMapping, 
// 			Attributes $attributes, EntrySourceInfo $entrySourceInfo) {
// 		$propertyValue = $eiMapping->getValue(EiFieldPath::from($this));
// 		$attributeValue = null;
// 		if ($propertyValue instanceof N2nLocale) {
// 			$attributeValue = $propertyValue->getId(); 
// 		}
// 		$attributes->set($this->id, $attributeValue);
// 	}

	public function buildIdentityString(EiObject $eiObject, N2nLocale $n2nLocale) {
		$value = $this->read($eiObject);
		if (null === ($parsedN2nLocale = N2nLocale::create($value))) return $value;
		return $this->generateDisplayNameForN2nLocale($parsedN2nLocale, $n2nLocale);
	}

	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof N2nLocaleEntityProperty;
	}
	
	private function buildN2nLocaleArray(N2nLocale $displayN2nLocale) {
		$n2nLocales = array();
		foreach (N2N::getAppConfig()->http()->getAllN2nLocales() as $n2nLocale) {
			$n2nLocales[$n2nLocale->getId()] = $this->generateDisplayNameForN2nLocale($n2nLocale, $displayN2nLocale);
		}
		return $n2nLocales;
	}
	
	private function generateDisplayNameForN2nLocale(N2nLocale $n2nLocale, $displayN2nLocale = null) {
		return $n2nLocale->getName($displayN2nLocale) . ' / ' . $n2nLocale->toPrettyId();
	}
	
// 	public function isMandatory(FieldSourceInfo $entrySourceInfo): bool {
// 		return $this->isMultiLingual() && parent::isMandatory($entrySourceInfo);
// 	}
	
// 	public function isMultiLingual() {
// 		return count($this->n2nLocales) > 1;
// 	}

	public function buildManagedFilterField(EiState $eiState) {
		return $this->buildFilterField($eiState->getN2nContext());
	}
	
	public function buildFilterField(N2nContext $n2nContext) {
		return new N2nLocaleFilterField(CrIt::p($this->entityProperty), $this->getLabelLstr(), 
				$this->buildN2nLocaleArray($n2nContext->getN2nLocale()));
	}
	
	public function buildEiMappingFilterField(N2nContext $n2nContext) {
		return null;
	}
	
	public function buildManagedSortField(EiState $eiState) {
		return $this->buildSortField($eiState->getN2nContext());
	}
	
	public function buildSortField(N2nContext $n2nContext) {
		return new SimpleSortField(CrIt::p($this->entityProperty), $this->getLabelLstr());
	}
	
	public function getGenericEiProperty() {
		if ($this->entityProperty === null) return null;
		
		return new CommonGenericEiProperty($this, CrIt::p($this->entityProperty));
	}
}
