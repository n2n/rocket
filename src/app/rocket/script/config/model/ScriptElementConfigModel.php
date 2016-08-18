<?php

namespace rocket\script\config\model;

use n2n\dispatch\Dispatchable;
use rocket\script\entity\IndependentScriptElement;
use n2n\dispatch\option\impl\OptionForm;

class ScriptElementConfigModel implements Dispatchable {
	private $configurable;
	protected $optionForm;
	
	public function __construct(IndependentScriptElement $configurable) {
		$this->configurable = $configurable;
		$this->optionForm = new OptionForm($configurable->createOptionCollection(), $configurable->getAttributes());
	}
	
	public function getTypeName() {
		return $this->configurable->getTypeName();
	}
	
	public function setOptionForm(OptionForm $optionForm) {
		$this->optionForm = $optionForm;
	}
	
	public function getOptionForm() {
		return $this->optionForm;
	}
	
	private function _validation() {
	}
}