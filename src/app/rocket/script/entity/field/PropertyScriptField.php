<?php
namespace rocket\script\entity\field;

interface PropertyScriptField extends ScriptField {
	public function getPropertyName();
	/**
	 * @return \n2n\reflection\property\PropertyAccessProxy
	 */
	public function getPropertyAccessProxy();
} 