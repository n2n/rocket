<?php

namespace rocket\script\entity\manage\mapping;

interface MappingConstraint extends MappingValidator {
	/**
	 * @param \ArrayAccess $values
	 * @return boolean
	 */
	public function acceptValues(\ArrayAccess $values);
	/**
	 * @param unknown $id
	 * @param unknown $value
	 * @return boolean
	 */
	public function acceptValue($id, $value);
}