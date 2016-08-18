<?php
namespace rocket\script\entity\manage;

use n2n\persistence\orm\criteria\CriteriaProperty;
use n2n\persistence\orm\criteria\Criteria;

interface CriteriaConstraint {
	public function applyToCriteria(Criteria $criteria, CriteriaProperty $alias);
}