<?php
namespace rocket\spec\ei\component\field\impl\bool\conf;

use rocket\spec\ei\component\field\impl\adapter\AdaptableEiPropConfigurator;
use rocket\spec\ei\component\field\impl\bool\BooleanEiProp;

class BooleanEiPropConfigurator extends AdaptableEiPropConfigurator {
	
	public function __construct(BooleanEiProp $eiComponent) {
		parent::__construct($eiComponent);
		
		$this->addMandatory = false;

		$this->autoRegister();
	}
}