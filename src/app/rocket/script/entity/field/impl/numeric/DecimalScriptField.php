<?php
namespace rocket\script\entity\field\impl\numeric;

use n2n\dispatch\option\impl\IntegerOption;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\dispatch\option\impl\NumericOption;
use rocket\script\entity\field\impl\ManageInfo;
use n2n\dispatch\option\OptionCollection;

class DecimalScriptField extends NumericScriptFieldAdapter {
	const OPTION_DECIMAL_PLACES_KEY = 'decimalPlaces';
	const OPTION_DECIMAL_PLACES_DEFAULT = 0;
	
	protected $optionDecimalPlacesDefault = self::OPTION_DECIMAL_PLACES_DEFAULT;
	
	public function getTypeName() {
		return 'Decimal';
	}

	public function getDecimalPlaces() {
		return $this->getAttributes()->get(self::OPTION_DECIMAL_PLACES_KEY, self::OPTION_DECIMAL_PLACES_KEY);
	}
	
	public function createOptionCollection() {
		$optionCollection = parent::createOptionCollection();
		$this->applyDecimalOptions($optionCollection);
		return $optionCollection;
	}
	
	protected function applyDecimalOptions(OptionCollection $optionCollection, $addDecimalPlacesOption = true) {
		if ($addDecimalPlacesOption) {
			$optionCollection->addOption(self::OPTION_DECIMAL_PLACES_KEY,
					new IntegerOption('Positions after decimal point', $this->optionDecimalPlacesDefault, true, 0));
		}
	}

	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return new NumericOption($this->getLabel(), null,
				$this->isRequired($scriptSelectionMapping, $manageInfo), 
				$this->getMinValue(), $this->getMaxValue(), $this->getDecimalPlaces(), array('placeholder' => $this->getLabel()));
	}
}