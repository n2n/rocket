<?php
namespace rocket\impl\ei\component\prop\translation\model;

use rocket\spec\ei\manage\critmod\quick\QuickSearchField;
use rocket\spec\ei\manage\critmod\filter\ComparatorConstraint;
use rocket\spec\ei\manage\critmod\quick\QuickSearchDefinition;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\criteria\compare\ComparatorCriteria;
use n2n\persistence\orm\property\EntityProperty;
use n2n\persistence\orm\criteria\item\CrIt;

class TranslationQuickSearchField implements QuickSearchField {
	private $entityProperty;
	private $targetEntityClass;
	/**
	 * @var QuickSearchDefinition $targetQuickSearchDefinition
	 */
	private $targetQuickSearchDefinition;
	
	public function __construct(EntityProperty $entityProperty, \ReflectionClass $targetEntityClass, QuickSearchDefinition $targetQuickSearchDefinition) {
		$this->entityProperty = $entityProperty;
		$this->targetEntityClass = $targetEntityClass;
		$this->targetQuickSearchDefinition = $targetQuickSearchDefinition;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\critmod\quick\QuickSearchField::createComparatorConstraint()
	 */
	public function createComparatorConstraint(string $queryStr): ComparatorConstraint {
		return new TranslationComparatorConstraint($this->entityProperty, $this->targetEntityClass, 
				$this->targetQuickSearchDefinition->buildCriteriaConstraint($queryStr));
	}
}

class TranslationComparatorConstraint implements ComparatorConstraint {
	private $entityProperty;
	private $targetEntityClass;
	private $targetComparatorConstraint;
	
	public function __construct(EntityProperty $entityProperty, \ReflectionClass $targetEntityClass, 
			ComparatorConstraint $targetComparatorConstraint = null) {
		$this->entityProperty = $entityProperty;
		$this->targetEntityClass = $targetEntityClass;
		$this->targetComparatorConstraint = $targetComparatorConstraint;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\critmod\filter\ComparatorConstraint::applyToCriteriaComparator()
	 */
	public function applyToCriteriaComparator(CriteriaComparator $criteriaComparator, CriteriaProperty $alias) {
		if ($this->targetComparatorConstraint === null) return;
		
		$critProp = CrIt::p($alias, $this->entityProperty);
		$subAlias = $criteriaComparator->endClause()->uniqueAlias();
		
		$subCriteria = new ComparatorCriteria();
		$subCriteria->select($subAlias)->from($this->targetEntityClass, $subAlias);
		
		$this->targetComparatorConstraint->applyToCriteriaComparator($subCriteria->where(), $critProp);
		
		$criteriaComparator->match($critProp, 'CONTAINS ANY', $subCriteria);
	}
}