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
namespace rocket\spec\ei\component\field\impl\enum;

use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\spec\ei\manage\critmod\filter\impl\field\FilterFieldAdapter;
use rocket\spec\ei\manage\critmod\filter\impl\model\SimpleComparatorConstraint;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\component\field\FilterableEiField;
use rocket\spec\ei\component\field\SortableEiField;
use rocket\spec\ei\component\field\QuickSearchableEiField;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\preview\PreviewModel;
use n2n\web\dispatch\map\PropertyPath;
use n2n\l10n\N2nLocale;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\critmod\filter\impl\field\EnumFilterField;
use rocket\spec\ei\manage\critmod\sort\impl\SimpleSortField;
use rocket\spec\ei\manage\critmod\filter\impl\field\EnumSelectorItem;
use rocket\spec\ei\manage\gui\EntrySourceInfo;
use n2n\reflection\ArgUtils;
use n2n\reflection\property\TypeConstraint;
use n2n\reflection\property\AccessProxy;
use rocket\spec\ei\component\field\impl\adapter\DraftableEiFieldAdapter;
use rocket\spec\ei\manage\EiObject;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\manage\gui\FieldSourceInfo;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\component\field\impl\enum\conf\EnumEiFieldConfigurator;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use rocket\spec\ei\manage\critmod\quick\impl\model\LikeQuickSearchField;

class EnumEiField extends DraftableEiFieldAdapter implements FilterableEiField, SortableEiField, 
		QuickSearchableEiField {
	
	private $options = array();
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
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
	
	public function createEiFieldConfigurator() : EiFieldConfigurator {
		return new EnumEiFieldConfigurator($this);
	}
	
	public function createMag(string $propertyName, FieldSourceInfo $entrySourceInfo): Mag {
		$choicesMap = $this->getOptions();
		foreach ($choicesMap as $value => $label) {
			if (!$entrySourceInfo->getEiMapping()->acceptsValue($this, $value)) {
				unset($choicesMap[$value]);
			}
		}
		return new EnumMag($propertyName, $this->getLabelLstr(), $choicesMap, null, 
				$this->isMandatory($entrySourceInfo));
	}
	
	public function createOutputUiComponent(HtmlView $view, FieldSourceInfo $entrySourceInfo)  {
		$html = $view->getHtmlBuilder();
		$options = $this->getOptions();
		$value = $entrySourceInfo->getValue(EiFieldPath::from($this));
		if (isset($options[$value])) {
			return $html->getEsc($options[$value]);
		}
		return $html->getEsc($value);
	}
	
	public function buildManagedFilterField(EiState $eiState) {
		return $this->buildFilterField($eiState->getN2nContext());
	}
	
	public function buildFilterField(N2nContext $n2nContext) {
		return new EnumFilterField(CrIt::p($this->getEntityProperty()), $this->getLabelLstr(), $this->getOptions());
	}

	public function buildEiMappingFilterField(N2nContext $n2nContext) {
		return new EnumSelectorItem($this->getEntityProperty()->getName(), $this->getLabelLstr(),
				FilterFieldAdapter::createOperatorOptions($n2nContext->getN2nLocale()), $this->getOptions());
	}
	
	public function buildManagedSortField(EiState $eiState) {
		return $this->buildSortField($eiState->getN2nContext());
	}
	
	public function buildSortField(N2nContext $n2nContext) {
		return new SimpleSortField(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}
	
	public function buildQuickSearchField(EiState $eiState) {
		return new LikeQuickSearchField(CrIt::p($this->getEntityProperty()));
	}

	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath, HtmlView $view,\Closure $createCustomUiElementCallback = null) {
		return $view->getFormHtmlBuilder()->getSelect($propertyPath, $this->getOptions(), array('class' => 'rocket-preview-inpage-component'));
	}

	public function isStringRepresentable(): bool {
		return true;
	}
	
	public function buildIdentityString(EiObject $eiObject, N2nLocale $n2nLocale) {
		return $this->read($eiObject);
	}
}
