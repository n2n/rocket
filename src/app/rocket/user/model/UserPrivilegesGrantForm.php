<?php

namespace rocket\user\model;

use n2n\dispatch\Dispatchable;
use rocket\user\bo\UserPrivilegesGrant;
use rocket\script\entity\filter\FilterForm;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\DispatchAnnotations;
use n2n\dispatch\val\ValEnum;
use n2n\dispatch\map\BindingConstraints;
use rocket\script\entity\filter\data\FilterData;

class UserPrivilegesGrantForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES, array('names' => array('restricted', 'privileges')));
		$as->p('restrictionFilterForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
	}
	
	private $userPrivilegesGrant;
	private $privilegeOptions;
	
	private $restrictionFilterForm; 
	
	public function __construct(UserPrivilegesGrant $userPrivilegesGrant, array $privilegeOptions, 
			array $restrictionSelectorItems) {
		$this->userPrivilegesGrant = $userPrivilegesGrant;
		$this->privilegeOptions = $privilegeOptions;
		$this->restrictionFilterForm = FilterForm::createFromFilterItems($restrictionSelectorItems);
		$this->restrictionFilterForm->writeFilterData($userPrivilegesGrant->readRestrictionFilterData());
	}
	
	public function getUserPrivilegesGrant() {
		return $this->userPrivilegesGrant;
	}
	
	public function getPrivilegeOptions() {
		return $this->privilegeOptions;
	}
	
	public function getPrivileges() {
		$privileges = $this->userPrivilegesGrant->getPrivileges();
		return array_combine($privileges, $privileges);
	}
	
	public function setPrivileges(array $privileges) {
		$this->userPrivilegesGrant->setPrivileges(array_values($privileges));
	}

	public function isRestricted() {
		return $this->userPrivilegesGrant->isRestricted();
	}
	
	public function setRestricted($restricted) {
		$this->userPrivilegesGrant->setRestricted((boolean) $restricted 
				&& $this->areRestrictionsAvailable());
	}
	
	public function areRestrictionsAvailable() {
		return $this->restrictionFilterForm !== null;
	}
	
	public function getRestrictionFilterForm() {
		return $this->restrictionFilterForm;
	}
	
	public function setRestrictionFilterForm(FilterForm $restrictionFilterForm = null) {
		$this->restrictionFilterForm = $restrictionFilterForm;

		if ($restrictionFilterForm === null) {
			$this->userPrivilegesGrant->writeRestrictionFilterData(new FilterData());
		} else {
			$this->userPrivilegesGrant->writeRestrictionFilterData($restrictionFilterForm->readFilterData());
		}
	}
	
	private function _validation(BindingConstraints $bc) {
		$bc->val('privileges', new ValEnum(array_keys($this->privilegeOptions)));

// 		if ($this->restrictionFilterForm === null) {
// 			$bc->ignore('restrictionFilterForm');
// 		}
	}
}