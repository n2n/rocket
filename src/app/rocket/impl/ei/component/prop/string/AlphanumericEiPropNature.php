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

use rocket\op\ei\util\filter\prop\StringFilterProp;
use rocket\op\ei\manage\critmod\sort\impl\SimpleSortProp;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropNatureAdapter;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\generic\CommonGenericEiProperty;
use rocket\op\ei\manage\generic\CommonScalarEiProperty;
use rocket\op\ei\manage\critmod\filter\FilterProp;
use rocket\op\ei\manage\critmod\sort\SortProp;
use rocket\op\ei\manage\generic\GenericEiProperty;
use rocket\op\ei\manage\critmod\quick\QuickSearchProp;
use rocket\op\ei\manage\generic\ScalarEiProperty;
use rocket\op\ei\manage\idname\IdNameProp;
use n2n\util\StringUtils;
use rocket\ui\si\content\impl\SiFields;
use rocket\impl\ei\component\prop\meta\AddonAdapter;
use rocket\impl\ei\component\prop\meta\AddonEiPropNature;
use rocket\impl\ei\component\prop\adapter\trait\QuickSearchConfigTrait;
use rocket\op\ei\manage\critmod\quick\impl\QuickSearchProps;
use rocket\ui\gui\field\impl\GuiFields;
use rocket\ui\gui\field\BackableGuiField;
use rocket\ui\gui\field\impl\string\StringInGuiField;


abstract class AlphanumericEiPropNature extends DraftablePropertyEiPropNatureAdapter implements AddonEiPropNature {
	use AddonAdapter, QuickSearchConfigTrait;

	/**
	 * @var int|null
	 */
	private ?int $minlength = null;
	/**
	 * @var int|null
	 */
	private ?int $maxlength = null;



	/**
	 * @return int|null
	 */
	function getMinlength(): ?int {
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
	function getMaxlength(): ?int {
		return $this->maxlength;
	}

	/**
	 * @param int|null $maxlength
	 */
	function setMaxlength(?int $maxlength) {
		$this->maxlength = $maxlength;
	}
	
	public function buildOutGuiField(Eiu $eiu): ?BackableGuiField  {
		return GuiFields::out(SiFields::stringOut(StringUtils::strOf($eiu->field()->getValue(), true)));
	}
	
	function buildInGuiField(Eiu $eiu): ?BackableGuiField  {
		$guiField = GuiFields::stringIn(mandatory: $this->isMandatory(),
				minlength: $this->getMinlength() ?? 0, maxlength: $this->getMaxlength() ?? 255,
				prefixAddons: $this->getPrefixSiCrumbGroups(), suffixAddons: $this->getSuffixSiCrumbGroups());

//		$guiField->setModel($eiu->field()->asGuiFieldModel());

		return $guiField;
	}
	
//	public function buildManagedFilterProp(EiFrame $eiFrame): ?FilterProp  {
//		return $this->buildFilterProp($eiFrame->getN2nContext());
//	}

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

	public function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp {
		if ($this->isQuickSearchable()
				&& null !== ($entityProperty = $this->getEntityProperty())) {
			return QuickSearchProps::like(CrIt::p($entityProperty));
		}
		
		return null;
	}
	
	public function getGenericEiProperty(): ?GenericEiProperty {
		if ($this->entityProperty === null) return null;
		
		return new CommonGenericEiProperty($this, CrIt::p($this->entityProperty));
	}
	/**
	 * {}
	 * @see \rocket\op\ei\component\prop\ScalarEiProp::buildScalarValue()
	 */
	public function buildScalarEiProperty(Eiu $eiu): ?ScalarEiProperty {
		return new CommonScalarEiProperty($eiu->prop()->getPath(), $this->getLabelLstr());
	}
	
	function buildIdNameProp(Eiu $eiu): ?IdNameProp  {
		return $eiu->factory()->newIdNameProp(function (Eiu $eiu) {
			return StringUtils::reduce((string) $eiu->object()->readNativeValue(), 30, '...');
		})->toIdNameProp();
	}
}
