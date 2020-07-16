<?php
namespace rocket\impl\ei\component\prop\translation\model;

use rocket\ei\manage\critmod\quick\QuickSearchProp;
use rocket\ei\manage\critmod\filter\ComparatorConstraint;
use n2n\persistence\orm\property\EntityProperty;
use rocket\ei\util\Eiu;

class ToOneQuickSearchProp implements QuickSearchProp {
	private $entityProperty;
	private $targetDefPropPaths;
	/**
	 * @var Eiu $targetEiu
	 */
	private $targetEiu;
	
	public function __construct(EntityProperty $entityProperty, array $targetDefPropPaths, Eiu $targetEiu) {
		$this->entityProperty = $entityProperty;
		$this->targetDefPropPaths = $targetDefPropPaths;
		$this->targetEiu = $targetEiu;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\critmod\quick\QuickSearchProp::createComparatorConstraint()
	 */
	public function buildComparatorConstraint(string $queryStr): ?ComparatorConstraint {
		return $this->targetEiu->frame()->getEiFrame()->getQuickSearchDefinition()
				->buildCriteriaConstraint($queryStr, $this->targetDefPropPaths);
	}
}
