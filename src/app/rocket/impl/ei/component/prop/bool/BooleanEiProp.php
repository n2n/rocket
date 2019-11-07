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
namespace rocket\impl\ei\component\prop\bool;

use n2n\impl\persistence\orm\property\BoolEntityProperty;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\property\AccessProxy;
use n2n\util\type\ArgUtils;
use n2n\util\type\TypeConstraint;
use rocket\ei\component\prop\FilterableEiProp;
use rocket\ei\component\prop\SecurityFilterEiProp;
use rocket\ei\component\prop\SortableEiProp;
use rocket\ei\manage\critmod\filter\FilterProp;
use rocket\ei\manage\critmod\sort\SortProp;
use rocket\ei\manage\critmod\sort\impl\SimpleSortProp;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\security\filter\SecurityFilterProp;
use rocket\ei\util\Eiu;
use rocket\ei\util\filter\prop\BoolFilterProp;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use rocket\si\content\impl\SiFields;
use rocket\si\content\SiField;
use rocket\impl\ei\component\prop\bool\conf\BooleanConfig;

class BooleanEiProp extends DraftablePropertyEiPropAdapter implements FilterableEiProp, SortableEiProp, SecurityFilterEiProp {
	private $booleanConfig;
	
	function __construct() {
		$this->booleanConfig = new BooleanConfig();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter::createEiPropConfigurator()
	 */
	function prepare() {
		$this->getConfigurator()->addAdaption($this->booleanConfig);
	}
	
	function isEntityPropertyRequired(): bool {
		return false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\PropertyEiPropAdapter::setEntityProperty()
	 */
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof BoolEntityProperty 
				|| $entityProperty instanceof ScalarEntityProperty || $entityProperty === null);
		
		$this->entityProperty = $entityProperty;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\PropertyEiPropAdapter::setObjectPropertyAccessProxy()
	 */
	public function setObjectPropertyAccessProxy(?AccessProxy $propertyAccessProxy) {
// 		if ($propertyAccessProxy === null) {
// 			return;
// 		}
		ArgUtils::assertTrue(null !== $propertyAccessProxy);
		
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('bool',
					$propertyAccessProxy->getConstraint()->allowsNull(), true));
		parent::setObjectPropertyAccessProxy($propertyAccessProxy);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter::read()
	 */
	public function readEiFieldValue(Eiu $eiu) {
		return (bool) parent::readEiFieldValue($eiu);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldDisplayable::createUiComponent()
	 */
	public function createOutSiField(Eiu $eiu): SiField  {
		$value = $this->getObjectPropertyAccessProxy()->getValue(
				$eiu->entry()->getEiEntry()->getEiObject()->getLiveObject());
		if ($value) {
			return new HtmlElement('i', array('class' => 'fa fa-check'), '');
		}
		return new HtmlElement('i', array('class' => 'fa fa-check-empty'), '');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldEditable::createMag()
	 */
	public function createInSiField(Eiu $eiu): SiField {
		$mapCb = function ($guiFieldPath) { return (string) $guiFieldPath; };
		
		return SiFields::boolIn($eiu->field()->getValue())
				->setMandatory($this->editConfig->isMandatory())
				->setOnAssociatedFieldIds(array_map($mapCb, $this->booleanConfig->getOnAssociatedGuiFieldPaths()))
				->setOffAssociatedFieldIds(array_map($mapCb, $this->booleanConfig->getOffAssociatedGuiFieldPaths()));
	}
	
	
	public function saveSiField(SiField $siField, Eiu $eiu) {
		$eiu->field()->setValue($siField->getValue());
	}
	
	public function buildEiField(Eiu $eiu): ?EiField {
		$eiu->entry()->onValidate(function () use ($eiu) {
			$activeGuiFieldPaths = array();
			$notactiveGuiFieldPaths = array();
			
			if ($eiu->field()->getValue()) {
				$activeGuiFieldPaths = $this->getOnAssociatedGuiFieldPaths();
				$notactiveGuiFieldPaths = $this->getOffAssociatedGuiFieldPaths();
			} else {
				$activeGuiFieldPaths = $this->getOffAssociatedGuiFieldPaths();
				$notactiveGuiFieldPaths = $this->getOnAssociatedGuiFieldPaths();
			}
			
			foreach ($notactiveGuiFieldPaths as $eiPropPath) {
				if (null !== ($eiFieldWrapper = $eiu->entry()->getEiFieldAbstraction($eiPropPath))) {
					$eiFieldWrapper->setIgnored(true);
				}
			}
			
			foreach ($activeGuiFieldPaths as $eiPropPath) {
				if (null !== ($eiFieldWrapper = $eiu->entry()->getEiFieldAbstraction($eiPropPath))) {
					$eiFieldWrapper->setIgnored(false);
				}
			}
		});
			
		return parent::buildEiField($eiu);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FilterableEiProp::buildFilterProp()
	 */
	public function buildFilterProp(Eiu $eiu): ?FilterProp {
		return $this->buildSecurityFilterProp($eiu);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FilterableEiProp::buildFilterProp()
	 */
	public function buildSecurityFilterProp(Eiu $eiu): ?SecurityFilterProp {
		if (null !== ($entityProperty = $this->getEntityProperty())) {
			return new BoolFilterProp(CrIt::p($entityProperty), $this->getLabelLstr());
		}
		
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
}
