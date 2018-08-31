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
namespace rocket\ei\manage\critmod\sort;

use n2n\reflection\ArgUtils;
use rocket\ei\EiPropPath;
use n2n\util\col\ArrayUtils;

class SortDefinition {
	private $sortFields = array();
	private $sortFieldForks = array();
	
	public function putSortProp(string $id, SortProp $sortField) {
		ArgUtils::assertTrue(!EiPropPath::constainsSpecialIdChars($id), 'Invalid id.');
		$this->sortFields[$id] = $sortField;	
	}
	
	public function containsSortPropId(string $id): bool {
		return isset($this->sortFields[$id]);
	}
	
	public function getSortProps(): array {
		return $this->sortFields;
	}
	
// 	public function setSortItems(array $sortFields) {
// 		$this->sortFields = $sortFields;
// 	}

	public function containsSortPropFork(string $id): bool {
		return isset($this->sortFieldForks[$id]);
	}

	public function putSortPropFork(string $id, SortPropFork $sortFieldFork) {
		ArgUtils::assertTrue(!EiPropPath::constainsSpecialIdChars($id), 'Invalid id.');
		$this->sortFieldForks[$id] = $sortFieldFork;
	}
	
	public function getSortPropForks(): array {
		return $this->sortFieldForks;
	}
	
	public function getAllSortProps(): array {
		$sortFields = $this->sortFields;
		
		foreach ($this->sortFieldForks as $forkId => $sortFieldFork) {
			$forkEiPropPath = EiPropPath::create($forkId);
			foreach ($sortFieldFork->getForkedSortDefinition()->getAllSortProps() as $id => $sortField) {
				$forkEiPropPath->ext(EiPropPath::create($id));
			}
		}
		
		return $sortFields;
	}
	
	public function builCriteriaConstraint(SortData $sortData, bool $tmp) {
		$sortConstraints = array();
					
		foreach ($sortData->getSortItemDatas() as $sortItemData) {
			$sortConstraint = $this->buildSortCriteriaConstraint( 
					EiPropPath::create($sortItemData->getSortPropId())->toArray(), $sortItemData->getDirection());
			if ($sortConstraint !== null) {
				$sortConstraints[] = $sortConstraint;
			}
		}
		
		if (empty($sortConstraints)) return null;
		
		return new SortCriteriaConstraintGroup($sortConstraints, $tmp);
	}
	
	protected function buildSortCriteriaConstraint(array $nextIds, string $direction) {
		$id = ArrayUtils::shift($nextIds, true);
		
		if (empty($nextIds)) {
			if (!isset($this->sortFields[$id])) return null;
			
			return $this->sortFields[$id]->createSortConstraint($direction);
		}		

		if (!isset($this->sortFieldForks[$id])) return null;

		$forkedSortConstraint = $this->sortFieldForks[$id]->getForkedSortDefinition()
				->buildSortCriteriaConstraint($nextIds, $direction);
		if ($forkedSortConstraint !== null) {
			return $this->sortFieldForks[$id]->createSortConstraint($forkedSortConstraint);
		}
		
		return null;
	}
}
