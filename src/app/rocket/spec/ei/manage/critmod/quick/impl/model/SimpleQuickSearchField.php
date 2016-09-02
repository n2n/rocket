<?php
namespace rocket\spec\ei\manage\critmod\quick\impl\model;

use rocket\spec\ei\manage\critmod\quick\QuickSearchField;
use n2n\persistence\orm\criteria\item\CriteriaItem;
use rocket\spec\ei\manage\critmod\filter\impl\model\SimpleComparatorConstraint;
use n2n\persistence\orm\criteria\item\CrIt;

class SimpleQuickSearchField implements QuickSearchField {
	private $criteriaItem;
	private $operator;
	
	public function __construct(CriteriaItem $criteriaItem, string $operator = CriteriaComparator::OPERATOR_LIKE) {
		$this->criteriaItem = $criteriaItem;
		$this->operator = $operator;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\critmod\quick\QuickSearchField::createComparatorConstraint($queryStr)
	 */
	public function createComparatorConstraint(string $queryStr) {
		return new SimpleComparatorConstraint($this->criteriaItem, $this->operator, CrIt::c($queryStr));
	}
}