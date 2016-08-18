<?php
namespace rocket\script\entity\command\impl\tree\controller;

use rocket\script\entity\command\impl\tree\field\TreeRightScriptField;
use rocket\script\entity\command\impl\tree\field\TreeRootIdScriptField;
use rocket\script\entity\command\impl\tree\field\TreeLeftScriptField;
use n2n\http\ControllerAdapter;

abstract class TreeController extends ControllerAdapter {
	protected $treeLeftScriptField;
	protected $treeRightScriptField;
	protected $treeRootIdScriptField;
	
	public function initialize(TreeLeftScriptField $treeLeftScriptField,
			TreeRightScriptField $treeRightScriptField, TreeRootIdScriptField $treeRootIdScriptField) {
		$this->treeLeftScriptField = $treeLeftScriptField;
		$this->treeRightScriptField = $treeRightScriptField;
		$this->treeRootIdScriptField = $treeRootIdScriptField;
	}
	
// 	protected function createNestedSetUtils(ScriptState $scriptState) {
// 		$nsUtils = new NestedSetUtils($scriptState->getEntityManager(), 
// 				$scriptState->getContextEntityScript()->getEntityModel()->getClass());
// 		$nsUtils->setLeftPropertyName($this->treeLeftScriptField->getEntityProperty()->getName());
// 		$nsUtils->setRightPropertyName($this->treeRightScriptField->getEntityProperty()->getName());
// 		$nsUtils->setRootIdPropertyName($this->treeRootIdScriptField->getEntityProperty()->getName());
// 		return $nsUtils;
// 	}
}