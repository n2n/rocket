<?php
namespace rocket\script\entity\listener\impl;

use rocket\script\entity\IndependentScriptElementAdapter;
use rocket\script\entity\listener\ScriptListener;
use rocket\script\entity\listener\IndependentScriptListener;

abstract class IndependentScriptListenerAdapter extends IndependentScriptElementAdapter implements IndependentScriptListener {
	public function getTypeName() {
		return self::shortenTypeName(parent::getTypeName(), array('Script', 'Listener'));
	}
	
	public function equals($obj) {
		return $obj instanceof ScriptListener && parent::equals($obj);
	}
}