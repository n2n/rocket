<?php
namespace rocket\script\entity\field\impl\numeric;

use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\dispatch\option\impl\IntegerOption;
use rocket\script\entity\field\impl\ManageInfo;

class IntegerScriptField extends NumericScriptFieldAdapter {

	public function getTypeName() {
		return 'Integer';
	}

	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return new IntegerOption($this->getLabel(), null,
				$this->isRequired($scriptSelectionMapping, $manageInfo),
				$this->getMinValue(), $this->getMaxValue(), array('placeholder' => $this->getLabel()));
	}
}