<?php
namespace rocket\script\entity\field\impl\relation\model;

use rocket\script\entity\manage\ScriptSelection;
use rocket\script\entity\manage\ScriptState;
use n2n\dispatch\option\OptionCollection;
use rocket\script\entity\field\impl\DraftableScriptFieldAdapter;
use rocket\script\entity\field\impl\TranslatableScriptFieldAdapter;
use rocket\script\core\SetupProcess;
use n2n\persistence\orm\Entity;
use rocket\script\entity\field\impl\relation\command\EmbeddedEditPseudoCommand;
use rocket\script\entity\field\impl\relation\command\EmbeddedPseudoCommand;
use n2n\dispatch\option\impl\BooleanOption;
use n2n\core\DynamicTextCollection;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\security\MappingArrayAccess;

class EmbeddedScriptFieldRelation extends ScriptFieldRelation {
	private $embeddedPseudoCommand;
	private $embeddedEditPseudoCommand;
	
	public function setup(SetupProcess $setupProcess) {
		parent::setup($setupProcess);
		
		if ($setupProcess->hasFailed()) return;
		
		if (!$this->isPersistCascaded()) {
			$setupProcess->failed($this->relationField, 'ScriptField requires an EntityProperty which cascades persist.');
		}

		if ($this->isDraftEnabled() && !$this->isJoinTableRelation($this)) {
			$setupProcess->failed($this->relationField, 'Only ScriptFields of properties with join table relations can be drafted.');
			return;
		}
		
// 		if ($this->isTranslationEnabled() && !$this->getTargetEntityScript()->isTranslationEnabled()) {
// 			$setupProcess->failed($this->relationField, 'Translation for ScriptField enabled, but target EntityScript ('
// 					. $this->getTargetEntityScript()->getId() . ') is not translatable.');
// 			return;
// 		}
		
		$this->embeddedPseudoCommand = new EmbeddedPseudoCommand($this->getTargetEntityScript());
		$this->getTargetEntityScript()->getCommandCollection()->add($this->embeddedPseudoCommand);
		
		$this->embeddedEditPseudoCommand = new EmbeddedEditPseudoCommand($this->getRelationField()->getEntityScript()->getLabel() 
						. ' > ' . $this->relationField->getLabel() . ' Embedded Edit', 
				$this->getRelationField()->getId(), $this->getTargetEntityScript()->getId());
		
		$this->getTargetEntityScript()->getCommandCollection()->add($this->embeddedEditPseudoCommand);
	}
	
	public function isReadOnlyRequired(ScriptSelectionMapping $mapping, ScriptState $scriptState) {
		if (parent::isReadOnlyRequired($mapping, $scriptState) || $this->hasRecursiveConflict($scriptState)) return true;

		$esConstraint = $scriptState->getManageState()->getSecurityManager()
				->getEntityScriptConstraintByEntityScript($this->getTargetEntityScript());
		
		return $esConstraint !== null
				&& !$esConstraint->isScriptCommandAvailable($this->embeddedEditPseudoCommand);		
	}
	
	public function completeOptionCollection(OptionCollection $optionCollection) {
		$dtc = new DynamicTextCollection('rocket');
		$optionCollection->addOption(DraftableScriptFieldAdapter::OPTION_DRAFT_ENABLED_KEY,
				new BooleanOption($dtc->translate('script_impl_draftable_label'), 
				self::OPTION_DRAFT_ENABLED_DEFAULT));
		$optionCollection->addOption(TranslatableScriptFieldAdapter::OPTION_TRANSLATION_ENABLED_KEY,
				new BooleanOption($dtc->translate('script_impl_translatable_label'),
				self::OPTION_TRANSLATION_ENABLED_DEFAULT));
		
		parent::completeOptionCollection($optionCollection);
		return $optionCollection;
	}
	
	const OPTION_DRAFT_ENABLED_DEFAULT = false;
	const OPTION_TRANSLATION_ENABLED_DEFAULT = false;
	
	public function isDraftEnabled() {
		return $this->relationField->getAttributes()->get(DraftableScriptFieldAdapter::OPTION_DRAFT_ENABLED_KEY, 
				self::OPTION_DRAFT_ENABLED_DEFAULT);
	}
	
	public function isTranslationEnabled() {
		return $this->relationField->getAttributes()->get(TranslatableScriptFieldAdapter::OPTION_TRANSLATION_ENABLED_KEY,
				self::OPTION_TRANSLATION_ENABLED_DEFAULT);
	}
	
	protected function configureTargetScriptState(ScriptState $targetScriptState, ScriptState $scriptState, 
			ScriptSelection $scriptSelection, $editCommandRequired = null) {
		parent::configureTargetScriptState($targetScriptState, $scriptState, $scriptSelection);
		
		$targetScriptState->setOverviewDisabled(true);
		
		if ($targetScriptState->isPseudo()) {
			if ($editCommandRequired) {
				$targetScriptState->setExecutedScriptCommand($this->embeddedEditPseudoCommand);
			} else {
				$targetScriptState->setExecutedScriptCommand($this->embeddedPseudoCommand);
			}
			return;
		}

		if (null !== $targetScriptState->getOverviewPathExt() && null !== $targetScriptState->getDetailPathExt()) {
			$pathExt = $scriptState->getControllerContext()->toPathExt()->extend(
					$scriptState->getContextEntityScript()->getEntryDetailPathExt($scriptSelection->toNavPoint()));
			$targetScriptState->setOverviewPathExt($pathExt);
			$targetScriptState->setDetailPathExt($pathExt);
		}
		
		$targetScriptState->setDetailBreadcrumbLabel($this->relationField->getLabel());
		$targetScriptState->setDetailDisabled(true);
	}
	
	public function createTargetScriptSelection(ScriptState $targetScriptState, Entity $targetEntity, 
			Entity $targetTranslatedEntity = null) {
		$id = $this->relationField->getId();
		
		$targetScriptSelection = new ScriptSelection($targetScriptState->getContextEntityScript()
				->extractId($targetEntity), $targetEntity);
		
		if ($targetTranslatedEntity !== null) {
			$targetScriptSelection->setTranslation($targetScriptState->getTranslationManager()
					->getManagedByTranslatedEntity($targetTranslatedEntity));
		}
		
		return $targetScriptSelection;
	}
}