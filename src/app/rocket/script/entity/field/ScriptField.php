<?php
namespace rocket\script\entity\field;

use rocket\script\entity\ScriptElement;

interface ScriptField extends ScriptElement {
	/**
	 * @return string
	 */
	public function getLabel();
}