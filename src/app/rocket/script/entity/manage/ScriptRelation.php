<?php
namespace rocket\script\entity\manage;

class ScriptRelation {
	private $scriptState;
// 	private $scriptModificators = array();
	
	public function __construct(ScriptState $scriptState, ScriptSelection $scriptSelection) {
		$this->scriptState = $scriptState;
		$this->scriptSelection = $scriptSelection;
	}
	
	public function getScriptState() {
		return $this->scriptState;
	}
	
	public function getScriptSelection() {
		return $this->scriptSelection;
	}
	
// 	public function setCriteriaConstraints(array $scriptModificators) {
// 		$this->scriptModificators = $scriptModificators;
// 	}
	
// 	public function getCriteriaConstraints() {
// 		return $this->scriptModificators;
// 	}
}