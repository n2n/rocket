<?php

namespace rocket\script\core\extr;

use rocket\script\entity\filter\data\FilterData;

class ScriptMaskExtraction {
	private $id;
	private $label;
	private $pluralLabel;
	private $knownStringPattern;
	private $draftDisabled;
	private $translationDisabled;
	private $listFieldOrder;
	private $entryFieldOrder;
	private $detailFieldOrder;
	private $editFieldOrder;
	private $addFieldOrder;
	private $commandIds;
	private $overviewCommandId;
	private $entryDetailCommandId;
	private $entryAddCommandId;
	private $partialControlOrder = array();
	private $overallControlOrder = array();
	private $entryControlOrder = array();
	private $filterData;
	private $defaultSortDirections = null;
	private $subMaskIds = array();
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
	}

	public function getPluralLabel() {
		return $this->pluralLabel;
	}

	public function setPluralLabel($pluralLabel) {
		$this->pluralLabel = $pluralLabel;
	}

	public function getKnownStringPattern() {
		return $this->knownStringPattern;
	}

	public function setKnownStringPattern($knownStringPattern) {
		$this->knownStringPattern = $knownStringPattern;
	}

	public function isDraftDisabled() {
		return $this->draftDisabled;
	}

	public function setDraftDisabled($draftDisabled) {
		$this->draftDisabled = $draftDisabled;
	}

	public function isTranslationDisabled() {
		return $this->translationDisabled;
	}

	public function setTranslationDisabled($translationDisabled) {
		$this->translationDisabled = $translationDisabled;
	}

	public function getListFieldOrder() {
		return $this->listFieldOrder;
	}

	public function setListFieldOrder(array $listFieldOrder = null) {
		$this->listFieldOrder = $listFieldOrder;
	}

	public function getEntryFieldOrder() {
		return $this->entryFieldOrder;
	}

	public function setEntryFieldOrder(array $entryFieldOrder = null) {
		$this->entryFieldOrder = $entryFieldOrder;
	}

	public function getDetailFieldOrder() {
		return $this->detailFieldOrder;
	}

	public function setDetailFieldOrder(array $detailFieldOrder = null) {
		$this->detailFieldOrder = $detailFieldOrder;
	}

	public function getEditFieldOrder() {
		return $this->editFieldOrder;
	}

	public function setEditFieldOrder(array $editFieldOrder = null) {
		$this->editFieldOrder = $editFieldOrder;
	}

	public function getAddFieldOrder() {
		return $this->addFieldOrder;
	}

	public function setAddFieldOrder(array $addFieldOrder = null) {
		$this->addFieldOrder = $addFieldOrder;
	}

	public function getCommandIds() {
		return $this->commandIds;
	}
	
	public function setCommandIds(array $commandIds = null) {
		$this->commandIds = $commandIds;	}
	
	public function getOverviewCommandId() {
		return $this->overviewCommandId;
	}

	public function setOverviewCommandId($overviewCommandId) {
		$this->overviewCommandId = $overviewCommandId;
	}

	public function getEntryDetailCommandId() {
		return $this->entryDetailCommandId;
	}

	public function setEntryDetailCommandId($entryDetailCommandId) {
		$this->entryDetailCommandId = $entryDetailCommandId;
	}

	public function getEntryAddCommandId() {
		return $this->entryAddCommandId;
	}

	public function setEntryAddCommandId($entryAddCommandId) {
		$this->entryAddCommandId = $entryAddCommandId;
	}

	public function getPartialControlOrder() {
		return $this->partialControlOrder;
	}

	public function setPartialControlOrder(array $partialControlOrder) {
		$this->partialControlOrder = $partialControlOrder;
	}

	public function getOverallControlOrder() {
		return $this->overallControlOrder;
	}

	public function setOverallControlOrder(array $overallControlOrder) {
		$this->overallControlOrder = $overallControlOrder;
	}

	public function getEntryControlOrder() {
		return $this->entryControlOrder;
	}

	public function setEntryControlOrder(array $entryControlOrder) {
		$this->entryControlOrder = $entryControlOrder;
	}

	public function getFilterData() {
		return $this->filterData;
	}

	public function setFilterData(FilterData $filterData = null) {
		$this->filterData = $filterData;
	}
	
	public function getDefaultSortDirections() {
		return $this->defaultSortDirections;
	}
	
	public function setDefaultSortDirections(array $defaultSortDirections = null) {
		$this->defaultSortDirections = $defaultSortDirections;
	}
	
	public function getSubMaskIds() {
		return $this->subMaskIds;
	}

	public function setSubMaskIds($subMaskIds) {
		$this->subMaskIds = $subMaskIds;
	}
}