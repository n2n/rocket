<?php
namespace rocket\ei\manage\security\filter;

use rocket\ei\manage\critmod\filter\FilterProp;
use n2n\util\config\Attributes;
use rocket\ei\manage\entry\EiFieldConstraint;
use n2n\util\config\AttributesException;

interface SecurityFilterProp extends FilterProp {
	
	/**
	 * @param Attributes $attributes
	 * @return EiFieldConstraint
	 * @throws AttributesException
	 */
	function createEiFieldConstraint(Attributes $attributes): EiFieldConstraint;
}

