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
