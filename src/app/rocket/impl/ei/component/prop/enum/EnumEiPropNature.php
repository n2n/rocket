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
namespace rocket\impl\ei\component\prop\enum;


 

use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use rocket\op\ei\manage\frame\EiFrame;
use n2n\core\container\N2nContext;
use rocket\op\ei\util\filter\prop\EnumFilterProp;
use rocket\op\ei\manage\critmod\sort\impl\SimpleSortProp;
use n2n\util\type\ArgUtils;
use n2n\util\type\TypeConstraint;
use n2n\reflection\property\AccessProxy;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropNatureAdapter;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\critmod\quick\impl\LikeQuickSearchProp;
use rocket\op\ei\manage\critmod\filter\FilterProp;
use rocket\op\ei\manage\critmod\sort\SortProp;
use rocket\op\ei\manage\critmod\quick\QuickSearchProp;
use rocket\op\ei\manage\entry\EiField;
use n2n\util\StringUtils;
use n2n\util\type\TypeConstraints;
use n2n\impl\persistence\orm\property\IntEntityProperty;
use rocket\si\content\SiField;
use rocket\si\content\impl\SiFields;
use rocket\impl\ei\component\prop\enum\conf\EnumConfig;
use rocket\op\ei\manage\idname\IdNameProp;
use rocket\impl\ei\component\prop\adapter\config\QuickSearchConfigTrait;
use rocket\op\ei\util\factory\EifGuiField;
use rocket\si\content\impl\EnumInSiField;
use rocket\op\ei\manage\DefPropPath;
use n2n\reflection\property\PropertyAccessProxy;
use n2n\util\EnumUtils;
use function PHPUnit\Framework\containsEqual;

class EnumEiPropNature extends DraftablePropertyEiPropNatureAdapter {
	use QuickSearchConfigTrait;

	private array $options = array();
	private array $associatedDefPropPathMap = array();
	private ?string $emptyLabel = null;

	function __construct(PropertyAccessProxy $propertyAccessProxy, private ?\ReflectionEnum $enum = null) {
		if ($this->enum === null) {
			$getterTypeConstraint = TypeConstraints::scalar(true);
		} else {
			$getterTypeConstraint = TypeConstraints::namedType($enum, true);
			$names = array_map(fn (\UnitEnum $c) => $c->name, EnumUtils::units($enum));
			$this->options = array_combine($names, $names);
		}

		parent::__construct($propertyAccessProxy->createRestricted($getterTypeConstraint));
	}

	function getEnum(): ?\ReflectionEnum {
		return $this->enum;
	}

	public function setOptions(array $options): void {
		ArgUtils::valArray($options, 'scalar');

		if ($this->enum === null) {
			$this->options = $options;
			return;
		}

		$this->options = [];
		foreach ($options as $backedValue => $label) {
			if ($this->enum->hasCase($backedValue)) {
				$this->options[$backedValue] = $label;
				continue;
			}

			throw new \InvalidArgumentException('Invalid option value "' . $backedValue
					. '". Options are fixed to: '
					. join(', ', array_map(fn ($u) => $u->name, EnumUtils::units($this->enum))));
		}
	}

	private function unitValueToBackedValue(string|int|\UnitEnum|null $value): string|int|null {
		if ($value === null) {
			return null;
		}

		if ($this->enum === null) {
			assert(is_scalar($value));
			return $value;
		}

		assert($value instanceof \UnitEnum);
		return $value->name;
	}

	private function backedValueToUnitValue(string|int|null $value): string|int|\UnitEnum|null {
		if ($value === null || $this->enum === null) {
			return $value;
		}

		return EnumUtils::nameToUnit($value, $this->enum);
	}


	public function getOptions() {
		return $this->options;
	}

	/**
	 * @param array $associatedDefPropPathMap
	 * @return void
	 */
	public function setAssociatedDefPropPathMap(array $associatedDefPropPathMap) {
		ArgUtils::valArray($associatedDefPropPathMap,
				TypeConstraint::createArrayLike('array', false, TypeConstraint::createSimple(DefPropPath::class)));
		$this->associatedDefPropPathMap = $associatedDefPropPathMap;
	}

	/**
	 * @return array
	 */
	public function getAssociatedDefPropPathMap() {
		return $this->associatedDefPropPathMap;
	}

	/**
	 * @param string|null $emptyLabel
	 */
	function setEmptyLabel(?string $emptyLabel) {
		$this->emptyLabel = $emptyLabel;
	}

	/**
	 * @return string|null
	 */
	function getEmptyLabel() {
		return $this->emptyLabel;
	}

	public function setPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		ArgUtils::assertTrue($propertyAccessProxy !== null);
		
		if (null !== ($typeConstraint = $propertyAccessProxy->getConstraint())) {
			if ($typeConstraint->isArrayLike()) {
				throw new \InvalidArgumentException($typeConstraint->__toString() . ' not compatible with ' . TypeConstraints::scalar(true));
			}
			
			if (!$typeConstraint->isEmpty()) {
				$typeConstraint->setConvertable(true);
			}
			$this->propertyAccessProxy = $propertyAccessProxy;
			return;
		}
		
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('scalar', 
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->propertyAccessProxy = $propertyAccessProxy;
	}

