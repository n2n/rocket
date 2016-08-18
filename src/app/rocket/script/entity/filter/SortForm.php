<?php

namespace rocket\script\entity\filter;

use n2n\dispatch\Dispatchable;
use n2n\persistence\orm\criteria\Criteria;
use n2n\core\DynamicTextCollection;
use n2n\l10n\Locale;
use n2n\dispatch\map\BindingConstraints;
use n2n\dispatch\val\ValEnum;
use rocket\script\entity\filter\item\SortItem;

class SortForm implements Dispatchable {
	private $sortItems = array();
	
	protected $itemIds = array();
	protected $directions = array();
	
	public function putSortItem($id, SortItem $sortItem) {
		$this->sortItems[$id] = $sortItem;
	}
	
	public function setItemIds(array $itemIds) {
		$this->itemIds = $itemIds;
	}
	
	public function getItemIds() {
		return $this->itemIds;
	}
	
	public function setDirections(array $directions) {
		$this->directions = $directions;
	}
	
	public function getDirections() {
		return $this->directions;
	}
	
	public function getItemIdOptions() {
		$options = array(null => null);
		foreach ($this->sortItems as $id => $sortItem) {
			$options[$id] = $sortItem->getLabel();
		}
		return $options;
	}
	
	public function getSortDirectionOptions(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return array(null => null,
				Criteria::ORDER_DIRECTION_ASC => $dtc->translate('script_filter_asc_label'),
				Criteria::ORDER_DIRECTION_DESC => $dtc->translate('script_filter_desc_label'));
	}
	
	private function _validation(BindingConstraints $bc) {
		$bc->val('itemIds', new ValEnum(array_keys($this->sortItems), false));
		$bc->val('directions', new ValEnum(array(Criteria::ORDER_DIRECTION_ASC, Criteria::ORDER_DIRECTION_DESC), false));
	}
	
	public function setSortDirections(array $sortDirections) {
		$this->itemIds = array();
		$this->directions = array();
		foreach ($sortDirections as $id => $direction) {
			$this->itemIds[] = $id;
			$this->directions[] = $direction;
		}
	}
	
	public function getSortDirections() {
		$sortDirections = array();
		foreach ($this->itemIds as $key => $itemId) {
			if (isset($this->sortItems[$itemId]) && isset($this->directions[$key])) {
				$sortDirections[$itemId] = $this->directions[$key];
			}
		}
		return $sortDirections;
	}
	
	public static function createFromSortModel(SortModel $sortModel) {
		$sortForm = new SortForm();
		$sortForm->sortItems = $sortModel->getSortItems();
		return $sortForm;
	}
}