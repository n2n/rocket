<?php
namespace rocket\script\entity\filter;

use n2n\persistence\orm\criteria\CriteriaProperty;
use n2n\persistence\orm\criteria\CriteriaComparator;

interface ComparatorConstraint {
	public function applyToCriteriaComparator(CriteriaComparator $criteriaComparator, CriteriaProperty $alias, $useAnd);
}