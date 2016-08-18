<?php

namespace rocket\script\config\model;

use n2n\dispatch\Dispatchable;
use rocket\script\entity\mask\GroupedFieldOrder;

class FieldOrderForm implements Dispatchable {
	protected $enabled;
	protected $fieldIds = array();
	protected $fieldGroupKeys = array();
	protected $groupTitles = array();
	protected $groupTypes = array();
	protected $groupParentKeys = array();
	
	public function isEnabled() {
		return $this->enabled;
	}

	public function setEnabled($enabled) {
		$this->enabled = $enabled;
	}

	public function getFieldIds() {
		return $this->fieldIds;
	}

	public function setFieldIds(array $fieldIds) {
		$this->fieldIds = $fieldIds;
	}

	public function getFieldGroupKeys() {
		return $this->fieldGroupKeys;
	}

	public function setFieldGroupKeys(array $fieldGroupKeys) {
		$this->fieldGroupKeys = $fieldGroupKeys;
	}

	public function getGroupTitles() {
		return $this->groupTitles;
	}

	public function setGroupTitles(array $groupTitles) {
		$this->groupTitles = $groupTitles;
	}

	public function getGroupTypes() {
		return $this->groupTypes;
	}

	public function setGroupTypes(array $groupTypes) {
		$this->groupTypes = $groupTypes;
	}
	
	public function getGroupTypeOptions() {
		return array(null => null, GroupedFieldOrder::ASIDE => GroupedFieldOrder::ASIDE, 
				GroupedFieldOrder::MAIN => GroupedFieldOrder::MAIN);
	}

	public function getGroupParentKeys() {
		return $this->groupParentKeys;
	}

	public function setGroupParentKeys(array $groupParentKeys) {
		$this->groupParentKeys = $groupParentKeys;
	}
	
	private function _validation() { }
	
	public function toOrder() {
		if (!$this->enabled) return null;
		
		$tba = array();
		$groups = array();
		foreach ($this->groupTitles as $key => $groupTitle) {
			$group = new GroupedFieldOrder();
			$group->setTitle($groupTitle);
			if (isset($this->groupTypes[$key])) {
				$group->setType($this->groupTypes[$key]);
			}
			if (isset($this->groupParentKeys[$key])) {
				$tba[$key] = $this->groupParentKeys[$key];
			} else {
				$tba[$key] = null;
			}
			$groups[$key] = $group;
		}
		
		$order = array();
		foreach ($this->fieldIds as $key => $fieldId) {
			if (!isset($this->fieldGroupKeys[$key])) {
				$order[] = $fieldId;
				continue;
			}
			
			$groupKey = $this->fieldGroupKeys[$key];
			if (!isset($groups[$groupKey])) continue;
			
			$groups[$groupKey]->add($fieldId);
			
			while (array_key_exists($groupKey, $tba)) {
				if (isset($groups[$tba[$groupKey]])) {
					$groups[$tba[$groupKey]]->add($groups[$groupKey]);
					$oldGroupKey = $groupKey;
					$groupKey = $tba[$groupKey];
					unset($tba[$oldGroupKey]);
				} else {
					$order[] = $groups[$groupKey];
					unset($tba[$groupKey]);
					break;
				}
			}
		}
		
		return $order;
	}
	
	public static function createFromOrder(array $order = null) {
		$fieldOrderModel = new FieldOrderForm(); 
		if ($order === null) {
			$fieldOrderModel->enabled = false;
			return $fieldOrderModel;
		}
		
		$fieldOrderModel->enabled = true;
		self::applyOrder($fieldOrderModel, $order);
		return $fieldOrderModel;
	}
	
	private static function applyOrder(FieldOrderForm $fieldOrderModel, array $order, $groupKey = null) {
		foreach ($order as $key => $field) {
			if ($field instanceof GroupedFieldOrder) {
				$fieldOrderModel->groupTitles[] = $field->getTitle();
				$fieldOrderModel->groupTypes[] = $field->getType();
				$fieldOrderModel->groupParentKeys[] = $groupKey;
				
				end($fieldOrderModel->groupTitles);
				self::applyOrder($fieldOrderModel, $field->getFieldOrder(), key($fieldOrderModel->groupTitles));
				continue;
			}
			
			$fieldOrderModel->fieldIds[] = $field;
			$fieldOrderModel->fieldGroupKeys[] = $groupKey;
		}
	}
}