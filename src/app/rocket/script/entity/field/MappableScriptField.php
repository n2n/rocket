<?php

namespace rocket\script\entity\field;

use n2n\reflection\property\TypeConstraints;

interface MappableScriptField {
	/**
	 * @return TypeConstraints 
	 */
	public function getTypeConstraints();
}