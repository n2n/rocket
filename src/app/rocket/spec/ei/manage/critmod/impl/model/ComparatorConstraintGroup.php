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
namespace rocket\spec\ei\manage\critmod\impl\model;

use rocket\spec\ei\manage\critmod\CriteriaConstraint;
use n2n\reflection\ArgUtils;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\criteria\Criteria;
use rocket\spec\ei\manage\critmod\filter\ComparatorConstraint;

class ComparatorConstraintGroup implements CriteriaConstraint {
	private $andUsed;
	private $comparatorConstraints;
	
	public function __construct(bool $andUsed, array $comparatorConstraints = array()) {
		$this->andUsed = $andUsed;
		ArgUtils::valArray($comparatorConstraints, ComparatorConstraint::class);
		$this->comparatorConstraints = $comparatorConstraints;
	}
	
	public function getComparatorConstaints() {
		return $this->comparatorConstraints;
	}
		
	public function add(ComparatorConstraint $comparatorConstraint) {
		$this->comparatorConstraints[] = $comparatorConstraint;
	}
	
	public function applyToCriteria(Criteria $criteria, CriteriaProperty $alias) {
		$andComparator = $criteria->where()->andGroup();
		foreach ($this->comparatorConstraints as $comparatorConstraint) {
			$comparatorConstraint->applyToCriteriaComparator($andComparator->group($this->andUsed));
		}
	}
}
