<?php

namespace rocket\ei\manage\security\filter;


use rocket\ei\manage\critmod\filter\FilterProp;
use n2n\util\config\Attributes;
use rocket\ei\manage\mapping\EiFieldConstraint;

interface SecurityFilterProp extends FilterProp {
	
	/**
	 * @param Attributes $attributes
	 * @return EiFieldConstraint
	 */
	function createEiFieldConstraint(Attributes $attributes): EiFieldConstraint;
}

