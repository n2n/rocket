<?php
namespace rocket\op\ei\manage\security\filter;

use rocket\op\ei\manage\critmod\filter\FilterProp;
use n2n\util\type\attrs\DataSet;
use rocket\op\ei\manage\entry\EiFieldConstraint;
use n2n\util\type\attrs\AttributesException;

interface SecurityFilterProp extends FilterProp {
	
	/**
	 * @param DataSet $dataSet
	 * @return EiFieldConstraint
	 * @throws AttributesException
	 */
	function createEiFieldConstraint(DataSet $dataSet): EiFieldConstraint;
}

