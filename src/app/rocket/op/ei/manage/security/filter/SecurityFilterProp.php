<?php
namespace rocket\op\ei\manage\security\filter;

use rocket\op\ei\manage\critmod\filter\FilterProp;
use n2n\util\attr\DataSet;
use rocket\op\ei\manage\entry\EiFieldConstraint;
use n2n\util\attr\AttributesException;

interface SecurityFilterProp extends FilterProp {
	
	/**
	 * @param DataSet $dataSet
	 * @return EiFieldConstraint
	 * @throws AttributesException
	 */
	function createEiFieldConstraint(DataSet $dataSet): EiFieldConstraint;
}

