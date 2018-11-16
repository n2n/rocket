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

use n2n\reflection\ArgUtils;
use n2n\reflection\property\TypeConstraint;
use n2n\reflection\property\AccessProxy;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use rocket\ei\manage\EiObject;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\web\dispatch\mag\Mag;
use rocket\ei\util\Eiu;
use rocket\ei\EiPropPath;
use rocket\impl\ei\component\prop\enum\conf\EnumEiPropConfigurator;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\manage\critmod\quick\impl\LikeQuickSearchProp;
use rocket\ei\manage\gui\GuiPropPath;
use n2n\impl\web\dispatch\mag\model\group\EnumTogglerMag;
use rocket\ei\manage\critmod\filter\FilterProp;
use rocket\ei\manage\critmod\sort\SortProp;
use rocket\ei\manage\critmod\quick\QuickSearchProp;
use rocket\ei\manage\entry\EiField;

class EnumEiProp extends DraftablePropertyEiPropAdapter implements FilterableEiProp, SortableEiProp, 
		QuickSearchableEiProp {
	
	private $options = array();
	private $associatedGuiPropPathMap = array();
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty === null || $entityProperty instanceof ScalarEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		ArgUtils::assertTrue($propertyAccessProxy !== null);
		
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
				
			$activeGuiPropPaths = array();
			foreach ($that->getAssociatedGuiPropPathMap() as $value => $eiPropPaths) {
				if ($value == $type) {
					$activeGuiPropPaths = $eiPropPaths;
					continue;
				}
				
				foreach ($eiPropPaths as $eiPropPath) {
					if (null !== ($eiFieldWrapper = $eiu->entry()->getEiFieldWrapperByGuiPropPath($eiPropPath))) {
						$eiFieldWrapper->setIgnored(true);
					}
				}
			}
			
			foreach ($activeGuiPropPaths as $eiPropPath) {
				if (null !== ($eiFieldWrapper = $eiu->entry()->getEiFieldWrapperByGuiPropPath($eiPropPath))) {
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
		
		if (empty($this->associatedGuiPropPathMap)) {
			return new EnumMag($this->getLabelLstr(), $choicesMap, null, 
					$this->isMandatory($eiu));
		}
		
		$enablerMag = new EnumTogglerMag($this->getLabelLstr(), $choicesMap, null, 
					$this->isMandatory($eiu));
		
		$that = $this;
		$eiu->entryGui()->whenReady(function () use ($eiu, $enablerMag, $that) {
			$associatedMagWrapperMap = array();
			foreach ($that->getAssociatedGuiPropPathMap() as $value => $eiPropPaths) {
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
	
	public function createOutputUiComponent(HtmlView $view, Eiu $eiu)  {
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
		return $this->read($eiu);
	}
	
	public function setAssociatedGuiPropPathMap(array $associatedGuiPropPathMap) {
		ArgUtils::valArray($associatedGuiPropPathMap, 
				TypeConstraint::createArrayLike('array', false, TypeConstraint::createSimple(GuiPropPath::class)));
		$this->associatedGuiPropPathMap = $associatedGuiPropPathMap;
	}
	
	/**
	 * @return array
	 */
	public function getAssociatedGuiPropPathMap() {
		return $this->associatedGuiPropPathMap;
	}
}