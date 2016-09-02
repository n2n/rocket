<?php
namespace rocket\spec\ei\manage\critmod\quick\impl\model;

use rocket\spec\ei\manage\critmod\quick\QuickSearchField;
use n2n\persistence\orm\criteria\item\CriteriaItem;
use rocket\spec\ei\manage\critmod\filter\impl\model\SimpleComparatorConstraint;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use rocket\spec\ei\manage\critmod\filter\ComparatorConstraint;
use rocket\spec\ei\manage\critmod\filter\impl\model\PropertyValueComparatorConstraint;
use n2n\persistence\orm\criteria\item\CriteriaProperty;

class LikeQuickSearchField implements QuickSearchField {
	private $criteriaItem;
	
	public function __construct(CriteriaProperty $criteriaProperty) {
		$this->criteriaItem = $criteriaProperty;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\critmod\quick\QuickSearchField::createComparatorConstraint($queryStr)
	 */
	public function createComparatorConstraint(string $queryStr): ComparatorConstraint {
		return new PropertyValueComparatorConstraint($this->criteriaItem, CriteriaComparator::OPERATOR_LIKE, 
				CrIt::c('%' . $queryStr . '%'));
	}
}