<?php
namespace rocket\script\entity;

use rocket\script\entity\field\SortableScriptField;
use n2n\reflection\ArgumentUtils;
use n2n\util\HashMap;

class SortModificatorCollection {
	private $fields = array();
	private $directions = array();
	private $sortConstraints;
	
	public function addSortableScriptField(SortableScriptField $field, $direction) {
		$this->fields[] = $field;
		$this->directions[] = $direction;
		$this->sortConstraints = null;
	}
	
	public function getSortableScriptFields() {
		return $this->fields;
	}
	
	public function removeSortableScriptFields() {
		$this->fields = array();
		$this->directions = array();
		$this->sortConstraints = array();
	}
	
	public function getSortConstraints() {
		if ($this->sortConstraints === null) {
			$this->sortConstraints = array();
			foreach ($this->fields as $key => $field) {
				$sortConstraint = $field->createSortCriteriaConstraint($this->directions[$key]);
				ArgumentUtils::validateReturnType($sortContraint, 'rocket\script\entity\manage\CriteriaConstraint',
						$field, 'createSortCriteriaConstraint');
				$this->sortConstraints[] = $sortConstraint;
			}
		}
		
		return $this->sortConstraints;
	}
	
	public function toFieldMap() {
		$hashMap = new HashMap();
		foreach ($this->fields as $key => $field) {
			$hashMap[$field] = $this->directions[$key]; 
		}
		return $hashMap;
	}
}