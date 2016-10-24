<?php
namespace rocket\spec\ei\component\field\impl\bool\conf;

use rocket\spec\ei\component\field\impl\adapter\AdaptableEiFieldConfigurator;
use rocket\spec\ei\component\field\impl\bool\BooleanEiField;

class BooleanEiFieldConfigurator extends AdaptableEiFieldConfigurator {
	
	public function __construct(BooleanEiField $eiComponent) {
		parent::__construct($eiComponent);
		
		$this->addMandatory = false;

		$this->autoRegister();
	}
}