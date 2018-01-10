<?php
namespace rocket\impl\ei\component\prop\bool\conf;

use rocket\impl\ei\component\prop\adapter\AdaptableEiPropConfigurator;
use rocket\impl\ei\component\prop\bool\BooleanEiProp;

class BooleanEiPropConfigurator extends AdaptableEiPropConfigurator {
	
	public function __construct(BooleanEiProp $eiComponent) {
		parent::__construct($eiComponent);
		
		$this->addMandatory = false;

		$this->autoRegister();
	}
}