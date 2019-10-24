<?php
namespace rocket\ei\manage\security\filter;

use rocket\ei\manage\critmod\filter\FilterProp;
use n2n\util\type\attrs\DataSet;
use rocket\ei\manage\entry\EiFieldConstraint;
use n2n\util\type\attrs\DataSetException;

interface SecurityFilterProp extends FilterProp {
	
	/**
	 * @param DataSet $dataSet
	 * @return EiFieldConstraint
	 * @throws DataSetException
	 */
	function createEiFieldConstraint(DataSet $dataSet): EiFieldConstraint;
}

