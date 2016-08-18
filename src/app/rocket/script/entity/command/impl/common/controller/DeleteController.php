<?php
namespace rocket\script\entity\command\impl\common\controller;

use rocket\script\core\ManageState;
use rocket\script\entity\EntityScript;
use n2n\http\ControllerAdapter;
use n2n\core\DynamicTextCollection;
use n2n\http\ParamGet;
use n2n\http\PageNotFoundException;
use rocket\script\entity\manage\EntryManageUtils;

class DeleteController extends ControllerAdapter {
	private $dtc;
	private $utils;
	
	private function _init(ManageState $manageState, DynamicTextCollection $dtc) {
		$this->dtc = $dtc;
		$this->utils = new EntryManageUtils($manageState->peakScriptState());
	}
	
	public function index($id) {
		try {
			$this->utils->removeScriptSelection($this->utils
					->createScriptSelectionFromEntityId($id));
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException();
		}

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