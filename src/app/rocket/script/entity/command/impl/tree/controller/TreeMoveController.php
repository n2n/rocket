<?php
namespace rocket\script\entity\command\impl\tree\controller;

use rocket\script\core\ManageState;
use rocket\script\entity\command\impl\tree\model\TreeMoveModel;
use rocket\core\model\Breadcrumb;
use n2n\core\DynamicTextCollection;
use rocket\script\entity\manage\ScriptState;
use rocket\core\model\RocketState;
use n2n\http\PageNotFoundException;
use n2n\persistence\orm\NestedSetUtils;
use n2n\http\NoHttpRefererGivenException;

class TreeMoveController extends TreeController {
	
	public function doMove(RocketState $rocketState, ManageState $manageState, $id) {
		$scriptState = $manageState->peakScriptState();
		
		$treeMoveModel = new TreeMoveModel($scriptState);
		if (!$treeMoveModel->initialize($id)) {
			throw new PageNotFoundException();
		}
		
		if ($this->dispatch($treeMoveModel, 'move')) {
			$this->redirect($scriptState->getOverviewPath($this->getRequest()));
			return;
		}
		
		$this->applyBreadcrumbs($rocketState, $scriptState);
		
		$this->forward('script\entity\command\impl\tree\view\treeMove.html', array('treeMoveModel' => $treeMoveModel));
	}
	
	private function applyBreadcrumbs(RocketState $rocketState, ScriptState $scriptState) {
		$scriptSelection = $scriptState->getScriptSelection();
		$request = $this->getRequest();
		$scriptCommandId = $scriptState->getExecutedScriptCommand()->getId();
	
		if (!$scriptState->isOverviewDisabled()) {
			$rocketState->addBreadcrumb(
					$scriptState->createOverviewBreadcrumb($this->getRequest()));
		}
	
		$rocketState->addBreadcrumb($scriptState->createDetailBreadcrumb($request));
	
		$dtc = new DynamicTextCollection('rocket');
		$rocketState->addBreadcrumb(new Breadcrumb($request->getPath(), 
				$dtc->translate('script_cmd_tree_move_breadcrumb')));
	}
	
	
	public function doMoveUp(ManageState $manageState, $id) {
		$this->order($manageState, $id, true);
	}
	
	public function doMoveDown(ManageState $manageState, $id) {
		$this->order($manageState, $id, false);
	}
	
	private function order(ManageState $manageState, $id, $up) {		
		$scriptState = $manageState->peakScriptState();
		$entityScript = $scriptState->getContextEntityScript();
		$em = $scriptState->getEntityManager();
		$class = $entityScript->getEntityModel()->getClass();
		
		$object = $em->find($class, $id);
		if (!isset($object)) {
			throw new PageNotFoundException();
		}
		
		$nestedSetUtils = new NestedSetUtils($em, $class);
		$nestedSetUtils->order($object, $up);
		
		try {
			$this->redirectToReferer();
		} catch (NoHttpRefererGivenException $e) {
			$this->redirectToController($scriptState->getContextEntityScript()->getOverviewPathExt(),
					null, null, $scriptState->getControllerContext());
		}
	}
}
