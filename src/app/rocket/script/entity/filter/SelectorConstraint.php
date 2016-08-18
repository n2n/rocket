<?php

namespace rocket\script\entity\filter;

interface SelectorConstraint {
	/**
	 * @param mixed $value
	 * @return boolean
	 */
	public function matches($value);
	
	public function validate($value);
}