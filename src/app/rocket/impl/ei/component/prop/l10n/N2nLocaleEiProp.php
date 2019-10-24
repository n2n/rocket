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
namespace rocket\impl\ei\component\prop\l10n;

use n2n\l10n\IllegalN2nLocaleFormatException;
use n2n\l10n\N2nLocale;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\persistence\orm\property\EntityProperty;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use rocket\ei\component\prop\FilterableEiProp;
use rocket\ei\component\prop\ScalarEiProp;
use rocket\ei\component\prop\SortableEiProp;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\ei\manage\generic\CommonScalarEiProperty;
use n2n\impl\persistence\orm\property\N2nLocaleEntityProperty;
use n2n\util\type\ArgUtils;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use n2n\util\type\TypeConstraint;
use n2n\reflection\property\AccessProxy;
use n2n\core\container\N2nContext;
use n2n\web\dispatch\mag\Mag;
use rocket\ei\util\Eiu;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\manage\critmod\sort\impl\SimpleSortProp;
use rocket\ei\component\prop\GenericEiProp;
use rocket\ei\manage\generic\CommonGenericEiProperty;
use n2n\core\config\WebConfig;
use rocket\ei\manage\critmod\filter\FilterProp;
use rocket\ei\manage\critmod\sort\SortProp;
use rocket\ei\manage\generic\GenericEiProperty;
use rocket\ei\manage\generic\ScalarEiProperty;
use rocket\si\content\SiField;

class N2nLocaleEiProp extends DraftablePropertyEiPropAdapter implements FilterableEiProp, SortableEiProp, GenericEiProp,
		ScalarEiProp {
	private $definedN2nLocales;
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
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

	public function createOutSiField(Eiu $eiu): SiField  {
		$value = $eiu->entry()->getValue($this);
		if ($value === null) return null;
		
		$n2nLocale = N2nLocale::create($value);
		return $this->generateDisplayNameForN2nLocale($n2nLocale, $view->getN2nContext()->getN2nLocale());
	}

	public function createInSiField(Eiu $eiu): SiField {
		return new EnumMag($this->getLabelLstr(), 
				$this->buildN2nLocaleOptions($eiu->lookup(WebConfig::class), $eiu->frame()->getN2nLocale()), 
				null, $this->isMandatory($eiu));
	}
	
	public function saveMagValue(Mag $mag, Eiu $eiu) {
		$eiu->field()->setValue(N2nLocale::build($mag->getValue()));
	}
	
	public function loadMagValue(Eiu $eiu, Mag $mag) {
		if (null !== ($n2nLocale = $eiu->field()->getValue())) {
			$mag->setValue((string) $n2nLocale);
		}
	}
	
// 	public function optionAttributeValueToPropertyValue(DataSet $dataSet, 
// 			EiEntry $eiEntry, Eiu $eiu) {
// 		$eiEntry->setValue($this->id, N2nLocale::create($dataSet->get($this->id)));
// 	}
	
// 	public function propertyValueToOptionAttributeValue(EiEntry $eiEntry, 
// 			DataSet $dataSet, Eiu $eiu) {
// 		$propertyValue = $eiEntry->getValue(EiPropPath::from($this));
// 		$attributeValue = null;
// 		if ($propertyValue instanceof N2nLocale) {
// 			$attributeValue = $propertyValue->getId(); 
// 		}
// 		$dataSet->set($this->id, $attributeValue);
// 	}

	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		$value = $eiu->object()->readNativValue($this);
		
		if ($value === null) {
			return null;
		}
		
		return $this->generateDisplayNameForN2nLocale(N2nLocale::create($value), $n2nLocale);
	}

	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof N2nLocaleEntityProperty;
	}
	
	/**
	 * @param WebConfig $webConfig
	 * @param N2nLocale $displayN2nLocale
	 * @return N2nLocale[]
	 */
	private function buildN2nLocaleOptions(WebConfig $webConfig, N2nLocale $displayN2nLocale) {
		$options = array();
		$n2nLocales = $this->definedN2nLocales ?? $webConfig->getAllN2nLocales();
		
		foreach ($n2nLocales as $n2nLocale) {
			$options[$n2nLocale->getId()] = $this->generateDisplayNameForN2nLocale($n2nLocale, $displayN2nLocale);
		}
		return $options;
	}
	
	private function generateDisplayNameForN2nLocale(N2nLocale $n2nLocale, $displayN2nLocale = null) {
		return $n2nLocale->getName($displayN2nLocale) /*. ' / ' . $n2nLocale->toPrettyId()*/;
	}
	
// 	public function isMandatory(Eiu $eiu): bool {
// 		return $this->isMultiLingual() && parent::isMandatory($eiu);
// 	}
	
// 	public function isMultiLingual() {
// 		return count($this->n2nLocales) > 1;
// 	}
	
	public function buildFilterProp(Eiu $eiu): ?FilterProp {
		return new N2nLocaleFilterProp(CrIt::p($this->entityProperty), $this->getLabelLstr(), 
				$this->buildN2nLocaleOptions($eiu->lookup(WebConfig::class), $eiu->getN2nLocale()));
	}
	
	public function buildSecurityFilterProp(N2nContext $n2nContext) {
		return null;
	}
	
	public function buildSortProp(Eiu $eiu): ?SortProp {
		return new SimpleSortProp(CrIt::p($this->entityProperty), $this->getLabelLstr());
	}
	
	public function getGenericEiProperty(): ?GenericEiProperty {
		if ($this->entityProperty === null) return null;
		
		return new CommonGenericEiProperty($this, CrIt::p($this->entityProperty));
	}


	public function getScalarEiProperty(): ?ScalarEiProperty {
		return new CommonScalarEiProperty($this,
				function (N2nLocale $n2nLocale = null) {
					if ($n2nLocale === null) return null;

					return (string) $n2nLocale;
				},
				function (string $n2nLocaleId = null) {
					if ($n2nLocaleId === null) return null;

					try {
						return N2nLocale::create($n2nLocaleId);
					} catch (IllegalN2nLocaleFormatException $e) {
						throw new ValueIncompatibleWithConstraintsException(null, 0, $e);
					}
				});
	}
}
