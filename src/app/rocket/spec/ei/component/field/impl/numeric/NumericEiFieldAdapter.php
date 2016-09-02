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
namespace rocket\spec\ei\component\field\impl\numeric;

use rocket\spec\ei\component\field\QuickSearchableEiField;
use rocket\spec\ei\manage\critmod\filter\impl\field\StringFilterField;
use rocket\spec\ei\component\field\SortableEiField;
use rocket\spec\ei\component\field\FilterableEiField;
use n2n\l10n\N2nLocale;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\spec\ei\manage\critmod\sort\impl\SimpleSortField;
use rocket\spec\ei\manage\gui\EntrySourceInfo;
use rocket\spec\ei\component\field\impl\adapter\DraftableEiFieldAdapter;
use rocket\spec\ei\component\field\impl\numeric\conf\NumericEiFieldConfigurator;
use n2n\reflection\ArgUtils;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\EiState;
use n2n\core\container\N2nContext;
use rocket\spec\ei\EiFieldPath;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\spec\ei\manage\critmod\sort\SortField;
use rocket\spec\ei\manage\gui\FieldSourceInfo;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use rocket\spec\ei\manage\critmod\quick\impl\model\LikeQuickSearchField;

abstract class NumericEiFieldAdapter extends DraftableEiFieldAdapter 
		implements FilterableEiField, SortableEiField, QuickSearchableEiField {
	
	protected $minValue = null;
	protected $maxValue = null;
	
	public function getMinValue() {
		return $this->minValue;
	}
	
	public function setMinValue($minValue) {
		$this->minValue = $minValue;
	}
	
	public function getMaxValue() {
		return $this->maxValue;
	}
	
	public function setMaxValue($maxValue) {
		$this->maxValue = $maxValue;
	}

	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		ArgUtils::assertTrue($propertyAccessProxy !== null);
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('scalar',
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}

	public function createEiFieldConfigurator(): EiFieldConfigurator {
		return new NumericEiFieldConfigurator($this);
	}
	
	public function createOutputUiComponent(HtmlView $view, FieldSourceInfo $entrySourceInfo)  {
		$html = $view->getHtmlBuilder();
		return $html->getEsc($entrySourceInfo->getValue(EiFieldPath::from($this)));
	}
	
// 	public function createPreviewUiComponent(EiState $eiState = null, HtmlView $view, $value) {
// 		return $view->getHtmlBuilder()->getEsc($value);
// 	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\FilterableEiField::buildManagedFilterField($eiState)
	 */
	public function buildManagedFilterField(EiState $eiState) {
		return $this->buildFilterField($eiState->getN2nContext());
	}
	
	public function buildFilterField(N2nContext $n2nContext) {
		return new StringFilterField(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}
	
	public function buildEiMappingFilterField(N2nContext $n2nContext) {
		return null;
	}
	
	public function buildManagedSortField(EiState $eiState) {
		return $this->buildSortField($eiState->getN2nContext());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\SortableEiField::createGlobalSortField()
	 * @return SortField
	 */
	public function buildSortField(N2nContext $n2nContext) {
		return new SimpleSortField(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}
	
	public function buildQuickSearchField(EiState $eiState) {
		return new LikeQuickSearchField(CrIt::p($this->getEntityProperty()));
	}

// 	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
// 			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
// 		return $view->getFormHtmlBuilder()->getInputField($propertyPath, 
// 				array('class' => 'rocket-preview-inpage-component'));
// 	}

	public function isStringRepresentable(): bool {
		return true;
	}
	
	public function buildIdentityString(EiObject $eiObject, N2nLocale $n2nLocale) {
		return $this->read($eiObject);
	}
}
