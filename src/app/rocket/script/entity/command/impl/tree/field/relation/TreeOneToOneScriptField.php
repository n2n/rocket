<?php
namespace rocket\script\entity\command\impl\tree\field\relation;

use rocket\script\entity\field\impl\relation\OneToOneScriptField;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\ScriptSelection;
use rocket\script\core\SetupProcess;
use n2n\util\Attributes;
use rocket\script\entity\command\impl\tree\TreeUtils;
use n2n\ui\html\HtmlView;

class TreeOneToOneScriptField extends OneToOneScriptField implements TreeRelationScriptField {
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
		return 'Tree One To One';
	}
}