<?php
namespace rocket\script\entity\command\impl\tree\controller;

use rocket\script\core\ManageState;
use rocket\script\entity\command\impl\tree\controller\TreeController;
use n2n\http\ParamGet;
use rocket\script\entity\manage\EntryManageUtils;
use n2n\http\PageNotFoundException;
use n2n\persistence\orm\NestedSetUtils;

class TreeDeleteController extends TreeController {	
	private $utils;
	
	private function _init(ManageState $manageState) {
		$this->utils = new EntryManageUtils($manageState->peakScriptState());
	}
	
	public function index($id) {
		$entity = null;
		try {
			$entity = $this->utils
					->createScriptSelectionFromEntityId($id)->getEntity();
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException();
		}
		
		$scriptState = $this->utils->getScriptState();
		$nestedSetUtils = new NestedSetUtils($scriptState->getEntityManager(), $scriptState->getContextEntityScript()
				->getTopEntityScript()->getEntityModel()->getClass());
		$nestedSetUtils->setRootIdPropertyName($this->treeRootIdScriptField->getEntityProperty()->getName());
		$nestedSetUtils->setLeftPropertyName($this->treeLeftScriptField->getEntityProperty()->getName());
		$nestedSetUtils->setRightPropertyName($this->treeRightScriptField->getEntityProperty()->getName());
		
		$nestedSetUtils->remove($entity);
	
		$this->redirect($this->utils->getScriptState()->getOverviewPath($this->getRequest()));
	}

	public function doDraft($id, $draftId, ParamGet $previewtype = null) {
		$scriptSelection = null;
		try {
			$scriptSelection = $this->utils->createScriptSelectionFromDraftId($id, $draftId);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException();
		}
	
		$this->utils->removeScriptSelection($scriptSelection);
	
		$scriptState = $this->utils->getScriptState();
		$this->redirect($this->utils->getScriptState()->getDetailPath(
				$scriptSelection->toNavPoint($previewtype)->copy(true)));
	}
}

