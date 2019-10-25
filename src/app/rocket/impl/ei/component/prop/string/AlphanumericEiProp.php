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

use rocket\ei\component\prop\QuickSearchableEiProp;
use rocket\ei\util\filter\prop\StringFilterProp;
use rocket\ei\component\prop\SortableEiProp;
use rocket\ei\component\prop\FilterableEiProp;
use rocket\ei\manage\critmod\sort\impl\SimpleSortProp;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use rocket\ei\manage\frame\EiFrame;
use n2n\core\container\N2nContext;
use rocket\ei\EiPropPath;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\ScalarEiProp;
use rocket\ei\component\prop\GenericEiProp;
use rocket\ei\manage\generic\CommonGenericEiProperty;
use rocket\ei\manage\generic\CommonScalarEiProperty;
use rocket\ei\manage\critmod\quick\impl\LikeQuickSearchProp;
use rocket\ei\manage\critmod\filter\FilterProp;
use rocket\ei\manage\critmod\sort\SortProp;
use rocket\ei\manage\generic\GenericEiProperty;
use rocket\ei\manage\critmod\quick\QuickSearchProp;
use rocket\ei\manage\generic\ScalarEiProperty;
use n2n\impl\persistence\orm\property\StringEntityProperty;
use rocket\si\content\SiField;
use rocket\impl\ei\component\prop\meta\config\AddonConfig;
use rocket\impl\ei\component\prop\string\conf\AlphanumericConfig;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;

abstract class AlphanumericEiProp extends DraftablePropertyEiPropAdapter implements FilterableEiProp, 
		SortableEiProp, QuickSearchableEiProp, ScalarEiProp, GenericEiProp {
	/**
	 * @var int|null
	 */
	private $minlength;
	/**
	 * @var int|null
	 */
	private $maxlength;
	/**
	 * @var AlphanumericConfig
	 */
	private $alphanumericConfig;
	/**
	 * @var AddonConfig
	 */
	private $addonConfig;

	function __construct() {
		parent::__construct();

		$this->alphanumericConfig = new AlphanumericConfig();
		$this->addonConfig = new AddonConfig();
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\string\conf\AlphanumericConfig
	 */
	protected function getAlphanumericConfig() {
		return $this->alphanumericConfig;
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\meta\config\AddonConfig
	 */
	protected function getAddonConfig() {
		return $this->addonConfig;
	}
	
	protected function isEntityPropertyRequired(): bool {
		return false;
	}
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		if ($entityProperty !== null && !($entityProperty instanceof ScalarEntityProperty 
				|| $entityProperty instanceof StringEntityProperty)) {
			throw new \InvalidArgumentException();
		}

		parent::setEntityProperty($entityProperty);
	}

	protected function adaptConfigurator(AdaptableEiPropConfigurator $configurator) {
		$configurator->addAdaption($this->alphanumericConfig);
		$configurator->addAdaption($this->addonConfig);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldDisplayable::createUiComponent()
	 */
	public function createOutSiField(Eiu $eiu): SiField  {
		return $view->getHtmlBuilder()->getEsc($eiu->entry()->getEiEntry()->getValue(
				EiPropPath::from($this)));
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
	
	public function buildSecurityFilterProp(N2nContext $n2nContext) {
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
		if (null !== ($entityProperty = $this->getEntityProperty(false))) {
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
}
