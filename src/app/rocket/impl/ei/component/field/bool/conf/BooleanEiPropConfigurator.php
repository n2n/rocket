<?php
namespace rocket\impl\ei\component\field\bool\conf;

use rocket\impl\ei\component\field\adapter\AdaptableEiPropConfigurator;
use rocket\impl\ei\component\field\bool\BooleanEiProp;

class BooleanEiPropConfigurator extends AdaptableEiPropConfigurator {
	
	public function __construct(BooleanEiProp $eiComponent) {
		parent::__construct($eiComponent);
		
		$this->addMandatory = false;

		$this->autoRegister();
	}
}