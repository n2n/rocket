<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\impl\ei\component\prop\relation\model\filter;

use rocket\op\ei\manage\critmod\filter\ComparatorConstraint;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\criteria\compare\ComparatorCriteria;
use n2n\impl\persistence\orm\property\RelationEntityProperty;

class TestComparatorConstraint implements ComparatorConstraint {
	private $entityProperty;
	private $targetComparatorConstraint;
	
	public function __construct(RelationEntityProperty $entityProperty, 
			ComparatorConstraint $targetComparatorConstraint) {
		$this->entityProperty = $entityProperty;
		$this->targetComparatorConstraint = $targetComparatorConstraint;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\critmod\filter\ComparatorConstraint::applyToCriteriaComparator()
	 */
	public function applyToCriteriaComparator(CriteriaComparator $criteriaComparator, CriteriaProperty $alias) {
		$operator = $this->entityProperty->isToMany() ? CriteriaComparator::OPERATOR_CONTAINS
				: CriteriaComparator::OPERATOR_EQUAL;
		
		$targetAlias = $criteriaComparator->endClause()->uniqueAlias();
		$subCriteria = new ComparatorCriteria();
		$subCriteria->select('1')->from($this->entityProperty->getTargetEntityModel()->getClass(), $targetAlias)
				->where()->match($alias, $operator, CrIt::p($targetAlias));
		$this->targetComparatorConstraint->applyToCriteriaComparator($subCriteria->where()->andGroup(), $targetAlias);
	}

}
