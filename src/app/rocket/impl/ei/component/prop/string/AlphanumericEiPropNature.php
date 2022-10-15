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
namespace rocket\impl\ei\component\prop\string;

use rocket\ei\util\filter\prop\StringFilterProp;
 

use rocket\ei\manage\critmod\sort\impl\SimpleSortProp;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropNatureAdapter;
use rocket\ei\manage\frame\EiFrame;
use n2n\core\container\N2nContext;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use rocket\ei\util\Eiu;


use rocket\ei\manage\generic\CommonGenericEiProperty;
use rocket\ei\manage\generic\CommonScalarEiProperty;
use rocket\ei\manage\critmod\quick\impl\LikeQuickSearchProp;
use rocket\ei\manage\critmod\filter\FilterProp;
use rocket\ei\manage\critmod\sort\SortProp;
use rocket\ei\manage\generic\GenericEiProperty;
use rocket\ei\manage\critmod\quick\QuickSearchProp;
use rocket\ei\manage\generic\ScalarEiProperty;
use rocket\ei\manage\idname\IdNameProp;
use n2n\util\StringUtils;
use rocket\ei\util\factory\EifGuiField;
use rocket\si\content\impl\SiFields;
use rocket\impl\ei\component\prop\meta\AddonAdapter;
use rocket\impl\ei\component\prop\meta\AddonEiPropNature;
use rocket\impl\ei\component\prop\adapter\QuickSearchTrait;
use n2n\reflection\property\PropertyAccessProxy;
use n2n\util\type\TypeConstraints;

abstract class AlphanumericEiPropNature extends DraftablePropertyEiPropNatureAdapter implements AddonEiPropNature {
	use AddonAdapter, QuickSearchTrait;

	/**
	 * @var int|null
	 */
	private $minlength;
	/**
	 * @var int|null
	 */
	private $maxlength;

	function __construct(PropertyAccessProxy $propertyAccessProxy) {
		parent::__construct($propertyAccessProxy->createRestricted(TypeConstraints::string(true)));
	}

	/**
	 * @return int|null
	 */
	function getMinlength() {
		return $this->minlength;
	}

	/**
	 * @param int|null $minlength
	 */
	function setMinlength(?int $minlength) {
		$this->minlength = $minlength;
	}

	/**
	 * @return int|null
	 */
	function getMaxlength() {
		return $this->maxlength;
	}

	/**
	 * @param int|null $maxlength
	 */
	function setMaxlength(?int $maxlength) {
		$this->maxlength = $maxlength;
	}
	
	public function createOutEifGuiField(Eiu $eiu): EifGuiField  {
		return $eiu->factory()->newGuiField(SiFields::stringOut(StringUtils::strOf($eiu->field()->getValue(), true)));
	}
	
	function createInEifGuiField(Eiu $eiu): EifGuiField {
		$siField = SiFields::stringIn($eiu->field()->getValue())
				->setMandatory($this->isMandatory())
				->setMinlength($this->getMinlength())
				->setMaxlength($this->getMaxlength())
				->setPrefixAddons($this->getPrefixSiCrumbGroups())
				->setSuffixAddons($this->getSuffixSiCrumbGroups());
		
		return $eiu->factory()->newGuiField($siField)
				->setSaver(function () use ($siField, $eiu) {
					$eiu->field()->setValue($siField->getValue());
				});
	}
	
	public function buildManagedFilterProp(EiFrame $eiFrame): ?FilterProp  {
		return $this->buildFilterProp($eiFrame->getN2nContext());
	}

	public function buildFilterProp(Eiu $eiu): ?FilterProp {
		if (null !== ($entityProperty = $this->getEntityProperty(false))) {
			return new StringFilterProp(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
		}

		return null;
	}

	public function buildSortProp(Eiu $eiu): ?SortProp {
		if (null !== ($entityProperty = $this->getEntityProperty(false))) {
			return new SimpleSortProp(CrIt::p($entityProperty), $this->getLabelLstr());
		}

		return null;
	}
	
	public function getSortItemFork() {
		return null;
	}
	
	public function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp {
		if ($this->isQuickSerachable()
				&& null !== ($entityProperty = $this->getEntityProperty())) {
			return new LikeQuickSearchProp(CrIt::p($entityProperty));
		}
		
		return null;
	}
	
	public function getGenericEiProperty(): ?GenericEiProperty {
		if ($this->entityProperty === null) return null;
		
		return new CommonGenericEiProperty($this, CrIt::p($this->entityProperty));
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\ScalarEiProp::buildScalarValue()
	 */
	public function getScalarEiProperty(): ?ScalarEiProperty {
		return new CommonScalarEiProperty($this);
	}
	
	function buildIdNameProp(Eiu $eiu): ?IdNameProp  {
		return $eiu->factory()->newIdNameProp(function (Eiu $eiu) {
			return StringUtils::reduce((string) $eiu->object()->readNativValue($eiu->prop()->getEiProp()), 30, '...');
		})->toIdNameProp();
	}
}