	public function buildEiField(Eiu $eiu): ?EiField {
		$eiu->entry()->onValidate(function () use ($eiu) {
			$curBackedValue = $this->unitValueToBackedValue($eiu->field()->getValue());
				
			$activeDefPropPaths = array();
			foreach ($this->getAssociatedDefPropPathMap() as $value => $defPropPaths) {
				if ($value == $curBackedValue) {
					$activeDefPropPaths = $defPropPaths;
					continue;
				}
				
				foreach ($defPropPaths as $defPropPath) {
					if (null !== ($eiFieldWrapper = $eiu->entry()->getEiFieldAbstraction($defPropPath))) {
						$eiFieldWrapper->setIgnored(true);
					}
				}
			}
			
			foreach ($activeDefPropPaths as $defPropPath) {
				if (null !== ($eiFieldWrapper = $eiu->entry()->getEiFieldAbstraction($defPropPath))) {
					$eiFieldWrapper->setIgnored(false);
				}
			}
		});
		
		return parent::buildEiField($eiu);
	}
	
	public function createInEifGuiField(Eiu $eiu): EifGuiField {
		$choicesMap = $this->getOptions();
		foreach (array_values($choicesMap) as $value) {
			if (!$eiu->entry()->acceptsValue($eiu->prop(), $value)) {
				unset($choicesMap[$value]);
			}
		}
		
		$mapCb = function ($defPropPaths) {
			return array_map(function ($defPropPath) { return (string) $defPropPath; }, $defPropPaths);
		};
		
		$siField = SiFields::enumIn($choicesMap, $this->unitValueToBackedValue($eiu->field()->getValue()))
				->setMandatory($this->isMandatory())
				->setAssociatedPropIdsMap(array_map($mapCb, $this->getAssociatedDefPropPathMap()))
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs())
				->setEmptyLabel($this->getEmptyLabel());
		
// 		$defPropPathMap = $this->getEnumConfig()->getAssociatedDefPropPathMap();
// 		if (empty($defPropPathMap)) {
		return $eiu->factory()->newGuiField($siField)
				->setSaver(function () use ($eiu, $siField)  {
					$this->saveSiField($siField, $eiu);
				});
// 		}
		
// 		$enablerMag = new EnumTogglerMag($this->getLabelLstr(), $choicesMap, null, 
// 					$this->isMandatory($eiu));
		
// 		$that = $this;
// 		$eiu->entryGui()->whenReady(function () use ($eiu, $enablerMag, $that) {
// 			$associatedMagWrapperMap = array();
// 			foreach ($that->getAssociatedDefPropPathMap() as $value => $eiPropPaths) {
// 				$magWrappers = array();
// 				foreach ($eiPropPaths as $eiPropPath) {
// 					$magWrapper = $eiu->entryGui()->getMagWrapper($eiPropPath, false);
// 					if ($magWrapper === null) continue;
					
// 					$magWrappers[] = $magWrapper;
// 				}
				
// 				$associatedMagWrapperMap[$value] = $magWrappers; 
// 			}
			
// 			$enablerMag->setAssociatedMagWrapperMap($associatedMagWrapperMap);
// 		});
		
		
// 		return $enablerMag;
	}
	
	function saveSiField(SiField $siField, Eiu $eiu): void {
		ArgUtils::assertTrue($siField instanceof EnumInSiField);
		$eiu->field()->setValue($this->backedValueToUnitValue($siField->getValue()));
	}
	
	public function createOutEifGuiField(Eiu $eiu): EifGuiField  {
		$backedValue = $this->unitValueToBackedValue($eiu->field()->getValue());
		$options = $this->getOptions();
		
		return $eiu->factory()->newGuiField(SiFields::stringOut($options[$backedValue] ?? $backedValue)
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs()));
	}
	
//	public function buildManagedFilterProp(EiFrame $eiFrame): ?FilterProp  {
//		return $this->buildFilterProp($eiFrame->getN2nContext());
//	}
	
	public function buildFilterProp(Eiu $eiu): ?FilterProp {
		if (null !== ($entityProperty = $this->getEntityProperty())) {
			return new EnumFilterProp(CrIt::p($entityProperty), $this->getLabelLstr(), $this->getOptions());
		}
		
		return null;
	}

	public function buildSortProp(Eiu $eiu): ?SortProp {
		if (null !== ($entityProperty = $this->getEntityProperty())) {
			return new SimpleSortProp(CrIt::p($entityProperty), $this->getLabelLstr());
		}
		
		return null;
	}
	
	public function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp {
		if ($this->isQuickSearchable()
				&& null !== ($entityProperty = $this->getEntityProperty())) {
			return new LikeQuickSearchProp(CrIt::p($entityProperty));
		}
		
		return null;
	}

	function buildIdNameProp(Eiu $eiu): ?IdNameProp  {
		return $eiu->factory()->newIdNameProp(function (Eiu $eiu) {
			return StringUtils::strOf($eiu->object()->readNativeValue($eiu->prop()->getEiProp()));
		})->toIdNameProp();
	}
}
