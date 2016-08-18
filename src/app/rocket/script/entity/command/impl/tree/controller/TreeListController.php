<?php
namespace rocket\script\entity\command\impl\tree\controller;

use rocket\script\core\ManageState;
use rocket\script\entity\command\impl\tree\model\TreeListModel;

class TreeListController extends TreeController {
	
	public function index(ManageState $manageState, $baseid = null) {
		$scriptState = $manageState->peakScriptState();
				
		$treeListModel = new TreeListModel($scriptState);
		$treeListModel->initialize();
		
		$treeListView = $scriptState->getScriptMask()->createListView($treeListModel);
		
		$this->forward('script\entity\command\impl\tree\view\treeList.html',
				array('treeListModel' => $treeListModel, 'treeListView' => $treeListView));
	}
}
