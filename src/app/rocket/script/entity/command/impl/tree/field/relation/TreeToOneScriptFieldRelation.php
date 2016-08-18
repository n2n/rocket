<?php
namespace rocket\script\entity\command\impl\tree\field\relation;

use rocket\script\entity\manage\ScriptSelection;
use rocket\script\entity\manage\ScriptState;
use n2n\core\NotYetImplementedException;
use n2n\persistence\orm\OrmUtils;
use rocket\script\entity\field\impl\relation\model\SimpleScriptFieldRelation;

class TreeToOneScriptFieldRelation extends SimpleScriptFieldRelation {
	private $targetTreeRootIdField;
	private $targetTreeLeftScriptField;
	private $targetTreeRightScriptField;
	
	public function __construct(TreeRelationScriptField $scriptField) {
		parent::__construct($scriptField, false, false);
		
	}
	
	protected function configureTargetScriptState(ScriptState $targetScriptState, ScriptState $scriptState, 
			ScriptSelection $scriptSelection, $editCommandRequired = null) {
		parent::configureTargetScriptState($targetScriptState, $scriptState, $scriptSelection, $editCommandRequired);

		$targetScriptState->setOverviewDisabled(false);
		$targetScriptState->setOverviewBreadcrumbLabel($this->buildDetailLabel($scriptState));
		$targetScriptState->setDetailBreadcrumbLabel(null);
	}

	protected function applyTargetModificators(ScriptState $targetScriptState, ScriptState $scriptState, 
			ScriptSelection $scriptSelection) {
		
	}
		
	protected function createTargetCriteriaFactory(ScriptSelection $scriptSelection) {
		if ($scriptSelection->isNew()) return null;
		
		$targetEntity = $this->relationField->read($scriptSelection->getOriginalEntity());
		if ($targetEntity === null) return null;
		
		OrmUtils::initializeProxy($targetEntity);
		$rootIdProperty = $this->relationField->getTargetTreeRootIdScriptField()->getEntityProperty();
		$lftProperty = $this->relationField->getTargetTreeLeftScriptField()->getEntityProperty();
		$rgtProperty = $this->relationField->getTargetTreeRightScriptField()->getEntityProperty();
		
				
		if (!$this->isMaster() && !$this->isSourceMany()) {
			throw new NotYetImplementedException;
// 			return new MappedOneToCriteriaFactory($this->relationProperty->getRelation(), 
// 					$scriptSelection->getOriginalEntity());	
		}
		
		return new TreeRelationCriteriaFactory($this->relationProperty, 
				$rootIdProperty->getAccessProxy()->getValue($targetEntity), 
				$lftProperty->getAccessProxy()->getValue($targetEntity), 
				$rgtProperty->getAccessProxy()->getValue($targetEntity), 
				$rootIdProperty->getName(), $lftProperty->getName(), $rgtProperty->getName());
	}
}