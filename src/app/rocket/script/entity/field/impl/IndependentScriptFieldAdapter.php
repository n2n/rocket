<?php
namespace rocket\script\entity\field\impl;

use rocket\script\entity\field\ScriptField;
use rocket\script\entity\IndependentScriptElementAdapter;
use rocket\script\entity\field\IndependentScriptField;

abstract class IndependentScriptFieldAdapter extends IndependentScriptElementAdapter implements IndependentScriptField {
	protected $label;

	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
	}
	
	public function getTypeName() {
		return self::shortenTypeName(parent::getTypeName(), array('Script', 'Field'));
	}
	
	public function equals($obj) {
		return $obj instanceof ScriptField && parent::equals($obj);
	}
}