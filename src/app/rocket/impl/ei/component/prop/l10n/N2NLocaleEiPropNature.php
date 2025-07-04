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

use n2n\core\config\WebConfig;
use n2n\impl\persistence\orm\property\N2nLocaleEntityProperty;
use n2n\l10n\IllegalN2nLocaleFormatException;
use n2n\l10n\N2nLocale;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\property\EntityProperty;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use rocket\op\ei\manage\critmod\filter\FilterProp;
use rocket\op\ei\manage\critmod\sort\SortProp;
use rocket\op\ei\manage\critmod\sort\impl\SimpleSortProp;
use rocket\op\ei\manage\generic\CommonGenericEiProperty;
use rocket\op\ei\manage\generic\CommonScalarEiProperty;
use rocket\op\ei\manage\generic\GenericEiProperty;
use rocket\op\ei\manage\generic\ScalarEiProperty;
use rocket\op\ei\manage\idname\IdNameProp;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\util\factory\EifGuiField;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropNatureAdapter;
use rocket\ui\si\content\SiField;
use rocket\ui\si\content\impl\SiFields;
use n2n\reflection\property\PropertyAccessProxy;
use n2n\util\type\TypeConstraints;
use rocket\ui\gui\field\BackableGuiField;
use rocket\ui\gui\field\impl\GuiFields;
use n2n\bind\mapper\impl\Mappers;


class N2NLocaleEiPropNature extends DraftablePropertyEiPropNatureAdapter {
	private $definedN2nLocales;

	function __construct(PropertyAccessProxy $propertyAccessProxy) {
		parent::__construct($propertyAccessProxy->createRestricted(TypeConstraints::namedType(N2nLocale::class, true)));
	}

	public function getDefinedN2nLocales() {
		return $this->definedN2nLocales;
	}

	public function setDefinedN2nLocales(?array $definedN2nLocales = null) {
		$this->definedN2nLocales = $definedN2nLocales;
	}

	public function buildOutGuiField(Eiu $eiu): ?BackableGuiField  {
		$value = $eiu->entry()->getValue($eiu->prop());

		return GuiFields::out(SiFields
				::stringOut($value === null ? '' : $this->generateDisplayNameForN2nLocale($value, $eiu->getN2nLocale())));
	}

	public function buildInGuiField(Eiu $eiu): ?BackableGuiField {
		$options = $this->buildN2nLocaleOptions($eiu->lookup(WebConfig::class), $eiu->frame()->getN2nLocale());
		$value = $eiu->field()->getValue();

		return GuiFields::enumIn($this->isMandatory(), $options, ($value !== null ? (string) $value : null))
				->setModel($eiu->field()->asGuiFieldModel(Mappers::n2nLocale()));

//		$siField = SiFields::enumIn($options, )
//				->setMandatory($this->isMandatory())
//				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());
//
//		return $eiu->factory()->newGuiField($siField)
//				->setSaver(function () use ($siField, $eiu) {
//					$eiu->field()->setValue(N2nLocale::build($siField->getValue()));
//				});
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

	
	function buildIdNameProp(Eiu $eiu): ?IdNameProp {
		return $eiu->factory()->newIdNameProp(function (Eiu $eiu) {
			return $this->buildIdentityString($eiu, $eiu->getN2nLocale());
		})->toIdNameProp();
	}
	
	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		$value = $eiu->object()->readNativeValue($eiu->prop()->getEiProp());
		
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

	public function buildSortProp(Eiu $eiu): ?SortProp {
		return new SimpleSortProp(CrIt::p($this->entityProperty), $this->getLabelLstr());
	}
	
	public function buildGenericEiProperty(Eiu $eiu): ?GenericEiProperty {
		if ($this->entityProperty === null) return null;
		
		return new CommonGenericEiProperty($eiu->prop()->getPath(), $this->getLabelLstr(), CrIt::p($this->entityProperty));
	}


	public function buildScalarEiProperty(Eiu $eiu): ?ScalarEiProperty {
		return new CommonScalarEiProperty($eiu->prop()->getPath(), $eiu->prop()->getLabelLstr(),
				function (?N2nLocale $n2nLocale = null) {
					if ($n2nLocale === null) return null;

					return (string) $n2nLocale;
				},
				function (?string $n2nLocaleId = null) {
					if ($n2nLocaleId === null) return null;

					try {
						return N2nLocale::create($n2nLocaleId);
					} catch (IllegalN2nLocaleFormatException $e) {
						throw new ValueIncompatibleWithConstraintsException(null, 0, $e);
					}
				});
	}
	protected function prepare() {
	}

	public function saveSiField(SiField $siField, Eiu $eiu) {
	}

}
