<?php
namespace rocket\user\model;

use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\DispatchAnnotations;
use n2n\dispatch\Dispatchable;
use rocket\user\bo\UserScriptGrant;
use n2n\dispatch\map\BindingConstraints;
use n2n\l10n\Locale;
use n2n\dispatch\option\impl\OptionForm;
use n2n\util\Attributes;
use rocket\script\core\Script;
use n2n\dispatch\option\OptionCollection;
use rocket\user\bo\UserPrivilegesGrant;

class UserScriptGrantForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES, array('names' => array('fullAccess')));
		$as->p('accessOptionForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
		$as->p('userPrivilegesGrantForms', DispatchAnnotations::MANAGED_DISPATCHABLE_ARRAY_PROPERTY, 
				array('creator' => function (UserScriptGrantForm $that) {
					return new UserPrivilegesGrantForm(new UserPrivilegesGrant(), 
							$that->privilegeOptions, $that->restrictionSelectorItems);
				}));
		$as->m('save', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $script;
	private $userScriptGrant;
	private $privilegeOptions;
	private $restrictionSelectorItems = array();
	
	private $accessOptionForm;
	private $userPrivilegesGrantForms = array();
	
	public function __construct(Script $script, UserScriptGrant $userScriptGrant,  
			OptionCollection $accessOptionCollection = null, array $privilegeOptions, array $restrictionSelectorItems) {
		$this->script = $script;
		$this->userScriptGrant = $userScriptGrant;
		$this->userScriptGrant->setScriptId($script->getId());
		$this->privilegeOptions = $privilegeOptions;
		$this->restrictionSelectorItems = $restrictionSelectorItems;

		if (null !== $accessOptionCollection) {
			$this->accessOptionForm = new OptionForm($accessOptionCollection, $userScriptGrant->getAccessAttributes());
		}
		
		foreach ($userScriptGrant->getPrivilegesGrants() as $privilegesGrant) {
			$this->userPrivilegesGrantForms[] = new UserPrivilegesGrantForm($privilegesGrant, 
					$this->privilegeOptions, $this->restrictionSelectorItems);
		}
	}
	
	public function getScript() {
		return $this->script; 
	}
	
	public function getUserScriptGrant() {
		return $this->userScriptGrant;
	}
	
	public function isNew() {
		return $this->userScriptGrant->getId() === null;
	}
	
	public function isUsed() {
		return $this->used;
	}
	
	public function setUsed($used) {
		$this->used = (boolean) $used;
	}
	
	public function isFullAccess() {
		return $this->userScriptGrant->isFull();
	}
	
	public function setFullAccess($fullAccess) {
		$this->userScriptGrant->setFull((boolean) $fullAccess);
	}
		
	public function areAccessOptionsAvailable() {
		return $this->accessOptionForm !== null;
	}
	
	public function getAccessOptionForm() {
		return $this->accessOptionForm;
	}
	
	public function setAccessOptionForm(OptionForm $optionForm = null) {
		$this->accessOptionForm = $optionForm;
		
		if ($optionForm === null) {
			$this->userScriptGrant->writeAccessAttributes(new Attributes());
		} else {
			$this->userScriptGrant->writeAccessAttributes($optionForm->getAttributes());
		}
	}
	
	public function getUserPrivilegesGrantForms() {
		return $this->userPrivilegesGrantForms;
	}
	
	public function setUserPrivilegesGrantForms(array $userPrivilegesGrantForms) {
		$this->userPrivilegesGrantForms = $userPrivilegesGrantForms;
	}
	
	private function _validation(BindingConstraints $bc, Locale $locale) {
// 		if ($this->accessOptionForm === null) {
// 			$bc->ignore('accessOptionForm');
// 		}
	}
	
	public function save() {
		$this->userScriptGrant->setFull(false);
		
		$privilegesGrants = new \ArrayObject();
		foreach ($this->userPrivilegesGrantForms as $grantForm) {
			$grant = $grantForm->getUserPrivilegesGrant();
			$grant->setScriptGrant($this->userScriptGrant);
			$privilegesGrants[] = $grant;
		}
		$this->userScriptGrant->setPrivilegeGrants($privilegesGrants);
	}
}