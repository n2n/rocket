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
namespace rocket\spec\ei\component\field\impl\bool;

use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\spec\ei\component\field\SortableEiField;
use rocket\spec\ei\component\field\FilterableEiField;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\spec\ei\manage\EiState;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\critmod\sort\impl\SimpleSortField;
use rocket\spec\ei\manage\gui\EntrySourceInfo;
use n2n\web\dispatch\mag\MagCollection;
use rocket\spec\ei\component\field\impl\adapter\DraftableEiFieldAdapter;
use n2n\reflection\ArgUtils;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\manage\gui\FieldSourceInfo;
use rocket\spec\ei\manage\critmod\filter\impl\field\BoolFilterField;
use rocket\spec\ei\manage\EiObject;

class BooleanEiField extends DraftableEiFieldAdapter implements FilterableEiField, SortableEiField {

	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('scalar',
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}
	
	public function isMandatory(FieldSourceInfo $entrySourceInfo): bool {
		return false;
	}
	
	public function read(EiObject $eiObject) {
		return (bool) parent::read($eiObject);
	}
	
// 	public function createMagCollection() {
// 		$magCollection = new MagCollection();
// 		$this->applyDisplayOptions($magCollection);
// 		$this->applyDraftOptions($magCollection);
// 		$this->applyEditOptions($magCollection, true, true, false);
// 		$this->applyTranslationMags($magCollection);
		
// 		return $magCollection;
// 	}
	
	public function createOutputUiComponent(HtmlView $view, FieldSourceInfo $entrySourceInfo)  {
		$value = $this->getObjectPropertyAccessProxy()->getValue(
				$entrySourceInfo->getEiMapping()->getEiSelection()->getLiveObject());
		if ($value) {
			return new HtmlElement('i', array('class' => 'fa fa-check'), '');
		}
		return new HtmlElement('i', array('class' => 'fa fa-check-empty'), '');
	}
	
	public function createMag(string $propertyName, FieldSourceInfo $entrySourceInfo): Mag {
		return new BoolMag($propertyName, $this->getLabelLstr(), true);
	}

	public function buildManagedFilterField(EiState $eiState) {
		return $this->buildFilterField($eiState->getN2nContext());
	}
	
	public function buildFilterField(N2nContext $n2nContext) {
		return $this->buildEiMappingFilterField($n2nContext);
	}
	
	public function buildEiMappingFilterField(N2nContext $n2nContext) {
		return new BoolFilterField(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}
	
	public function buildManagedSortField(EiState $eiState) {
		return $this->buildSortField($eiState->getN2nContext());
	}
	
	public function buildSortField(N2nContext $n2nContext) {
		return new SimpleSortField(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}
}
