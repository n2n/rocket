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
namespace rocket\spec\ei\component\field\impl\string;

use rocket\spec\ei\component\field\QuickSearchableEiField;
use rocket\spec\ei\manage\critmod\filter\impl\field\StringFilterField;
use rocket\spec\ei\component\field\SortableEiField;
use rocket\spec\ei\component\field\FilterableEiField;
use rocket\spec\ei\manage\critmod\sort\impl\SimpleSortField;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\component\field\impl\adapter\DraftableEiFieldAdapter;
use rocket\spec\ei\manage\EiState;
use n2n\core\container\N2nContext;
use rocket\spec\ei\EiFieldPath;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\field\ScalarEiField;
use rocket\spec\ei\component\field\GenericEiField;
use rocket\spec\ei\manage\generic\CommonGenericEiProperty;
use rocket\spec\ei\manage\generic\CommonScalarEiProperty;
use rocket\spec\ei\manage\critmod\quick\impl\model\LikeQuickSearchField;

abstract class AlphanumericEiField extends DraftableEiFieldAdapter implements FilterableEiField, 
		SortableEiField, QuickSearchableEiField, ScalarEiField, GenericEiField {
	
	private $maxlength;

	public  function __construct() {
		parent::__construct();

		$this->entityPropertyRequired = false;
	}

	public function getMaxlength() {
		return $this->maxlength;
	}

	public function setMaxlength(int $maxlength = null) {
		$this->maxlength = $maxlength;
	}
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		if ($entityProperty !== null && !($entityProperty instanceof ScalarEntityProperty)) {
			throw new \InvalidArgumentException();
		}

		parent::setEntityProperty($entityProperty);
	}

	public function createOutputUiComponent(HtmlView $view, Eiu $eiu)  {
		return $view->getHtmlBuilder()->getEsc($eiu->entry()->getEiMapping()->getValue(
				EiFieldPath::from($this)));
	}

	public function buildManagedFilterField(EiState $eiState) {
		return $this->buildFilterField($eiState->getN2nContext());
	}

	public function buildFilterField(N2nContext $n2nContext) {
		if (null !== ($entityProperty = $this->getEntityProperty(false))) {
			return new StringFilterField(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
		}

		return null;
	}
	
	public function buildEiMappingFilterField(N2nContext $n2nContext) {
		return null;
	}
	
	public function buildManagedSortField(EiState $eiState) {
		return $this->buildSortField($eiState->getN2nContext());
	}
	
	public function buildSortField(N2nContext $n2nContext) {
		if (null !== ($entityProperty = $this->getEntityProperty(false))) {
			return new SimpleSortField(CrIt::p($entityProperty), $this->getLabelLstr());
		}

		return null;
	}
	
	public function getSortItemFork() {
		return null;
	}
	
	public function buildQuickSearchField(EiState $eiState) {
		if (null !== ($entityProperty = $this->getEntityProperty(false))) {
			return new LikeQuickSearchField(CrIt::p($entityProperty));
		}
		
		return null;
	}
	
	public function getGenericEiProperty() {
		if ($this->entityProperty === null) return null;
		
		return new CommonGenericEiProperty($this, CrIt::p($this->entityProperty));
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\ScalarEiField::buildScalarValue()
	 */
	public function getScalarEiProperty() {
		return new CommonScalarEiProperty($this);
	}
}