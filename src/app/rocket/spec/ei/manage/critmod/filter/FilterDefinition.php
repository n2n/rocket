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
namespace rocket\spec\ei\manage\critmod\filter;

use rocket\spec\ei\manage\critmod\filter\data\FilterGroupData;
use n2n\util\config\AttributesException;

class FilterDefinition {
	private $filterFields = array();
	
	public function putFilterField(string $id, FilterField $filterItem) {
		$this->filterFields[$id] = $filterItem;
	}
	
	public function getFilterFields(): array {
		return $this->filterFields;
	}
	
	public function getFilterFieldById(string $id): FilterField {
		if (isset($this->filterFields[$id])) {
			return $this->filterFields[$id];
		}
		
		throw new UnknownFilterFieldException();
	}
	
	public function containsFilterFieldId(string $id): bool {
		return isset($this->filterFields[$id]);
	}
	
	public function isEmpty(): bool {
		return empty($this->filterFields);
	}
	
	public function createComparatorConstraint(FilterGroupData $filterGroupData): ComparatorConstraint {
		$criteriaComparators = array();
		
		foreach ($filterGroupData->getFilterItemDatas() as $subFilterItemData) {
			$id = $subFilterItemData->getFilterFieldId();
			if (!isset($this->filterFields[$id])) {
				continue;
			}
			
			try {
				$criteriaComparators[] = $this->filterFields[$id]->createComparatorConstraint(
						$subFilterItemData->getAttributes());
			} catch (AttributesException $e) {}
		}
		
		foreach ($filterGroupData->getFilterGroupDatas() as $subFilterGroupData) {
			$criteriaComparators[] = $this->createComparatorConstraint($subFilterGroupData);
		}
		
		return new ComparatorConstraintGroup($filterGroupData->isAndUsed(), $criteriaComparators);
	}
	
// 	private function createElementComparatorConstraint(FilterDataElement $element) {
// 		if ($element instanceof FilterDataUsage) {
// 			$itemId = $element->getItemId();
// 			if (isset($this->filterFields[$itemId])) {
// 				$comparatorConstraint = $this->filterFields[$itemId]->createComparatorConstraint($element->getAttributes());
// 				ArgUtils::valTypeReturn($comparatorConstraint, 
// 						'rocket\spec\ei\manage\critmod\ComparatorConstraint',
// 						$this->filterFields[$itemId], 'createComparatorConstraint');
// 				return $comparatorConstraint;
// 			}
// 		} else if ($element instanceof FilterDataGroup) {
// 			$group = new CriteriaComparatorConstraintGroup($element->isAndUsed());
// 			foreach ($element->getAll() as $childElement) {
// 				$group->addComparatorConstraint($this->createElementComparatorConstraint($childElement));
// 			}
// 			return $group;
// 		}
		
// 		return null;
// 	}
	
	
// 	public static function createFromFilterFields(FilterData $filterData, array $filterItems) {
// 		$filterModel = new FilterModel($filterData);
// 		foreach ($filterItems as $id => $filterItem) {
// 			$filterModel->putFilterField($id, $filterItem);
// 		}
// 		return $filterModel;
// 	}
}

class UnknownFilterFieldException extends \RuntimeException {
	
}
