<?php
namespace rocket\script\entity\command\impl\common\controller;

use n2n\http\PageNotFoundException;
use rocket\script\entity\filter\FilterStore;
use rocket\core\model\RocketState;
use rocket\script\core\ManageState;
use rocket\script\entity\command\impl\common\model\ListModel;
use n2n\http\ControllerAdapter;
use rocket\script\entity\command\impl\common\model\ListFilterForm;
use rocket\script\entity\command\impl\common\model\ListQuickSearchModel;
use rocket\script\entity\command\impl\common\model\ListTmpFilterStore;

class ListController extends ControllerAdapter {
	private $listSize;
	
	public function __construct($listSize) {
		$this->listSize = $listSize;
	}
	
	public function index(ManageState $manageState, RocketState $rocketState, FilterStore $filterStore, 
			ListTmpFilterStore $tmpFilterStore, $pageNo = null) {
		$scriptState = $manageState->peakScriptState();
		
		$rocketState->addBreadcrumb(
				$scriptState->createOverviewBreadcrumb($this->getRequest()));
		
		$listFilterForm = new ListFilterForm($scriptState, $filterStore, $tmpFilterStore);
		$listQuickSearchModel = new ListQuickSearchModel($scriptState, $tmpFilterStore);
		$listModel = new ListModel($scriptState, $this->listSize);
		
		$listFilterForm->applyToScriptState($scriptState);
		$listQuickSearchModel->applyToScriptState($scriptState);
		
		if ($pageNo === null) {
			$pageNo = 1;
		} else if ($pageNo == 1) {
			throw new PageNotFoundException();
		}
		
		if (!$listModel->initialize($pageNo)) {
			throw new PageNotFoundException();
		}
		
		if ($this->dispatch($listFilterForm, 'selectFilter') || $this->dispatch($listFilterForm, 'apply')
				|| $this->dispatch($listFilterForm, 'clear') || $this->dispatch($listFilterForm, 'saveFilter') 
				|| $this->dispatch($listFilterForm, 'createFilter') || $this->dispatch($listFilterForm, 'deleteFilter') 
				|| $this->dispatch($listQuickSearchModel, 'search') || $this->dispatch($listQuickSearchModel, 'clear')
				|| $this->dispatch($listModel, 'executePartialCommand')) {
			$this->refresh();
			return;
		}
		
		$listView = $scriptState->getScriptMask()->createListView($listModel);
		
		$this->forward('script\entity\command\impl\common\view\list.html', 
				array('listModel' => $listModel, 'listFilterForm' => $listFilterForm,
						'listQuickSearchModel' => $listQuickSearchModel, 
						'navPoints' => $this->createNavPoints($listModel),
						'listView' => $listView));
	}
	
	private function createNavPoints(ListModel $listModel) {
		if ($listModel->getNumPages() < 2) return array();
		
		$request = $this->getRequest();
		$navPoints = array();
		for ($pageNo = 1; $pageNo <= $listModel->getNumPages(); $pageNo++) {
			$navPoints[$request->getControllerContextPath($this->getControllerContext(), ($pageNo > 1 ? $pageNo : null))] = $pageNo;
		}
		return $navPoints;		
	}
}