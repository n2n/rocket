<?php

namespace rocket\script\entity\command\impl;

use rocket\script\entity\IndependentScriptElementAdapter;
use rocket\script\entity\command\IndependentScriptCommand;

abstract class IndependentScriptCommandAdapter extends IndependentScriptElementAdapter implements IndependentScriptCommand {
	public function getTypeName() {
		return self::shortenTypeName(parent::getTypeName(), array('Script', 'Command'));
	}
	
	public function equals($obj) {
		return $obj instanceof IndependentScriptCommand && parent::equals($obj);
	}
}