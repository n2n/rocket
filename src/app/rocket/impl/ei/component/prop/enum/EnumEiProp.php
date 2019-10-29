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

use rocket\ei\component\prop\FilterableEiProp;
use rocket\ei\component\prop\SortableEiProp;
use rocket\ei\component\prop\QuickSearchableEiProp;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use rocket\ei\manage\frame\EiFrame;
use n2n\l10n\N2nLocale;
use n2n\core\container\N2nContext;
use rocket\ei\util\filter\prop\EnumFilterProp;
use rocket\ei\manage\critmod\sort\impl\SimpleSortProp;

use n2n\util\type\ArgUtils;
use n2n\util\type\TypeConstraint;
use n2n\reflection\property\AccessProxy;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\enum\conf\EnumEiPropConfigurator;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\manage\critmod\quick\impl\LikeQuickSearchProp;
use rocket\ei\manage\gui\field\GuiFieldPath;
use n2n\impl\web\dispatch\mag\model\group\EnumTogglerMag;
use rocket\ei\manage\critmod\filter\FilterProp;
use rocket\ei\manage\critmod\sort\SortProp;
use rocket\ei\manage\critmod\quick\QuickSearchProp;
use rocket\ei\manage\entry\EiField;
use n2n\util\StringUtils;
use n2n\util\type\TypeConstraints;
use n2n\impl\persistence\orm\property\IntEntityProperty;
use rocket\si\content\SiField;
use rocket\si\content\impl\SiFields;
use rocket\impl\ei\component\prop\enum\conf\EnumConfig;

class EnumEiProp extends DraftablePropertyEiPropAdapter implements FilterableEiProp, SortableEiProp, 
		QuickSearchableEiProp {
	
	private $enumConfig;
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty === null || $entityProperty instanceof ScalarEntityProperty
				|| $entityProperty instanceof IntEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		ArgUtils::assertTrue($propertyAccessProxy !== null);
		
		if (null !== ($typeConstraint = $propertyAccessProxy->getConstraint())) {
			$typeConstraint->isPassableTo(TypeConstraints::scalar(true), true);
			if (!$typeConstraint->isEmpty()) {
				$typeConstraint->setConvertable(true);
			}
			$this->objectPropertyAccessProxy = $propertyAccessProxy;
			return;
		}
		
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('scalar', 
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}
	
	public function prepare() {
		$this->enumConfig = new EnumConfig();
		$this->getConfigurator()->addAdaption($this->enumConfig);
	}
	
	public function buildEiField(Eiu $eiu): ?EiField {
		$that = $this;
		$eiu->entry()->onValidate(function () use ($eiu, $that) {
			$type = $eiu->field()->getValue();
				
			$activeGuiFieldPaths = array();
			foreach ($that->getAssociatedGuiFieldPathMap() as $value => $guiFieldPaths) {
				if ($value == $type) {
					$activeGuiFieldPaths = $guiFieldPaths;
					continue;
				}
				
				foreach ($guiFieldPaths as $guiFieldPath) {
					if (null !== ($eiFieldWrapper = $eiu->entry()->getEiFieldAbstraction($guiFieldPath))) {
						$eiFieldWrapper->setIgnored(true);
					}
				}
			}
			
			foreach ($activeGuiFieldPaths as $guiFieldPath) {
				if (null !== ($eiFieldWrapper = $eiu->entry()->getEiFieldAbstraction($guiFieldPath))) {
					$eiFieldWrapper->setIgnored(false);
				}
			}
		});
		
		return parent::buildEiField($eiu);
	}
	
	public function createInSiField(Eiu $eiu): SiField {
		$choicesMap = $this->getOptions();
		foreach (array_values($choicesMap) as $value) {
			if (!$eiu->entry()->acceptsValue($this, $value)) {
				unset($choicesMap[$value]);
			}
		}
		
		if (empty($this->associatedGuiFieldPathMap)) {
			return SiFields::enumIn($choicesMap, $eiu->field()->getValue())
					->setMandatory($this->editConfig->isMandatory());
		}
		
		$enablerMag = new EnumTogglerMag($this->getLabelLstr(), $choicesMap, null, 
					$this->isMandatory($eiu));
		
		$that = $this;
		$eiu->entryGui()->whenReady(function () use ($eiu, $enablerMag, $that) {
			$associatedMagWrapperMap = array();
			foreach ($that->getAssociatedGuiFieldPathMap() as $value => $eiPropPaths) {
				$magWrappers = array();
				foreach ($eiPropPaths as $eiPropPath) {
					$magWrapper = $eiu->entryGui()->getMagWrapper($eiPropPath, false);
					if ($magWrapper === null) continue;
					
					$magWrappers[] = $magWrapper;
				}
				
				$associatedMagWrapperMap[$value] = $magWrappers; 
			}
			
			$enablerMag->setAssociatedMagWrapperMap($associatedMagWrapperMap);
		});
		
		
		return $enablerMag;
	}
	
	function saveSiField(SiField $siField, Eiu $eiu) {
		ArgUtils::assertTrue($siField instanceof EnumEiProp);
		$eiu->field()->setValue($siField->getValue());
	}
	
	public function createOutSiField(Eiu $eiu): SiField  {
		return SiFields::stringOut($eiu->field()->getValue());
	}
	
	public function buildManagedFilterProp(EiFrame $eiFrame): ?FilterProp  {
		return $this->buildFilterProp($eiFrame->getN2nContext());
	}
	
	public function buildFilterProp(Eiu $eiu): ?FilterProp {
		if (null !== ($entityProperty = $this->getEntityProperty())) {
			return new EnumFilterProp(CrIt::p($entityProperty), $this->getLabelLstr(), $this->getOptions());
		}
		
		return null;
	}

	public function buildSecurityFilterProp(N2nContext $n2nContext) {
		return null;
	}
		
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\SortableEiProp::buildSortProp()
	 */
	public function buildSortProp(Eiu $eiu): ?SortProp {
		if (null !== ($entityProperty = $this->getEntityProperty())) {
			return new SimpleSortProp(CrIt::p($entityProperty), $this->getLabelLstr());
		}
		
		return null;
	}
	
	public function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp {
		if (null !== ($entityProperty = $this->getEntityProperty())) {
			return new LikeQuickSearchProp(CrIt::p($this->getEntityProperty()));
		}
		
		return null;
	}

	public function isStringRepresentable(): bool {
		return true;
	}
	
	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		return StringUtils::strOf($eiu->object()->readNativValue($this));
	}

}
