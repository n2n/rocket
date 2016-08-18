<?php

namespace rocket\script\entity\field\impl\relation\model;

use n2n\dispatch\option\impl\BooleanOption;
use n2n\dispatch\option\OptionCollection;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\ScriptSelection;
use rocket\script\core\SetupProcess;
use rocket\script\entity\field\impl\relation\command\EmbeddedEditPseudoCommand;
use n2n\core\IllegalStateException;
use rocket\script\entity\field\impl\relation\command\EmbeddedPseudoCommand;

class SimpleScriptFieldRelation extends ScriptFieldRelation {
	const OPTION_EMBEDDED_ADD_KEY = 'embeddedAddEnabled';
	const OPTION_EMBEDDED_ADD_DEFAULT = false;
	
	protected $embeddedPseudoCommand;
	protected $embeddedEditPseudoCommand;
	
	public function setup(SetupProcess $setupProcess) {
		parent::setup($setupProcess);
		
		if ($this->targetEntityScript === null) return;
		
		if ($this->isEmbeddedAddEnabled()) {
			if (!$this->isPersistCascaded()) {
				$setupProcess->failed($this->relationField, 'Option ' . self::OPTION_EMBEDDED_ADD_KEY . ' requires an EntityProperty which cascades persist.');
				return;
			}
			
			$this->embeddedEditPseudoCommand = new EmbeddedEditPseudoCommand(
					$this->getRelationField()->getEntityScript()->getLabel() . ' > ' . $this->relationField->getLabel() . ' Embedded Add', 
					$this->getRelationField()->getId(), $this->getTargetEntityScript()->getId());
			$this->getTargetEntityScript()->getCommandCollection()->add($this->embeddedEditPseudoCommand);
		} 
		
		$this->embeddedPseudoCommand = new EmbeddedPseudoCommand($this->getTargetEntityScript());
		$this->targetEntityScript->getCommandCollection()->add($this->embeddedPseudoCommand);
	}
	
	public function completeOptionCollection(OptionCollection $optionCollection) {
		$optionCollection->addOption(self::OPTION_EMBEDDED_ADD_KEY, 
				new BooleanOption('Embedded Add Enabled', self::OPTION_EMBEDDED_ADD_DEFAULT, false));
		return $optionCollection;
	}
	
	private function isEmbeddedAddEnabled() {
		return (boolean) $this->relationField->getAttributes()->get(self::OPTION_EMBEDDED_ADD_KEY, self::OPTION_EMBEDDED_ADD_DEFAULT);
	}
	
	public function isEmbeddedAddActivated(ScriptState $scriptState) {
		return $this->isEmbeddedAddEnabled() && !$this->hasRecursiveConflict($scriptState)
				&& $scriptState->isScriptCommandAvailable($this->embeddedEditPseudoCommand);
	}
	
	protected function configureTargetScriptState(ScriptState $targetScriptState, ScriptState $scriptState, 
			ScriptSelection $scriptSelection, $editCommandRequired = null) {
		parent::configureTargetScriptState($targetScriptState, $scriptState, $scriptSelection, $editCommandRequired);

		if ($targetScriptState->isPseudo()) {
			$targetScriptState->setEntityScriptConstraint(null);
			if ($editCommandRequired) {
				if (null === $this->embeddedEditPseudoCommand) {
					throw new IllegalStateException();
				}
				$targetScriptState->setExecutedScriptCommand($this->embeddedEditPseudoCommand);
			} else {
				$targetScriptState->setExecutedScriptCommand($this->embeddedPseudoCommand);
			}
		}
		
		if (!$this->isTargetMany()) {
			$targetScriptState->setOverviewDisabled(true);
			$targetScriptState->setDetailBreadcrumbLabel($this->buildDetailLabel($scriptState));
			return;
		}
		
		$targetScriptState->setOverviewBreadcrumbLabel($this->buildDetailLabel($scriptState));
		
		// 	$targetEntityScript = $this->getTargetEntityScript();
		// 	$targetEntity = $this->scriptField->getPropertyAccessProxy()->getValue($scriptSelection->getEntity());
		// 	if (null === $targetEntity) {
		// 		return false;
		// 	}
		// 	$targetScriptState->setDetailBreadcrumbLabel($this->scriptField->getLabel() . ' ('
		// 			. $targetEntityScript->createKnownString($targetEntity, $scriptState->getLocale()) . ')');
	}
	
	protected function buildDetailLabel(ScriptState $scriptState) {
		$label = $this->relationField->getLabel();
		
		do {
			if ($scriptState->isDetailDisabled() 
					&& null !== ($detaiLabel = $scriptState->getDetailBreadcrumbLabel())) {
				$label = $detaiLabel . ' > ' . $label; 
			}
		} while (null !== ($scriptState = $scriptState->getParent()));
		
		return $label;
	}
}