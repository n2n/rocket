<?php

namespace rocket\script\entity\field\impl\relation\command\controller;

use rocket\script\core\ManageState;
use rocket\core\model\RocketState;
use n2n\http\PageNotFoundException;
use rocket\script\entity\manage\ScriptSelection;
use n2n\http\ControllerAdapter;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\field\impl\relation\model\ScriptFieldRelation;

class RelationController extends ControllerAdapter {
	private $fieldRelation;
	
	public function __construct(ScriptFieldRelation $fieldRelation) {
		$this->fieldRelation = $fieldRelation;
	}
		
	public function index($id, array $cmds, array $contextCmds, ManageState $manageState, RocketState $rocketState) {
		$scriptState = $manageState->peakScriptState();

		$em = $scriptState->getEntityManager();
		$entity = $em->find($scriptState->getContextEntityScript()->getEntityModel()->getClass(), $id);
	
		if (null === $entity || !$this->fieldRelation->getRelationField()->getEntityScript()->isObjectValid($entity)) {
			throw new PageNotFoundException();
		}
	
		$scriptSelection = new ScriptSelection($id, $entity);
		$scriptState->setScriptSelection($scriptSelection);
		
		$targetController = $this->fieldRelation->getTargetEntityScript()->createController();
		$targetScriptState = $this->fieldRelation->createTargetScriptState($manageState, $scriptState, 
				$scriptSelection, $targetController->getControllerContext());
		
		$this->applyBreadcrumb($rocketState, $scriptState, $scriptSelection);

		array_push($contextCmds, array_shift($cmds));
		$targetController->execute($cmds, $contextCmds, $this->getN2nContext());
	}
	
	private function applyBreadcrumb(RocketState $rocketState, ScriptState $scriptState, ScriptSelection $scriptSelection) {
		if (!$scriptState->isOverviewDisabled()) {
			$rocketState->addBreadcrumb(
					$scriptState->createOverviewBreadcrumb($this->getRequest()));
		}
	
		if (!$scriptState->isDetailDisabled()) {
			$rocketState->addBreadcrumb($scriptState->createDetailBreadcrumb($this->getRequest()));
		}
	} 
}

?>