<?php
namespace rocket\script\entity\command\impl\tree\controller;

use rocket\core\model\Breadcrumb;
use rocket\script\core\ManageState;
use rocket\core\model\RocketState;
use n2n\core\DynamicTextCollection;
use rocket\script\entity\manage\EntryManageUtils;
use rocket\script\entity\command\impl\tree\model\TreeAddModel;
use n2n\http\PageNotFoundException;
use rocket\script\entity\command\impl\common\model\EntryCommandViewModel;

class TreeAddController extends TreeController {
	private $rocketState;
	private $dtc;
	private $utils;
	
	private function _init(ManageState $manageState, RocketState $rocketState, DynamicTextCollection $dtc) {
		$this->rocketState = $rocketState;
		$this->dtc = $dtc;
		$this->utils = new EntryManageUtils($manageState->peakScriptState());
	}
	
	public function index(ManageState $manageState, $parentId = null) {
		$entryForm = $this->utils->createEntryForm();
		$entryManager = $this->utils->createEntryManager();
		
		$treeAddModel = new TreeAddModel($entryManager, $entryForm, 
				$this->treeRootIdScriptField->getEntityProperty()->getEntityModel(),
				$this->treeRootIdScriptField->getEntityProperty()->getName(),
				$this->treeLeftScriptField->getEntityProperty()->getName(), 
				$this->treeRightScriptField->getEntityProperty()->getName());
		if ($parentId !== null) {
			try {
				$treeAddModel->setParentEntity($this->utils->createScriptSelectionFromEntityId($parentId));
			} catch (\InvalidArgumentException $e) {
				throw new PageNotFoundException(null, null, $e);
			}
		}
		
		if (is_object($scriptSelection = $this->dispatch($treeAddModel, 'create'))) {
			$this->redirect($this->utils->getScriptState()->getDetailPath($this->getRequest(), 
					$scriptSelection->toNavPoint()));
			return;
		}
		
		$this->applyBreadcrumbs($treeAddModel);
		$this->forward('script\entity\command\impl\common\view\add.html', array('addModel' => $treeAddModel,
				'entryViewInfo' => new EntryCommandViewModel($treeAddModel, true, 
						null, $this->dtc->translate('script_cmd_tree_add_title'))));
	}
	
	private function applyBreadcrumbs() {
		$scriptState = $this->utils->getScriptState();
		$request = $this->getRequest();
		
		if (!$scriptState->isOverviewDisabled()) {
			$this->rocketState->addBreadcrumb(
					$scriptState->createOverviewBreadcrumb($request));
		}
		
		$commandId = $scriptState->getExecutedScriptCommand()->getId();
		
		if ($scriptState->hasScriptSelection()) {
			$this->rocketState->addBreadcrumb($scriptState->createDetailBreadcrumb($request));
			
			$breadcrumbPath = $request->getControllerContextPath($scriptState->getControllerContext(),
					array($commandId, $scriptState->getScriptSelection()->getId()));
			$breadcrumbLabel = $this->dtc->translate('script_cmd_tree_add_child_breadcrumb');
			$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
			return;
		}
		
		$breadcrumbPath = $request->getControllerContextPath($scriptState->getControllerContext(), array($commandId));
		$breadcrumbLabel = $this->dtc->translate('script_cmd_tree_add_root_breadcrumb');
		$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
	}
	
}