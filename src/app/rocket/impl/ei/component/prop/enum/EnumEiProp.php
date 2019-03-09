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

use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\impl\web\ui\view\html\HtmlView;
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
use n2n\web\dispatch\mag\Mag;
use rocket\ei\util\Eiu;
use rocket\ei\EiPropPath;
use rocket\impl\ei\component\prop\enum\conf\EnumEiPropConfigurator;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\manage\critmod\quick\impl\LikeQuickSearchProp;
use rocket\ei\manage\gui\GuiFieldPath;
use n2n\impl\web\dispatch\mag\model\group\EnumTogglerMag;
use rocket\ei\manage\critmod\filter\FilterProp;
use rocket\ei\manage\critmod\sort\SortProp;
use rocket\ei\manage\critmod\quick\QuickSearchProp;
use rocket\ei\manage\entry\EiField;
use n2n\util\StringUtils;
use n2n\util\type\TypeConstraints;
use n2n\impl\persistence\orm\property\IntEntityProperty;

class EnumEiProp extends DraftablePropertyEiPropAdapter implements FilterableEiProp, SortableEiProp, 
		QuickSearchableEiProp {
	
	private $options = array();
	private $associatedGuiFieldPathMap = array();
	
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
	
	public function setOptions(array $options) {
		ArgUtils::valArray($options, 'scalar');
		$this->options = $options;
	}
	
	public function getOptions() {
		return $this->options;
	}
	
	public function createEiPropConfigurator() : EiPropConfigurator {
		return new EnumEiPropConfigurator($this);
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
	
	public function createMag(Eiu $eiu): Mag {
		$choicesMap = $this->getOptions();
		foreach (array_values($choicesMap) as $value) {
			if (!$eiu->entry()->acceptsValue($this, $value)) {
				unset($choicesMap[$value]);
			}
		}
		
		if (empty($this->associatedGuiFieldPathMap)) {
			return new EnumMag($this->getLabelLstr(), $choicesMap, null, 
					$this->isMandatory($eiu));
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
	
	public function createUiComponent(HtmlView $view, Eiu $eiu)  {
		$html = $view->getHtmlBuilder();
		$options = $this->getOptions();
		$value = $eiu->field()->getValue(EiPropPath::from($this));
		if (isset($options[$value])) {
			return $html->getEsc($options[$value]);
		}
		return $html->getEsc($value);
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
	
	public function setAssociatedGuiFieldPathMap(array $associatedGuiFieldPathMap) {
		ArgUtils::valArray($associatedGuiFieldPathMap, 
				TypeConstraint::createArrayLike('array', false, TypeConstraint::createSimple(GuiFieldPath::class)));
		$this->associatedGuiFieldPathMap = $associatedGuiFieldPathMap;
	}
	
	/**
	 * @return array
	 */
	public function getAssociatedGuiFieldPathMap() {
		return $this->associatedGuiFieldPathMap;
	}
}
