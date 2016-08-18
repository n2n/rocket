<?php
namespace rocket\script\entity\field\impl\numeric;

use n2n\dispatch\option\impl\OptionCollectionImpl;
use rocket\script\core\SetupProcess;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\ManageInfo;
class IdScriptField extends IntegerScriptField {
	
	public function setup(SetupProcess $setupProcess) {
		$this->displayInListViewDefault = false;
		$this->displayInDetailViewDefault = true;
	}
	
	public function getTypeName() {
		return 'Auto Generated Id';
	}
	
	public function createOptionCollection() {
		$optionCollection = new OptionCollectionImpl();
		$this->applyDisplayOptions($optionCollection, true, true, false, false, false);
		return $optionCollection;
	}
	
	public function isDisplayInAddViewEnabled() {
		return false;
	}
	
	public function isDisplayInEditViewEnabled() {
		return false;
	}
	
	public function isReadOnly(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return true;
	}
	
	public function isRequired(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return false;
	}
}