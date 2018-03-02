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
namespace rocket\spec\config\extr;

use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\critmod\sort\SortData;
use rocket\spec\ei\manage\critmod\filter\data\FilterGroupData;
use rocket\spec\ei\mask\model\DisplayScheme;

class EiMaskExtraction {
	private $label;
	private $pluralLabel;
	private $identityStringPattern;
	private $draftingAllowed;
	private $previewControllerLookupId;
	
	private $filterData;
	private $defaultSortData;
	
	private $eiPropExtractions = array();
	private $eiCommandExtractions = array();
	
	private $displayScheme;
	
	private $overviewEiCommandId;
	private $entryDetailEiCommandId;
	private $entryEditEiCommandId;
	private $entryAddEiCommandId;
	
	/**
	 * @return string
	 */
	public function getLabel(): string {
		return $this->label;
	}
	
	/**
	 * @param string $label
	 */
	public function setLabel($label) {
		$this->label = $label;
	}
	
	/**
	 * @return string
	 */
	public function getPluralLabel() {
		return $this->pluralLabel;
	}
	
	/**
	 * @param string $pluralLabel
	 */
	public function setPluralLabel($pluralLabel) {
		$this->pluralLabel = $pluralLabel;
	}
	
	/**
	 * @return string
	 */
	public function getIconType() {
		return $this->iconType;
	}
	
	/**
	 * @param string $iconType
	 */
	public function setIconType($iconType) {
		$this->iconType = $iconType;
	}
	
	/**
	 * @return string
	 */
	public function getIdentityStringPattern() {
		return $this->identityStringPattern;
	}

	/**
	 * @param string $identityStringPattern
	 */
	public function setIdentityStringPattern($identityStringPattern) {
		$this->identityStringPattern = $identityStringPattern;
	}

	public function isDraftingAllowed() {
		$this->draftingAllowed;
	}
	
	public function setDraftingAllowed(bool $draftingAllowed = null) {
		$this->draftingAllowed = $draftingAllowed;
	}

	public function getPreviewControllerLookupId() {
		return $this->previewControllerLookupId;
	}

	public function setPreviewControllerLookupId($previewControllerLookupId) {
		$this->previewControllerLookupId = $previewControllerLookupId;
	}

	public function getFilterGroupData() {
		return $this->filterData;
	}
	
	public function setFilterGroupData(FilterGroupData $filterData = null) {
		$this->filterData = $filterData;
	}
	
	public function getDefaultSortData() {
		return $this->defaultSortData;
	}

	public function setDefaultSortData(SortData $defaultSortData = null) {
		$this->defaultSortData = $defaultSortData;
	}

	/**
	 * @return EiPropExtraction []
	 */
	public function getEiPropExtractions() {
		return $this->eiPropExtractions;
	}
	
	public function addEiPropExtraction(EiPropExtraction $eiPropExtraction) {
		$this->eiPropExtractions[] = $eiPropExtraction;
	}
	
	public function setEiPropExtractions(array $eiPropExtractions) {
		ArgUtils::valArray($eiPropExtractions, EiPropExtraction::class);
		$this->eiPropExtractions = $eiPropExtractions;	
	}
	
	public function getEiCommandExtractions() {
		return $this->eiCommandExtractions;
	}
	
	public function addEiCommandExtraction(EiComponentExtraction $configurableExtraction) {
		$this->eiCommandExtractions[] = $configurableExtraction;
	}
	
	public function setEiCommandExtraction(array $eiCommandExtractions) {
		ArgUtils::valArray($eiCommandExtractions, EiComponentExtraction::class);
		$this->eiCommandExtractions = $eiCommandExtractions;
	}
		
	/**
	 * @return DisplayScheme
	 */
	public function getDisplayScheme() {
		return $this->displayScheme;
	}
	
	/**
	 * @param DisplayScheme $displayScheme
	 */
	public function setDisplayScheme(DisplayScheme $displayScheme) {
		$this->displayScheme = $displayScheme;
	}
	
	public function getOverviewEiCommandId() {
		return $this->overviewEiCommandId;
	}

	public function setOverviewEiCommandId($overviewEiCommandId) {
		$this->overviewEiCommandId = $overviewEiCommandId;
	}

	public function getGenericDetailEiCommandId() {
		return $this->entryDetailEiCommandId;
	}

	public function setGenericDetailEiCommandId($entryDetailEiCommandId) {
		$this->entryDetailEiCommandId = $entryDetailEiCommandId;
	}

	public function getGenericEditEiCommandId() {
		return $this->entryEditEiCommandId;
	}

	public function setGenericEditEiCommandId($entryEditEiCommandId) {
		$this->entryEditEiCommandId = $entryEditEiCommandId;
	}

	public function getGenericAddEiCommandId() {
		return $this->entryAddEiCommandId;
	}

	public function setGenericAddEiCommandId($entryAddEiCommandId) {
		$this->entryAddEiCommandId = $entryAddEiCommandId;
	}
}
