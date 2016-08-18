<?php

namespace rocket\script\config\model;

use n2n\dispatch\Dispatchable;

class NavigationForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->m('save', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $scriptManager;
	private $scripts = array();
	protected $menuGroupLabels = array();
	protected $scriptMenuGroupIds = array();
	
	public function __construct(ScriptManager $scriptManager) {
		$this->scriptManager = $scriptManager;
	
		foreach ($scriptManager->getScriptIds() as $scriptId) {
			$this->scripts[$scriptId] = $scriptManager->getScriptById($scriptId);
		}
	
		$idIncrementor = 1;
		foreach ($this->scriptManager->getMenuGroups() as $menuGroup) {
			$menuGroupId = $idIncrementor++;
			$this->menuGroupLabels[$menuGroupId] = $menuGroup->getLabel();
			foreach ($menuGroup->getMenuItems() as $scriptId => $label) {
				$this->scriptMenuGroupIds[$scriptId] = $menuGroupId;
			}
		}
	}
	
	public function setMenuGroupLabels(array $menuGroupLabels) {
		$this->menuGroupLabels = $menuGroupLabels;
	}
	
	public function getMenuGroupLabels() {
		return $this->menuGroupLabels;
	}
	
	public function setScriptMenuGroupIds(array $scriptMenuGroupIds) {
		$this->scriptMenuGroupIds = $scriptMenuGroupIds;
	}
	
	public function getScriptMenuGroupIds() {
		return $this->scriptMenuGroupIds;
	}
	
	private function _validation(BindingConstraints $bc) {	
		$bc->val('menuGroupLabels', new ValNotEmpty());
	}
	
	public function save() {
		$menuGroups = array();
		foreach ($this->menuGroupLabels as $id => $label) {
			if (!mb_strlen($label)) continue;
			$menuGroups[$id] = new MenuGroup($label, array());
		}
		
		foreach ($this->scriptMenuGroupIds as $scriptId => $menuGroupId) {
			if (!isset($this->scripts[$scriptId]) || !isset($menuGroups[$menuGroupId])) continue;
			$menuGroups[$menuGroupId]->putMenuItem($scriptId, $this->scripts[$scriptId]->getLabel());
		}
		
		$this->scriptManager->setMenuGroups($menuGroups);
		$this->scriptManager->persist();
	}
}