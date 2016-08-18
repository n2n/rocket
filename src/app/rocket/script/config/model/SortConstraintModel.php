<?php
namespace rocket\script\config\model;

use rocket\script\entity\filter\SortCriteriaConstraint;
use n2n\persistence\meta\data\OrderDirection;
use n2n\dispatch\val\ValEnum;
use n2n\dispatch\map\BindingConstraints;
use n2n\dispatch\Dispatchable;

class SortConstraintModel implements Dispatchable  {
	protected $scriptFieldId;
	protected $direction;
	
	private function _validation(BindingConstraints $bc) {
		$bc->val('direction', new ValEnum(OrderDirection::getValues()));
	}
	
	public function getScriptFieldId() {
		return $this->scriptFieldId;
	}
	
	public function setScriptFieldId($scriptFieldId) {
		$this->scriptFieldId = $scriptFieldId;
	}
	
	public function getDirection() {
		return $this->direction;
	}
	
	public function setDirection($direction) {
		$this->direction = $direction;
	}
	
	public static function createFromSortConstraint(SortCriteriaConstraint $sortConstraint) {
		$sortConstraintModel = new SortConstraintModel();
		$sortConstraintModel->setScriptFieldId($sortConstraint->getPropertyName());
		$sortConstraintModel->setDirection($sortConstraint->getDirection());
		return $sortConstraintModel;
	}
	
}