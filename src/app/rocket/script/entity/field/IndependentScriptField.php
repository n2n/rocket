<?php

namespace rocket\script\entity\field;

use rocket\script\entity\IndependentScriptElement;

interface IndependentScriptField extends ScriptField, IndependentScriptElement {
	/**
	 * @param string $label
	 */
	public function setLabel($label);
}