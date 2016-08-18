<?php
namespace rocket\script\entity\command\impl\common\controller;

use rocket\core\model\Breadcrumb;
use rocket\core\model\RocketState;
use n2n\core\DynamicTextCollection;
use rocket\script\core\ManageState;
use n2n\http\ControllerAdapter;
use rocket\script\entity\manage\EntryManageUtils;
use rocket\script\entity\command\impl\common\model\AddModel;
use rocket\script\entity\command\impl\common\model\EntryCommandViewModel;

class AddController extends ControllerAdapter {
	private $entityScript;
	private $dtc;
	private $rocketState;
	private $utils;
	
	private function _init(DynamicTextCollection $dtc, RocketState $rocketState, ManageState $manageState) {
		$this->dtc = $dtc;
		$this->rocketState = $rocketState;
		$this->utils = new EntryManageUtils($manageState->peakScriptState());
	}
		
	public function index(ManageState $manageState) {		
		$entryForm = $this->utils->createEntryForm();
		$entryManager = $this->utils->createEntryManager();
		$this->utils->applyToScriptState($entryForm->getMainEntryFormPart()
				->getScriptSelectionMapping()->getScriptSelection());
		
		$addModel = new AddModel($entryManager, $entryForm);
		
		if (is_object($scriptSelection = $this->dispatch($addModel, 'create'))) {
			$this->redirect($this->utils->getScriptState()->getDetailPath(
					$this->getRequest(), $scriptSelection->toNavPoint()));
			return;
		}

		$this->applyBreadcrumbs();
		$this->forward('script\entity\command\impl\common\view\add.html', array('addModel' => $addModel,
				'entryViewInfo' => new EntryCommandViewModel($addModel, true, 
						$this->dtc->translate('script_cmd_add_title'))));
	}
	
	private function applyBreadcrumbs() {
		$scriptState = $this->utils->getScriptState();
		$request = $this->getRequest();
		
		if (!$scriptState->isOverviewDisabled()) {
			$this->rocketState->addBreadcrumb(
					$scriptState->createOverviewBreadcrumb($request));
		}
		
		$breadcrumbPath = $request->getPath();
		$breadcrumbLabel = $this->dtc->translate('script_impl_add_entry_breadcrumb');
		$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
	}
}