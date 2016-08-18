<?php
namespace rocket\script\entity\command\impl\tree\field\relation;

use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\ScriptSelection;
use rocket\script\core\SetupProcess;
use n2n\util\Attributes;
use rocket\script\entity\command\impl\tree\TreeUtils;
use n2n\ui\html\HtmlView;
use rocket\script\entity\field\impl\relation\ManyToOneScriptField;
use n2n\persistence\orm\EntityManager;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\ManageInfo;
use rocket\script\entity\field\impl\relation\option\ToOneOption;
use rocket\script\entity\manage\EntryManageUtils;
use n2n\persistence\orm\NestedSetUtils;

class TreeManyToOneScriptField extends ManyToOneScriptField implements TreeRelationScriptField {
	private $targetTreeRootIdScriptField;
	private $targetTreeLeftScriptField;
	private $targetTreeRightScriptField;
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
	
		$this->initilaize(new TreeToOneScriptFieldRelation($this));
	}
	
	public function setup(SetupProcess $setupProcess) {
		parent::setup($setupProcess);
		
		$targetEntityScript = $this->fieldRelation->getTargetEntityScript();
		TreeUtils::findTreeField($targetEntityScript, $this->targetTreeLeftScriptField, 
				$this->targetTreeRightScriptField, $this->targetTreeRootIdScriptField);
		
		if ($this->targetTreeLeftScriptField === null) {
			$setupProcess->failed($this, 'Target EntityScript (' . $targetEntityScript->getId() 
					. ') has no TreeLeftScirptField)');
		}
		
		if ($this->targetTreeRightScriptField === null) {
			$setupProcess->failed($this, 'Target EntityScript (' . $targetEntityScript->getId() 
					. ') has no TreeRightScirptField.');
		}
		
		if ($this->targetTreeRootIdScriptField === null) {
			$setupProcess->failed($this, 'Target EntityScript (' . $targetEntityScript->getId() 
					. ') has no TreeRootIdScirptField.');
		}
	}
	
	public function getTargetTreeRootIdScriptField() {
		return $this->targetTreeRootIdScriptField;
	}
	
	public function getTargetTreeLeftScriptField() {
		return $this->targetTreeLeftScriptField;
	}
	
	public function getTargetTreeRightScriptField() {
		return $this->targetTreeRightScriptField;
	}

	protected function createUiOutput(ScriptState $targetScriptState, ScriptSelection $targetScriptSelection, HtmlView $view) {
		$html = $view->getHtmlBuilder();
		if ($targetScriptSelection->isNew()) return null;
		$knownString = $targetScriptState->createKnownString($targetScriptSelection->getEntity());
		if (!$targetScriptState->isOverviewPathAvailable()) {
			return $html->getEsc($knownString);
		} else {
			return $html->getLink($targetScriptState->getOverviewPath($view->getRequest(), 
					$targetScriptSelection->toNavPoint()), $knownString);
		}
	}
	
	public function getTypeName() {
		return 'Tree Many To One';
	}

	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$scriptState = $manageInfo->getScriptState();
		$embeddedAddActivated = $this->fieldRelation->isEmbeddedAddActivated($scriptState);
		$targetUtils = new EntryManageUtils($this->fieldRelation->createTargetPseudoScriptState(
				$scriptState, $scriptSelectionMapping->getScriptSelection(), $embeddedAddActivated));
	
		$toOneOption = new ToOneOption($this->getId(), $this->getLabel(), $scriptSelectionMapping, $targetUtils,
				$this->isRequired($scriptSelectionMapping, $manageInfo));
		$toOneOption->setTargetScriptSelection($this->createTargetScriptSelection($scriptSelectionMapping));
		$toOneOption->setEmbeddedAddEnabled($embeddedAddActivated);
	
		$labels = array();
		$targetEntities = $this->lookupSelectableTreeEntites($scriptState->getEntityManager(), $labels, 
				$targetUtils->getScriptState());
	
		$toOneOption->setSelectableEntities($this->filterAccessableTargetEntities(
				$targetEntities, $scriptSelectionMapping->getSelectionPrivilegeConstraint()));
		$toOneOption->setSelectableEntityLabels($labels);
	
		return $toOneOption;
	}
	
	protected function lookupSelectableTreeEntites(EntityManager $em, array &$labels, ScriptState $targetScriptState) {
		$targetEntityScript = $this->fieldRelation->getTargetEntityScript();
	
		$nestedSetUtils = new NestedSetUtils($em, $targetEntityScript->getEntityModel()->getClass());
		
		$selectableEntities = array();
		foreach ($nestedSetUtils->fetchNestedSetItems() as $nestedSetItem) {
			$targetEntity = $nestedSetItem->getObject();
			$id = $targetEntityScript->extractId($targetEntity);
			$selectableEntities[$id] = $targetEntity;
			$labels[$id] = str_repeat('..', $nestedSetItem->getLevel()) . ' ' . $targetScriptState->getScriptMask()
					->createKnownString($targetEntity, $targetScriptState->getLocale());
		}
	
		return $selectableEntities;
	}
	
	
}